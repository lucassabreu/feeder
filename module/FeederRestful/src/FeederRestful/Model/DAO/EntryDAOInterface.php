<?php

namespace FeederRestful\Model\DAO;

use Core\Model\DAO\DAOInterface;
use FeederRestful\Model\Entity\Feed;

/**
 * Interface for Entry DAO
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
interface EntryDAOInterface extends DAOInterface {

    /**
     * Retrieves one Feed by its link
     * @param string $link
     * @return Feed
     */
    public function findByLink($link);
}
