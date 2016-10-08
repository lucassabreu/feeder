<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\Controller;

use Exception;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController as ZendAbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\View\Model\JsonModel;

/**
 * Base class for restful controller at application
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class AbstractRestfulController extends ZendAbstractRestfulController {

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Retrieve a JSON Movel with the params
     * @param array $variables
     * @param array $options
     * @return JsonModel
     */
    protected function json($variables = array(), $options = array()) {
        return new JsonModel($variables, $options);
    }

    protected function returnJsonException(Exception $e) {
        $this->invalidRequestAction();

        if ($e instanceof BusinessException)
            return $this->json(array(
                'message' => $e->getMessage(),
                'help' => $e->getHelp(),
            ));

        return $this->json(array(
            'message' => 'Something went wrong !',
            'help' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ));
    }

    /**
     * Retrieves the of requested service by name
     * @param string $name
     * @return mixed|ServiceManagerAwareInterface|ServiceLocatorAwareInterface
     */
    public function getService($name) {
        return $this->getServiceLocator()->get($name);
    }

    public function notFoundAction() {
        $this->response->setStatusCode(404);
        return $this->response;
    }

    public function invalidRequestAction() {
        $this->response->setStatusCode(400);
        return $this->response;
    }

}
