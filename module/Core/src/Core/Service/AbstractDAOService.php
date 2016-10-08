<?php

namespace Core\Service;

use Core\Model\DAO\DAOInterface;
use Core\Model\Entity\Entity;
use Core\Service\DAOServiceInterface;
use Core\Service\Service;

/**
 * Abstract class with basic logic to DAO Service classes
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
abstract class AbstractDAOService extends Service implements DAOServiceInterface {

    /**
     * @var DAOInterface
     */
    protected $dao = null;

    /**
     * Merge the arrays without overwrite the keys of the first one.
     * @param array $values
     * @param array $oldValues
     * @return array 
     */
    protected function fillValues(array $values, array $oldValues) {
        if ($values === null)
            return $values = array();

        foreach ($oldValues as $key => $value)
            if (!isset($values[$key]))
                $values[$key] = $value;

        return $values;
    }

    public function findById($keys) {
        return $this->dao->findById($keys);
    }

    public function fetchAll($limite = null, $initial = null) {
        return $this->dao->fetchAll($limite, $initial);
    }

    public function fetchByParams(array $params, $limite = null, $offset = null) {
        return $this->dao->fetchByParams($params, $limite, $offset);
    }

    public function getAdapterPaginator($params, $orderBy = null) {
        return $this->getDAOInterface()->getAdapterPaginator($params, $orderBy);
    }

    public function getEntityClassName() {
        return $this->dao->getEntityClassName();
    }

    public function remove(Entity $ent) {
        $this->dao->remove($ent);
        return $this;
    }

    public function save($ent, array $values = null) {

        if ($ent !== null && !($ent instanceof Entity))
            $ent = $this->findById($ent);

        if ($ent === null)
            $ent = $this->returnInstanceOf($this->getEntityClassName());

        if ($values !== null) {
            $values = $this->fillValues($values, $ent->getData());
            $ent->setData($values);
        }

        $this->dao->save($ent);
        return $ent;
    }

    public function setDAOInterface(DAOInterface $dao) {
        $this->dao = $dao;
        return $this;
    }

    public function getDAOInterface() {
        return $this->dao;
    }

    public function beginTransaction() {
        return $this->dao->beginTransaction();
    }

    public function commit() {
        return $this->dao->commit();
    }

    public function rollback() {
        return $this->dao->rollback();
    }

}

?>
