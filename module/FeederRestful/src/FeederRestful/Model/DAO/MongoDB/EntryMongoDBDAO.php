<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FeederRestful\Model\DAO\MongoDB;

use Core\Model\DAO\MongoDB\AbstractMongoDBDAO;
use FeederRestful\Model\DAO\EntryDAOInterface;
use FeederRestful\Model\Entity\Feed;

/**
 * MongoDBDAO implementation for <code>Entry</code> entity
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * @see Feed
 */
class EntryMongoDBDAO extends AbstractMongoDBDAO implements EntryDAOInterface {

    public function __construct() {
        parent::__construct('FeederRestful\Model\Entity\Entry', 'entry', array(
            'id' => '_id',
            'link' => 'link',
            'feedId' => 'feedId',
            'title' => 'title',
            'authors' => 'authors',
            'content' => 'content',
            'dateModified' => 'dateModified',
        ), array('id'), 'entry_seq');
    }

    protected function createIndexes() {
        $this->collection()->ensureIndex(['title' => 1]);
        $this->collection()->ensureIndex(['feedId' => 1]);
        $this->collection()->ensureIndex(['dateModified' => -1]);
        $this->collection()->ensureIndex(['link' => 1], ['unique' => true]);
    }

    public function findByLink($link) {
        return $this->find(array('link' => $link));
    }

}
