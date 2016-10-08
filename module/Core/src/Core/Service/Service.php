<?php

namespace Core\Service;

use Core\Service\Service;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Session\Container;

/**
 * Service base class
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class Service implements ServiceManagerAwareInterface {

    /**
     * @var ServiceManager
     */
    private $serviceManager = null;

    public function getServiceManager() {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Retrieves a service by the name.
     * @param string $name Name of requested service.
     * @return Service | mixed
     */
    public function getService($name) {
        return $this->getServiceManager()->get($name);
    }

    /**
     * Returns information of session user, or null if has no user logged
     * @param string (optional) $attribute Attribute of session wanted
     * @return mixed|null
     */
    public function getCurrentUser() {

        $authService = new AuthenticationService();
        /* @var $authService AuthenticationService */

        $user = $authService->getIdentity();

        return $user;
    }

    /**
     * Retrieves a <code>Container</code> associated with the session.
     * @param string $name
     * @return Container
     */
    public function getSessionContainer($name = "Default") {
        return new Container($name, $this->getService('Zend\Session\SessionManager'));
    }

    /**
     * Retrieves a instance of param.
     * @param string $className
     * @return object
     * @throws Exception When the param not be a Closure or a valid class name.
     */
    protected function returnInstanceOf($className) {
        $instance = null;

        if ($className instanceof Closure) {
            $instance = $className->__invoke($this->serviceLocator);
        } else {
            if (class_exists('\\' . $className)) {
                $className = ('\\' . $className);
                $instance = new $className();
            } else
                throw new Exception("The parameter was not a valid class name.");
        }

        return $instance;
    }

}

?>
