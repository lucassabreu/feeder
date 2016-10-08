<?php

namespace Core\Service\Factory;

use Closure;
use Core\Model\DAO\DAOInterface;
use Core\Service\DAOServiceInterface;
use Exception;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory of DAO Service classes.
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
final class DAOServiceFactory implements AbstractFactoryInterface {

    protected $services = array();

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    protected function getConfig() {
        $gConfig = $this->serviceLocator->get('Configuration');

        if (isset($gConfig['service_manager']) && isset($gConfig['service_manager']['dao_factory']))
            return $gConfig['service_manager']['dao_factory'];
        else
            return array();
    }

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
        $this->serviceLocator = $serviceLocator;
        $config = $this->getConfig();

        return isset($config[$requestedName]);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
        $this->serviceLocator = $serviceLocator;
        $config = $this->getConfig();

        if (!isset($this->services[$requestedName]) || $this->services[$requestedName] === null) {

            /**
             * @var DAOServiceInterface
             */
            $service = $this->returnInstanceOf($config[$requestedName]['service']);
            $model = $this->returnInstanceOf($config[$requestedName]['model']);

            $service->setServiceManager($serviceLocator);
            $model->setServiceManager($serviceLocator);

            $service->setDAOInterface($model);

            $this->services[$requestedName] = $service;
        }

        return $this->services[$requestedName];
    }

    /**
     * Retrieves a instance of param.
     * @param Closure|string $param
     * @return DAOInterface
     * @throws Exception When the param not be a Closure or a valid class name.
     */
    protected function returnInstanceOf($param) {
        $instance = null;

        if ($param instanceof Closure) {
            $instance = $param->__invoke($this->serviceLocator);
        } else {
            if (class_exists('\\' . $param)) {
                $param = ('\\' . $param);
                $instance = new $param();
            } else
                throw new Exception("A service/model of DAOService must be a anonimous function or a class name valid. Param: $param");
        }

        return $instance;
    }

}

?>
