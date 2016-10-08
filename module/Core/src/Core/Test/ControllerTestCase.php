<?php

namespace Core\Test;

use Core\Test\TestCase;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ViewModel;

abstract class ControllerTestCase extends TestCase {

    /**
     * The ActionController we are testing
     *
     * @var AbstractActionController
     */
    protected $controller;

    /**
     * A request object
     *
     * @var Request
     */
    protected $request;

    /**
     * The matched route for the controller
     *
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * An MVC event to be assigned to the controller
     *
     * @var MvcEvent
     */
    protected $event;

    /**
     * The Controller fully qualified domain name, so each ControllerTestCase can create an instance
     * of the tested controller
     *
     * @var string
     */
    protected $controllerName;

    /**
     * The route to the controller, as defined in the configuration files
     *
     * @var string
     */
    protected $controllerRoute;

    public function setup() {
        parent::setup();

        $routes = $this->getRoutes();

        $this->controller = new $this->controllerName; //$this->getServiceManager()->get($this->controllerName);
        $this->request = new Request();
        $this->routeMatch = $this->getEvent()->getRouteMatch();

        $this->routeMatch = new RouteMatch(array(
                    'router' => array(
                        'routes' => array(
                            $this->controllerRoute => $routes[$this->controllerRoute]
                        )
                    )
                ));
        $this->event = $this->getEvent();
        $this->event->setRouteMatch($this->routeMatch);

        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($this->getServiceManager());
    }

    /**
     * Dispath the controller
     * @param string $action
     * @param array $params
     * @param string $method
     * @return ViewModel | mixed
     */
    protected function dispath($action = null, $params = null, $method = null) {
        if (!is_null($action))
            $this->routeMatch->setParam('action', $action);

        if ($params !== null) {
            if (isset($params['route']))
                foreach ($params['route'] as $key => $value)
                    $this->routeMatch->setParam($key, $value);

            if (isset($params['post']))
                foreach ($params['post'] as $key => $value)
                    $this->request->getPost()->set($key, $value);

            if (isset($params['get']))
                foreach ($params['get'] as $key => $value)
                    $this->request->getGet()->set($key, $value);
        }

        if (!is_null($method))
            $this->request->setMethod($method);

        return $this->controller->dispatch($this->request);
    }

    public function tearDown() {
        parent::tearDown();
        unset($this->controller);
        unset($this->request);
        unset($this->routeMatch);
    }

}