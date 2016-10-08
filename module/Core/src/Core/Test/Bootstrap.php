<?php

namespace Core\Test;

use Core\Test\Bootstrap;
use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Bootstrap class for application's test.
 * 
 * That class is based on concepts learned on course ZF2 na prática
 * (http://code-squad.com/curso/zf2-na-pratica)
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
final class Bootstrap {

    /**
     * @var Bootstrap
     */
    private static $instance = null;

    /**
     * @var array
     */
    protected $modules = null;

    /**
     * @var string
     */
    protected $rootDirectory = null;

    /**
     * @var Application
     */
    protected $application = null;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var MvcEvent
     */
    protected $event = null;

    /**
     * @var array
     */
    protected $routes = null;

    /**
     * @var array
     */
    protected $configuration = null;

    /**
     * @var array
     */
    protected $testConfiguration = null;

    /**
     * Return the instance of <code>\Core\Test\Bootstrap</code>
     * @param string $directory
     * @param string $module
     * @return Bootstrap
     */
    public static function getInstance($modules = null) {
        if (self::$instance === null)
            self::$instance = new Bootstrap($modules);

        return self::$instance;
    }

    /**
     * Constructor of class
     * @param string $modules Modules to test or NULL for all.
     */
    private function __construct($modules = null) {
        $this->modules = $modules;
        $this->rootDirectory = realpath(substr(__DIR__, 0, strlen(__DIR__) - strlen(__NAMESPACE__)) . '../../../');
    }

    /**
     * Initialize the tests
     * @return Bootstrap
     */
    public function init() {
        chdir($this->getRootDirectory());

        include 'init_autoloader.php';

        define('ZF2_PATH', realpath('vendor/zendframework/zendframework/library'));

        $path = array(
            ZF2_PATH,
            get_include_path(),
        );
        set_include_path(implode(PATH_SEPARATOR, $path));

        require_once 'Zend/Loader/AutoloaderFactory.php';
        require_once 'Zend/Loader/StandardAutoloader.php';

        // setup autoloader
        AutoloaderFactory::factory(
                array(
                    'Zend\Loader\StandardAutoloader' => array(
                        StandardAutoloader::AUTOREGISTER_ZF => true,
                        StandardAutoloader::ACT_AS_FALLBACK => false,
                        StandardAutoloader::LOAD_NS => array(
                            'Core' => getcwd() . '/module/Core/src/Core'
                        )
                    )
                )
        );

        return $this;
    }

    public function reset() {
        $this->application = null;
        $this->serviceManager = null;
        $this->event = null;
        $this->routes = null;
    }

    /**
     * At test module's name
     * @return string
     */
    public function getModuleName() {
        return $this->moduleName;
    }

    /**
     * Returns a module's root directory.
     * @param string $name Name of module (if not set then use the Module at test)
     * 
     * @return string
     */
    public function getModuleRoot($name = null, $config = null) {
        if ($name === null)
            $name = $this->getModuleName();

        return $this->getRootDirectory() . '/module/' . $name;
    }

    /**
     * Root directory of application
     * @return string
     */
    public function getRootDirectory() {
        return $this->rootDirectory;
    }

    /**
     * Return the processed configuration array.
     * @return array
     */
    public function getConfiguration() {
        if ($this->configuration == null) {
            $config = include 'config/application.config.php';
            $config['module_listener_options']['config_static_paths'] = array(getcwd() . '/config/test.config.php');

            if (file_exists(__DIR__ . '/config/test.config.php')) {
                $moduleConfig = include __DIR__ . '/config/test.config.php';
                array_unshift($config['module_listener_options']['config_static_paths'], $moduleConfig);
            }
            $this->configuration = $config;
        }

        return $this->configuration;
    }

    /**
     * Retuns the current application.
     * @return Application
     */
    public function getApplication() {

        if ($this->application === null) {
            $config = $this->getConfiguration();

            $this->serviceManager = new ServiceManager(new ServiceManagerConfig(
                                    isset($config['service_manager']) ? $config['service_manager'] : array()
                    ));
            $this->serviceManager->setService('ApplicationConfig', $config);
            $this->serviceManager->setFactory('ServiceListener', 'Zend\Mvc\Service\ServiceListenerFactory');

            $moduleManager = $this->serviceManager->get('ModuleManager');
            $moduleManager->loadModules();
            $this->routes = array();
            foreach ($moduleManager->getLoadedModules() as $key => $m) {
                $moduleConfig = $m->getConfig();
                if (isset($moduleConfig['router'])) {
                    foreach ($moduleConfig['router']['routes'] as $key => $name) {
                        $this->routes[$key] = $name;
                    }
                }
            }

            if ($this->testConfiguration === null) {
                $this->testConfiguration = array();
                $moduleKeys = array();
                foreach ($this->modules as $value)
                    $moduleKeys[$value] = 0;
                
                $testModuleConfig = array();
                foreach ($moduleManager->getModules() as $module) {
                    if (array_key_exists($module, $moduleKeys)) {
                        if (file_exists($this->getRootDirectory() . "/module/$module/data/test.data.php")) {
                            $testModuleConfig =
                                    include_once $this->getRootDirectory() . "/module/$module/data/test.data.php";
                            foreach ($testModuleConfig as $table => $values) {
                                $this->testConfiguration[$table] = $values;
                            }
                        }
                    }
                }
            }

            $this->serviceManager->setAllowOverride(true);

            $this->application = $this->serviceManager->get('Application');
            $this->event = new MvcEvent();
            $this->event->setTarget($this->application);
            $this->event->setApplication($this->application)
                    ->setRequest($this->application->getRequest())
                    ->setResponse($this->application->getResponse())
                    ->setRouter($this->serviceManager->get('Router'));

//            $this->application = Application::init($this->getConfiguration());
//            $this->serviceManager = $this->application->getServiceManager();
//            $this->event = $this->application->getMvcEvent();
        }

        return $this->application;
    }

    /**
     * Retrieves the test configuration
     * @return array
     */
    public function getTestConfig() {
        return $this->testConfiguration;
    }

    /**
     * Returns the current <code>ServiceManager</code>
     * @return ServiceManager
     */
    public function getServiceManager() {
        return $this->serviceManager;
    }

    /**
     * Returns the current <code>MvcEvent</code>.
     * @return MvcEvent
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * Returns the current routes list.
     * @return array
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Retrieves a array with test configuration.
     * @return array
     */
    public function getTestConfiguration() {
        return $this->testConfiguration;
    }

}

