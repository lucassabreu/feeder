<?php

namespace Core\Model\Entity;

use Core\Model\DAO\DAOInterface;
use Core\Model\DAO\Exception\DAOException;
use InvalidArgumentException;
use Serializable;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Json\Json;
use Zend\Stdlib\JsonSerializable;

/**
 * Base class for entities managed by Core\Model\DAOInterface.
 * 
 * @see DAOInterface
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * 
 */
abstract class Entity implements Serializable, InputFilterAwareInterface, JsonSerializable {

    /**
     * Filters
     * 
     * @var InputFilter
     */
    protected $inputFilter = null;

    /**
     * Set and validate field values
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function __set($key, $value) {
        $this->$key = $this->valid($key, $value);
    }

    /**
     * @param string $key
     * @return mixed 
     */
    public function __get($key) {
        return $this->$key;
    }

    /**
     * Set all entity data based in an array with data
     *
     * @param array $data
     * @return void
     */
    public function setData($data) {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * Return all entity data in array format
     *
     * @return array
     */
    public function getData() {
        $data = get_object_vars($this);
        unset($data['inputFilter']);
        return $data;
    }

    /**
     * Used by TableGateway
     *
     * @param array $data
     * @return void
     */
    public function exchangeArray($data) {
        $this->setData($data);
    }

    /**
     * Used by TableGateway
     *
     * @param array $data
     * @return void
     */
    public function getArrayCopy() {
        return $this->getData();
    }

    /**
     * @param InputFilterInterface $inputFilter
     * @return void
     */
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new DAOException("Not used");
    }

    /**
     * Entity filters
     *
     * @return InputFilter
     */
    abstract public function getInputFilter();

    /**
     * Filter and validate data
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function valid($key, $value) {
        if (!$this->getInputFilter())
            return $value;

        if (!$this->getInputFilter()->has($key))
            return $value;

        try {
            $filter = $this->getInputFilter()->get($key);
        } catch (InvalidArgumentException $e) {
            //não existe filtro para esse campo
            return $value;
        }

        $filter->setValue($value);

        if (!$filter->isValid()) {
            $errors = implode(', ', $filter->getMessages());
            throw new DAOException("Invalid input: $key = '$value'. $errors");
        }

        return $filter->getValue($key);
    }

    /**
     * Used by TableGateway
     *
     * @return array
     */
    public function toArray() {
        return $this->getData();
    }

    public function serialize() {
        return serialize($this->toArray());
    }

    public function unserialize($serialized) {
        $this->setData(unserialize($serialized));
        return $this;
    }

    public function __toString() {
        return get_class($this);
    }

    /**
     * Execute validations on entity
     * @return boolean
     */
    public function validate() {

        $data = get_object_vars($this);

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return true;
    }

    /**
     * Calls <code>validate</code> method on this object
     * @see Entity#validate
     * @return boolean
     */
    public function isValid() {
        return $this->validate();
    }

    public function jsonSerialize() {
        return $this->toArray();
    }
    
}

?>
