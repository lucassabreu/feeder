<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\Model\DAO\MongoDB;

use MongoCursor;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * <code>AdapterPaginator</code> for MongoDB cursor
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class MongoDBAdapterPaginator implements AdapterInterface {

    protected $cursor;
    protected $resultSetPrototype;

    /**
     * Creates a new <code>MongoDBAdapterPaginator</code> based on params
     * @param MongoCursor $cursor
     * @param ResultSetInterface $resultSetPrototype
     */
    public function __construct(MongoCursor $cursor, ResultSetInterface $resultSetPrototype) {
        $this->cursor = $cursor;
        $this->resultSetPrototype = $resultSetPrototype;
    }

    public function count() {
        return count($this->cursor);
    }

    public function getItems($offset, $itemCountPerPage) {
        $cursor = $this->cursor;

        if ($offset !== 0)
            $cursor->skip($offset);
        
        $cursor->limit($itemCountPerPage);
        
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($cursor);

        return $resultSet;
    }

    /**
     * @return MongoCursor
     */
    public function getCursor() {
        return $this->cursor;
    }

    /**
     * @return ResultSetInterface
     */
    public function getResultSetPrototype() {
        return $this->resultSetPrototype;
    }

}
