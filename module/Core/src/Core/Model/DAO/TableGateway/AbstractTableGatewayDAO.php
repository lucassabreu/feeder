<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\Model\DAO\TableGateway;

use Core\Model\DAO\DAOInterface;
use Core\Model\DAO\Exception\DAOException;
use Core\Model\DAO\Registrator;
use Core\Model\DAO\TableGateway\Reflection;
use Core\Model\Entity\Entity;
use Core\Service\Service;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\TableGateway\TableGateway;

/**
 * Basic implemented abstract class for DAO based on Zend Framework Table Gateway
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * @abstract
 */
abstract class AbstractTableGatewayDAO extends Service implements DAOInterface, Registrator {

    protected static $entities = array();
    protected $tableGateway = null;
    protected $entityClassName = null;
    protected $tableName = null;
    protected $collumnMapping = null;
    protected $idProperties = null;

    /**
     * Contruct a new TableGatewayDAO based on the params
     * @param string $entityClassName Name of the managed class
     * @param string $tableName Name of the managed table
     * @param array $collumnMapping Collumn mapping of the object array(property => collumn)
     * @param array $idProperties ID properties of the class
     */
    public function __construct($entityClassName, $tableName, array $collumnMapping, array $idProperties = array()) {
        $this->entityClassName = $entityClassName;
        $this->tableName = $tableName;
        $this->collumnMapping = $collumnMapping;
        $this->idProperties = $idProperties;

        self::$entities[$this->entityClassName] = array();
    }

    protected function find(array $params = array()) {
        $select = $this->getQuery($params, 1);

        $result = $this->tableGateway()->selectWith($select);

        if ($result->count() === 0)
            return null;
        else
            return $result->current();
    }

    public function fetchAll($limit = null, $offset = null) {
        $select = $this->getQuery(array(), $limit, $offset);
        return $this->tableGateway()->selectWith($select);
    }

    public function fetchByParams(array $params, $limit = null, $offset = null) {
        $select = $this->getQuery($params, $limit, $offset);
        return $this->tableGateway()->selectWith($select);
    }

    public function findById($keys) {
        if (!is_array($keys))
            $keys = array($keys);

        return $this->find(array_combine($this->idProperties, $keys));
    }

    public function save($entity, array $values = null) {
        $insert = false;

        if ($entity === null) {
            $entity = $this->returnInstanceOf($this->entityClassName);
            $entity->setData($values);
            $insert = true;
        } else
            $insert = !$this->isRegistered($entity);

        /* @var $entity Entity */
        $entityData = $entity->getData();

        $tableData = array();

        foreach ($entityData as $prop => $value) {
            if (isset($this->collumnMapping[$prop]))
                $tableData[$this->collumnMapping[$prop]] = $value;
        }

        if ($insert === false) {
            $tableWhere = array();

            foreach ($this->idProperties as $prop) {
                $tableWhere[$this->collumnMapping[$prop]] = $entityData[$prop];
            }
        }

        if ($insert) {
            $statement = $this->tableGateway()->getSql()->insert();
            /* @var $statement Insert */
            $statement->values($tableData);

            $result = $this->tableGateway()->insertWith($statement);
        } else {
            $statement = $this->tableGateway()->getSql()->update();
            /* @var $statement Update */

            $statement->set($tableData);
            $statement->where($tableWhere);

            $result = $this->tableGateway()->updateWith($statement);
        }

        if ($result !== 0 && $insert == true) {
            $entity->{$this->idProperties[0]} = $this->tableGateway()->getLastInsertValue();
            $this->register($entity);
        }

        return $entity;
    }

    public function remove(Entity $entity) {
        if (!$this->isRegistered($entity))
            throw new DAOException("Object was not registered in the TableGateway !");

        $where = array();

        foreach ($this->idProperties as $property) {
            $where[$this->collumnMapping[$property]] = $entity->{$property};
        }

        $result = $this->tableGateway()->delete($where);

        return ($result > 0);
    }

    public function getAdapterPaginator($params, $orderBy = null) {
        
    }

    public function rollback() {
        return $this->getAdapter()->getDriver()->getConnection()->rollback();
    }

    public function beginTransaction() {
        return $this->getAdapter()->getDriver()->getConnection()->beginTransaction();
    }

    public function commit() {
        return $this->getAdapter()->getDriver()->getConnection()->commit();
    }

    /**
     * Return a Select object based at the params
     * @param array $params
     * @param int $limit
     * @param int $offset
     * @return Select
     */
    protected function getQuery(array $params = array(), $limit = null, $offset = null) {
        $select = $this->tableGateway()->getSql()->select();

        $select->columns($this->collumnMapping);

        if ($params !== null) {
            if (is_array($params)) {
                if (count($params) !== 0) {
                    foreach ($params as $column => $clause) {
                        $column = sprintf("%s.%s", $this->tableName, $this->collumnMapping[$column]);

                        if (is_array($clause)) {
                            if (isset($clause['in']))
                                $select->where->in($column, $clause['in']);
                            else
                                $select->where->between($column, $clause[0], $clause[1]);
                        } else {
                            if (strpos($clause, '%') !== false)
                                $select->where->like($column, $clause);
                            else {
                                if (strtoupper($clause) === 'IS NULL' || $clause === null) {
                                    $select->where->isNull($column);
                                } else {
                                    if (strtoupper($clause) === 'IS NOT NULL') {
                                        $select->where->isNotNull($column);
                                    } else {
                                        $select->where->equalTo($column, $clause);
                                    }
                                }
                            }
                        }
                    }
                }
            } else
                $select->where($params);
        }

        if ($limit !== null)
            $select->limit($limit);

        if ($offset !== null)
            $select->offset($offset);

        return $select;
    }

    /**
     * Register a <code>Entity</code> object against the DAO
     * @param Entity $entity
     * @return Entity
     */
    public function register(Entity $entity) {
        if (!$this->isRegistered($entity))
            return self::$entities[$this->entityClassName][] = $entity;
        else {
            $oldEntity = self::$entities[$this->entityClassName][array_search($entity, self::$entities[$this->entityClassName])];
            $oldEntity->setData($entity->getData());
            return $oldEntity;
        }
    }

    /**
     * Retrieves <code>true</code> if the object was managed by the TableGateway
     * @param Entity $entity
     * @return bool
     */
    protected function isRegistered(Entity $entity) {
        return in_array($entity, self::$entities[$this->entityClassName]);
    }

    public function getEntityClassName() {
        return $this->entityClassName;
    }

    /**
     * Adapter for connection with database
     * @return Adapter
     */
    public function getAdapter() {
        return $this->getService('Zend\Db\Adapter\Adapter');
    }

    /**
     * Object for management of objects
     * @return TableGateway
     */
    protected function tableGateway() {
        if ($this->tableGateway === null) {

            $op = $this->returnInstanceOf($this->entityClassName);
            $hrs = new HydratingResultSet(new Reflection($this), $op);

            $this->tableGateway = new TableGateway($this->tableName, $this->getAdapter(), null, $hrs);
        }

        return $this->tableGateway;
    }

}
