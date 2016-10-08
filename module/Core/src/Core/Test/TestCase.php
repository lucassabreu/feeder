<?php

namespace Core\Test;

use Core\Test\Bootstrap;
use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Base Test Case class
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase {

    /**
     * Current application
     * @var Application
     */
    protected $application;

    /**
     * Returns the current bootstrap instance.
     * @return Bootstrap
     */
    public function getBootstrap() {
        return Bootstrap::getInstance();
    }

    public function setup() {
        parent::setup();
        $this->application = $this->getBootstrap()->getApplication();
        $this->createDatabase();
    }

    public function tearDown() {
        parent::tearDown();
        $this->dropDatabase();
        $this->application = null;
        $this->getBootstrap()->reset();
    }

    /**
     * @return void
     */
    public function createDatabase() {
        $dbAdapter = $this->getAdapter();

        if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Sqlite') {
            //enable foreign keys on sqlite
            $dbAdapter->query('PRAGMA foreign_keys = ON;', Adapter::QUERY_MODE_EXECUTE);
        }

        if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Mysql') {
            //enable foreign keys on mysql
            $dbAdapter->query('SET FOREIGN_KEY_CHECKS = 1;', Adapter::QUERY_MODE_EXECUTE);
        }

        $queries = $this->getBootstrap()->getTestConfig();
        foreach ($queries as $queries) {
            foreach ($queries['create'] as $query)
                $dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    /**
     * @return void
     */
    public function dropDatabase() {
        $dbAdapter = $this->getAdapter();

        if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Sqlite') {
            //disable foreign keys on sqlite
            $dbAdapter->query('PRAGMA foreign_keys = OFF;', Adapter::QUERY_MODE_EXECUTE);
        }
        if (get_class($dbAdapter->getPlatform()) == 'Zend\Db\Adapter\Platform\Mysql') {
            //disable foreign keys on mysql
            $dbAdapter->query('SET FOREIGN_KEY_CHECKS = 0;', Adapter::QUERY_MODE_EXECUTE);
        }

        $queries = $this->getBootstrap()->getTestConfig();
        foreach ($queries as $query) {
            $dbAdapter->query($query['drop'], Adapter::QUERY_MODE_EXECUTE);
        }
    }

    /**
     * 
     * @return Adapter
     */
    public function getAdapter() {
        return $this->getServiceManager()->get('DbAdapter');
    }

    public function getApplication() {
        return $this->application;
    }

    /**
     * Retrieve the current ServiceManager.
     * @return ServiceManager
     */
    public function getServiceManager() {
        return $this->getBootstrap()->getServiceManager();
    }

    /**
     * Retrieve the current MvcEvent.
     * @return MvcEvent
     */
    public function getEvent() {
        return $this->getBootstrap()->getEvent();
    }

    /**
     * Retrieve the list of routes.
     * @return array
     */
    public function getRoutes() {
        return $this->getBootstrap()->getRoutes();
    }

    /**
     * Retrieve Service
     *
     * @param  string $service
     * @return ServiceLocatorAwareInterface
     */
    protected function getService($service) {
        return $this->getServiceManager()->get($service);
    }

}