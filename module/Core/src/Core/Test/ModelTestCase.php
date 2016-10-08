<?php

namespace Core\Test;

/**
 * Base Class for Tests with Model Classes
 */
abstract class ModelTestCase extends TestCase {

    /**
     * Default DAO name
     * @var string
     */
    protected $daoName;

    /**
     * Retrieves the DAO.
     * @return UserDAOInterface
     */
    public function getDAO() {
        return $this->getService($this->daoName);
    }
}