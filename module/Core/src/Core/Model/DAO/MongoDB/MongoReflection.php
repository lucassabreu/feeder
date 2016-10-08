<?php

namespace Core\Model\DAO\MongoDB;

use Core\Model\DAO\TableGateway\Reflection;
use Core\Util\DateTime;
use MongoDate;

/**
 * Extension of class <code>Refletion</code> to implement compability with MongoDB
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * @see Reflection
 */
class MongoReflection extends Reflection {

    protected $propertyMapping;

    public function __construct(AbstractMongoDBDAO $registrator) {
        parent::__construct($registrator);
    }

    public function hydrate(array $data, $object) {
        $mappedData = array();
        $propMapping = $this->getPropertyMapping();

        foreach ($data as $field => $value) {
            if (isset($propMapping[$field]))
                $mappedData[$propMapping[$field]] = $value;
        }

        $this->convertDataAtArray($mappedData);
        
        return parent::hydrate($mappedData, $object);
    }

    protected function getPropertyMapping() {
        if ($this->propertyMapping == null) {
            $this->propertyMapping = array();

            foreach ($this->registrator->getFieldMapping() as $prop => $field)
                $this->propertyMapping[$field] = $prop;
        }

        return $this->propertyMapping;
    }
    
    public function extract($object) {
        return parent::extract($object);
    }

    /**
     * Convert objects
     * @param array $params
     */
    protected function convertDataAtArray(array &$params) {
        foreach ($params as &$value) {
            if (is_array($value))
                $this->convertDateAtArray($value);
            else {
                if ($value instanceof MongoDate) {
                    $nValue = new DateTime();
                    $nValue->setTimestamp($value->sec);
                    $value = $nValue;
                }
            }
        }
    }

}
