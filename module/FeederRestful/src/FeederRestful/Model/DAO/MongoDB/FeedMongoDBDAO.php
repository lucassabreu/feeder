<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FeederRestful\Model\DAO\MongoDB;

use Core\Model\DAO\MongoDB\AbstractMongoDBDAO;
use FeederRestful\Model\DAO\FeedDAOInterface;
use FeederRestful\Model\Entity\Feed;
use MongoRegex;

/**
 * MongoDBDAO implementation for <code>Feed</code> entity
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * @see Feed
 */
class FeedMongoDBDAO extends AbstractMongoDBDAO implements FeedDAOInterface {

    public function __construct() {
        parent::__construct('FeederRestful\Model\Entity\Feed', 'feed', array(
            'id' => '_id',
            'link' => 'link',
            'feedLink' => 'feedLink',
            'title' => 'title',
            'description' => 'description',
            'dateModified' => 'dateModified',
            'nextExpectedUpdate' => 'nextExpectedUpdate',
            'lastUpdate' => 'lastUpdate',
            'thumbnail' => 'thumbnail',
            'type' => 'type',
        ), array('id'), 'feed_seq');
    }

    protected function createIndexes() {
        $this->collection()->ensureIndex(['title' => 1]);
        $this->collection()->ensureIndex(['link' => 1]);
        $this->collection()->ensureIndex(['feedLink' => 1], ['unique' => true]);
    }

    public function fetchFeedLinkBeginsWith($linkBegins, $limit = 10) {
        $linkBegins = str_replace('/', '\/', preg_quote($linkBegins));

        if (is_null($limit) || $limit === 0)
            $limit = 10;

        return $this->fetch(array(
            'feedLink' => new MongoRegex("/^(http|https)\:\/\/$linkBegins/i")
        ), $limit, null, array(
            'link'
        ));
    }

    public function fetchLinkBeginsWith($linkBegins, $limit = 10) {
        $linkBegins = str_replace('/', '\/', preg_quote($linkBegins));

        if (is_null($limit) || $limit === 0)
            $limit = 10;

        return $this->fetch(array(
            'link' => new MongoRegex("/^(http|https)\:\/\/$linkBegins/i")
        ), $limit, null, array(
            'link'
        ));
    }

    public function fetchFeedLinkWith($part, $limit = 10) {
        $part = preg_quote($part);

        if (is_null($limit) || $limit === 0)
            $limit = 10;

        return $this->fetch(array(
            'feedLink' => new MongoRegex("/$part/i")
        ), $limit, null, array(
            'link'
        ));
    }

    public function fetchTitleTermsWith($params, $limit = 10) {
        foreach ($params as &$param)
            $param = preg_quote($param);
        $str_params = implode("|", $params);

        if (is_null($limit) || $limit === 0)
            $limit = 10;

        return $this->fetch(array(
            'title' => new MongoRegex("/($str_params)/i")
        ), $limit, null, array(
            'title'
        ));
    }

    public function findByLink($link) {
        return $this->find(array('link' => $link));
    }

    public function findByFeedLink($feedLink) {
        return $this->find(array('feedLink' => $feedLink));
    }

}
