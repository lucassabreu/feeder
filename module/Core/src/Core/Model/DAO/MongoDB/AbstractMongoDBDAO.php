<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\Model\DAO\MongoDB;

use Core\Model\DAO\DAOInterface;
use Core\Model\DAO\Exception\DAOException;
use Core\Model\DAO\MongoDB\MongoReflection as Reflection;
use Core\Model\DAO\Registrator;
use Core\Model\Entity\Entity;
use Core\Service\Service;
use DateTime;
use Iterator;
use LogicException;
use MongoCollection;
use MongoCursor;
use MongoDate;
use MongoDB;
use MongoException;
use MongoRegex;
use Zend\Db\ResultSet\HydratingResultSet;

/**
 * Basic implemented abstract class for DAO based on MongoDB Driver
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * @abstract
 */
abstract class AbstractMongoDBDAO extends Service implements DAOInterface, Registrator {

    protected static $entities = array();
    protected $tableGateway = null;
    protected $entityClassName = null;
    protected $collectionName = null;
    protected $fieldMapping = null;
    protected $idProperties = null;
    protected $resultSetPrototype = null;
    protected $enableNullID = false;
    protected $sequenceId = false;

    /**
     * Contruct a new TableGatewayDAO based on the params
     * @param string $entityClassName Name of the managed class
     * @param string $collectionName Name of the managed collection
     * @param array $fieldMapping Collumn mapping of the object array(property => field)
     * @param array $idProperties ID properties of the class
     * @param string $sequenceId Name of sequence in counters, if doesn't use a sequence set false (default false)
     */
    public function __construct($entityClassName, $collectionName, array $fieldMapping, array $idProperties = array('_id'), $sequenceId = false) {
        $this->entityClassName = $entityClassName;
        $this->collectionName = $collectionName;
        $this->fieldMapping = $fieldMapping;
        $this->idProperties = $idProperties;
        $this->sequenceId = $sequenceId;

        self::$entities[$this->entityClassName] = array();
    }

    /**
     * Retrieves one result based on query parameters
     * @param array $params
     * @return Entity|null
     */
    protected function find(array $params = array()) {
        $params = $this->normalizeQuery($params);
        $result = $this->collection()->findOne($params);

        if (is_null($result))
            return null;

        return $this->getResultSetPrototype()->getHydrator()->hydrate($result, $this->returnInstanceOf($this->entityClassName));
    }

    public function findById($keys) {
        if (!is_array($keys))
            $keys = array($keys);

        return $this->find(array_combine($this->idProperties, $keys));
    }

    /**
     * Execute a query based on informed params.
     * @param array $params [optional]
     * @param int|null $limit [optional]
     * @param int|null $offset [optional]
     * @param array $sortedBy [optional]
     * @return array Array of Entities
     */
    protected function fetch(array $params = null, $limit = null, $offset = null, array $sortedBy = null) {
        $result = $this->fetchCursor($params, $sortedBy);

        if ($offset !== null)
            $result->skip($offset);

        if ($limit !== null)
            $result->limit($limit);

        return $this->hydrateResult($result);
    }

    /**
     * Retrieves a <code>MongoCursor</code> based on params
     * @param array $params [optional]
     * @param int|null $limit [optional]
     * @param int|null $offset [optional]
     * @param array $orderBy [optional]
     * @return MongoCursor
     */
    function fetchCursor(array $params = null, array $orderBy = null) {
        if ($params !== null)
            $params = $this->normalizeQuery($params);
        else
            $params = array();

        $result = $this->collection()->find($params, array_values($this->fieldMapping));

        if ($orderBy !== null) {
            $sortedBy = array();

            foreach ($orderBy as $property => $order) {
                if (isset($this->fieldMapping[$property]))
                    $sortedBy[$this->fieldMapping[$property]] = $order == 'asc' ? 1 : -1;
            }

            $result->sort($sortedBy);
        }

        return $result;
    }

    public function fetchAll($limit = null, $offset = null) {
        return $this->fetch(null, $limit, $offset);
    }

    public function fetchByParams(array $params, $limit = null, $offset = null) {
        return $this->fetch($params, $limit, $offset);
    }

    public function save($entity, array $values = null) {
        if ($entity === null) {
            $entity = $this->returnInstanceOf($this->entityClassName);
            $entity->setData($values);
        }

        /* @var $entity Entity */
        $entityData = $entity->getData();

        $document = array();

        foreach ($entityData as $prop => $value) {
            if (isset($this->fieldMapping[$prop]))
                $document[$this->fieldMapping[$prop]] = $value;
        }

        if ($this->enableNullID === false) {
            if (isset($document['_id']) && $document['_id'] === null)
                unset($document['_id']);

            if (!isset($document['_id']))
                unset($document['_id']);
        }

        $this->convertDataAtArray($document);

        try {
            if ($this->isRegistered($entity))
                $this->collection()->save($document, array('w' => true));
            else {

                if ($this->collection()->count() === 0)
                    $this->createIndexes();

                if ($this->sequenceId !== false)
                    $document['_id'] = $this->getNextSequence($this->sequenceId);

                $this->collection()->insert($document, array('w' => true));
                $entity = $this->register($entity);
            }

            foreach ($this->fieldMapping as $prop => $field) {
                if ($field === '_id') {
                    $entity->{$prop} = $document['_id'];
                    break;
                }
            }
        } catch (MongoException $ex) {
            throw new DAOException($ex->getMessage(), $ex->getCode(), $ex);
        }

        return $entity;
    }

