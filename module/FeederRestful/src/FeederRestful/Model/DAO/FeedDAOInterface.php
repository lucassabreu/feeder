<?php

namespace FeederRestful\Model\DAO;

use Core\Model\DAO\DAOInterface;
use FeederRestful\Model\Entity\Feed;

/**
 * Interface for Feed DAO
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
interface FeedDAOInterface extends DAOInterface {

    /**
     * Fetch Feeds with terms passed by param in title
     * @param string|array $params Terms for search
     * @return array
     */
    public function fetchTitleTermsWith($params, $limit = 10);

    /**
     * Fetch Feeds that feed link begins with the param
     * @param string $linkBegins
     * @return array
     */
    public function fetchFeedLinkBeginsWith($linkBegins, $limit = 10);
    
    /**
     * Fetch Feeds that link begins with the param
     * @param string $linkBegins
     * @return array
     */
    public function fetchLinkBeginsWith($linkBegins, $limit = 10);

    /**
     * Fetch Feeds that has the param
     * @param string $linkBegins
     * @return array
     */
    public function fetchFeedLinkWith($part, $limit = 10);

    /**
     * Retrieves one Feed by its link
     * @param string $link
     * @return Feed
     */
    public function findByLink($link);

    /**
     * Retrieves one Feed by its feed's link
     * @param string $feedLink
     * @return Feed
     */
    public function findByFeedLink($feedLink);
}
