<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Core\Controller;

use Core\Model\DAO\DAOInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\View\Http\ViewManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * Base class for controller at application
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
abstract class AbstractController extends AbstractActionController {

    /**
     * DAO instance for controller
     * 
     * @var DAOInterface
     */
    private $dao = null;

    /**
     * Class name of default DAO
     * @var string
     */
    protected $daoName = null;
    
    /**
     * @inherit
     * @var Request
     */
    protected $request;
    
    /**
     * @inherit
     * @var Response
     */
    protected $response;

    /**
     * Retrieves the of requested service by name
     * @param string $name
     * @return mixed|ServiceManagerAwareInterface|ServiceLocatorAwareInterface
     */
    public function getService($name) {
        return $this->getServiceLocator()->get($name);
    }

    /**
     * Render a page based at param <code>$model</code>
     * @param ViewModel|array|mixed $model
     * @param string $layout Layout to be used
     */
    public function renderModel($model) {

        if (is_array($model)) {
            $model = new ViewModel($model);
        }

        $viewManager = $this->getService('ViewManager');
        /* @var $viewManager ViewManager */

        $renderer = new PhpRenderer();
        $renderer->setResolver($viewManager->getResolver());
        $renderer->setHelperPluginManager($viewManager->getHelperManager());

        return $renderer->render($model);
    }

    /**
     * Retrieves a DAO instance
     * @return DAOInterface
     */
    public function dao($name = null) {

        if ($name === null) {
            if ($this->dao === null)
                $this->dao = $this->getService($this->daoName);

            return $this->dao;
        }
        else
            return $this->getService($name);
    }

    public function indexAction() {
        return new ViewModel();
    }


}

?>