    public function remove(Entity $entity) {
        if (!$this->isRegistered($entity))
            throw new DAOException("Object was not registered in the system !");

        $where = array();

        foreach ($this->idProperties as $property) {
            $where[$this->fieldMapping[$property]] = $entity->{$property};
        }

        $result = $this->collection()->remove($where);

        return ($result == 1);
    }

    public function getAdapterPaginator($params, $orderBy = null) {
        return new MongoDBAdapterPaginator($this->fetchCursor($params, $orderBy), $this->getIntanceOfResultSet());
    }

    public function rollback() {
        throw new LogicException("You're using a MongoDB database, this operation isn't supported !");
    }

    public function beginTransaction() {
        throw new LogicException("You're using a MongoDB database, this operation isn't supported !");
    }

    public function commit() {
        throw new LogicException("You're using a MongoDB database, this operation isn't supported !");
    }

    /**
     * Convert DateTime objects into MongoDate objects at a param array
     * @param array $params
     */
    protected function convertDataAtArray(array &$params) {
        foreach ($params as &$value) {
            if (is_array($value))
                $this->convertDataAtArray($value);
            else {
                if ($value instanceof DateTime) {
                    $value = new MongoDate($value->getTimestamp());
                }
            }
        }
    }

    /**
     * Return a array normalized query based at the params
     * @param array $params
     * @return array
     */
    protected function normalizeQuery(array $params) {
        $nrlzdParams = array();

        $this->convertDataAtArray($params);

        if (is_array($params)) {
            if (count($params) !== 0) {
                foreach ($params as $property => $clause) {
                    if (!isset($this->fieldMapping[$property]))
                        continue;

                    $field = $this->fieldMapping[$property];

                    if (is_array($clause)) {
                        foreach ($clause as $key => $value) {
                            switch ($key) {
                                case 'lt':
                                case 'gt':
                                case 'gte':
                                case 'lte':
                                case 'in':
                                    $nrlzdParams[$field] = array(
                                        "\$$key" => $value,
                                    );
                                    break;
                                case 'between':
                                default:
                                    $nrlzdParams[$field] = array(
                                        '$gte' => $clause[0],
                                        '$lte' => $clause[1]
                                    );
                            }
                        }
                    } else {
                        if (strpos($clause, '%') !== false) {
                            $clause = preg_quote($clause);

                            if (substr($clause, 0, 1) !== '%')
                                $clause = "^$clause";
                            else
                                $clause = substr($clause, 1);

                            if (substr($clause, strlen($clause) - 1, 1) !== '%')
                                $clause = "$clause$";
                            else
                                $clause = substr($clause, 0, strlen($clause) - 1);

                            $clause = str_replace('%', '.*', $clause);

                            $nrlzdParams[$field] = new MongoRegex("/$clause/i");
                        } else {
                            if (strtoupper($clause) === 'IS NULL' || $clause === null) {
                                $nrlzdParams[$field] = null;
                            } else {
                                if (strtoupper($clause) === 'IS NOT NULL') {
                                    $nrlzdParams[$field] = array(
                                        '$not' => null
                                    );
                                } else {
                                    $nrlzdParams[$field] = $clause;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $nrlzdParams;
    }

    /**
     * HydratingResultSet prototype
     * @return HydratingResultSet
     */
    public function getResultSetPrototype() {
        if ($this->resultSetPrototype == null) {
            $op = $this->returnInstanceOf($this->entityClassName);
            $hdrtr = new Reflection($this);
            $hdrtr->addFilter('inputFilter', function($property) {
                return $property !== 'inputFilter';
            });
            $this->resultSetPrototype = new HydratingResultSet($hdrtr, $op);
        }
        return $this->resultSetPrototype;
    }

    /**
     * Uses HydratatingResultSet to feed a array with objects
     * @param Iterator|array $resultQuery
     * @return array
     */
    public function hydrateResult($resultQuery) {
        $resultSet = $this->getIntanceOfResultSet($resultQuery);
        $return = array();

        foreach ($resultSet as $row) {
            /* @var $row Entity */
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Retrieves the next sequence number
     * @param string $name
     * @return integer
     */
    protected function getNextSequence($name) {
        $counters = $this->getMongoDB()->counters;
        /* @var $counters MongoCollection */

        $sequence = $counters->findAndModify(array('_id' => $name), array('$inc' => array('seq' => 1)), null, array(
            'new' => true,
            'upsert' => true,
        ));

        return $sequence['seq'];
    }

    /**
     * Retrieves a clone of <code>resultSetPrototype</code> property
     * @param Iterator|array|mixed $dataSource
     * @return HydratingResultSet
     */
    protected function getIntanceOfResultSet($dataSource = null) {
        $resultSet = clone $this->getResultSetPrototype();

        if (!is_null($dataSource))
            $resultSet->
            initialize($dataSource);

        return $resultSet;
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
            $oldEntity = self::$entities[$this->entityClassName][array_search($entity, self:: $entities[$this->entityClassName])];
            $oldEntity->setData($entity->getData());
            return $oldEntity;
        }
    }

    /**
     * Retrieves <code>true</code> if the object was managed by the ModelDAO
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
     * @return MongoDB
     */
    public function getMongoDB() {
        return $this->getService('Core\MongoDB');
    }

    /**
     * Retrieves a field mapping between class and collection
     * @return array
     */
    public function getFieldMapping() {
        return $this->fieldMapping;
    }

    /**
     * Object for management of the collection
     * @return MongoCollection
     */
    protected function collection() {
        return $this->getMongoDB()->{$this->collectionName};
    }

    /**
     * When collection need more indexes, put its definition in this
     */
    protected function createIndexes() {
        
    }

}
