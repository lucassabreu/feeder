<?php

namespace Core\Service;

use Core\Model\DAO\DAOInterface;
use Core\Service\DAOServiceInterface;

/**
 * Base interface for DAO services.
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
interface DAOServiceInterface extends DAOInterface {

    /**
     * Sets the DAO object
     * @param DAOInterface $dao
     * @return DAOServiceInterface
     */
    public function setDAOInterface(DAOInterface $dao);
}

?>
