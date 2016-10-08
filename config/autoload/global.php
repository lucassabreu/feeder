<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
return array(
    'db' => array(
        'driver' => 'PDO_MySQL',
        'hostname' => GetEnv('OPENSHIFT_MYSQL_DB_HOST') == null ? "localhost" : GetEnv('OPENSHIFT_MYSQL_DB_HOST'),
        'port' => GetEnv('OPENSHIFT_MYSQL_DB_PORT') == null ? "3306" : GetEnv('OPENSHIFT_MYSQL_DB_PORT'),
        'username' => GetEnv('OPENSHIFT_MYSQL_DB_USERNAME') == null ? "root" : GetEnv('OPENSHIFT_MYSQL_DB_USERNAME'),
        'password' => GetEnv('OPENSHIFT_MYSQL_DB_PASSWORD') == null ? "root" : GetEnv('OPENSHIFT_MYSQL_DB_PASSWORD'),
        'database' => 'feeder',
    ),
    'mongodb' => array(
        'hostname' => GetEnv('OPENSHIFT_MONGODB_DB_HOST') == null ? "localhost" : GetEnv('OPENSHIFT_MONGODB_DB_HOST'),
        'port' => GetEnv('OPENSHIFT_MONGODB_DB_PORT') == null ? "27017" : GetEnv('OPENSHIFT_MONGODB_DB_PORT'),
        'username' => GetEnv('OPENSHIFT_MONGODB_DB_USERNAME') == null ? "mongo" : GetEnv('OPENSHIFT_MONGODB_DB_USERNAME'),
        'password' => GetEnv('OPENSHIFT_MONGODB_DB_PASSWORD') == null ? "mongo" : GetEnv('OPENSHIFT_MONGODB_DB_PASSWORD'),
        'database' => 'feeder',
    ),    
    'acl' => array(
        'roles' => array(
            'guest' => null,
            'user' => 'guest',
        ),
        'resources' => array(
            'Application\Controller\Auth.login',
            'Application\Controller\Auth.logout',
            'Application\Controller\Index.index',
            'Application\Controller\Index.cache',
        ),
        'privilege' => array(
            'guest' => array(
                'allow' => array(
                    'Application\Controller\Auth.login',
                ),
            ),
            'user' => array(
                'allow' => array(
                    'Application\Controller\Auth.logout',
                    'Application\Controller\Index.index',
                    'Application\Controller\Index.cache',
                ),
            ),
        ),
    ),
);
