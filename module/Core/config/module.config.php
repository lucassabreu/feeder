<?php

namespace Core;

use Zend\Session\Container;
use Zend\Session\SessionManager;

return array(
    'service_manager' => array(
        'factories' => array(
            'DbAdapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Session\SessionManager' => function ($sm) {
                $config = $sm->get('config');
                if (isset($config['session'])) {
                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                        $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                        $sessionConfig = new $class();
                        $sessionConfig->setOptions($options);
                    }

                    $sessionStorage = null;
                    if (isset($session['storage'])) {
                        $class = $session['storage'];
                        $sessionStorage = new $class();
                    }

                    $sessionSaveHandler = null;
                    if (isset($session['save_handler'])) {
                        // class should be fetched from service manager since it will require constructor arguments
                        $sessionSaveHandler = $sm->get($session['save_handler']);
                    }

                    $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                    if (isset($session['validator'])) {
                        $chain = $sessionManager->getValidatorChain();
                        foreach ($session['validator'] as $validator) {
                            $validator = new $validator();
                            $chain->attach('session.validate', array($validator, 'isValid'));
                        }
                    }
                } else {
                    $sessionManager = new SessionManager();
                }
                Container::setDefaultManager($sessionManager);
                return $sessionManager;
            },
            'Core\MongoDB' => function($sl) {
                $config = $sl->get("Config");

                if (!isset($config['mongodb']))
                    return null;

                $connConfig = $config['mongodb'];

                if (class_exists('Mongo'))
                    $mongoClientClass = 'Mongo';
                else
                    $mongoClientClass = 'MongoClient';

                $connectionString = "";

                if (isset($connConfig['username']))
                    $connectionString = "mongodb://{$connConfig['username']}:{$connConfig['password']}@"
                    . "{$connConfig['hostname']}:{$connConfig['port']}";
                else
                    $connectionString = "mongodb://{$connConfig['hostname']}:{$connConfig['port']}";

                if (isset($connConfig['database']))
                    $connectionString .= "/" . $connConfig['database'];

                $mongoClient = new $mongoClientClass($connectionString);

                if (isset($connConfig['database']))
                    return $mongoClient->{$connConfig['database']};
                else
                    return $mongoClient;
            },
        ),
        'abstract_factories' => array(
            'Core\Service\Factory\DAOServiceFactory'
        ),
        'invokables' => array(
            'Core\Acl\Builder' => 'Core\Acl\Builder',
            'Core\Service\Util\MailUtilService' => 'Core\Service\Util\MailUtilService',
        ),
        'dao_services' => array(
            'Admin\Service\UserDAOService' => array(
                'service' => 'Admin\Service\UserDAOService',
                'model' => 'Admin\Model\Doctrine\UserDAODoctrine',
            ),
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'formSelect' => 'Core\View\Helper\Elements\FormSelect',
            'stripContent' => 'Core\View\Helper\StripContentHelper',
            'ztbFormButton' => 'Core\View\Helper\ZTB\ZTBFormButton',
            'ztbFormPrepare' => 'Core\View\Helper\ZTB\ZTBFormPrepare',
        ),
    ),
);
