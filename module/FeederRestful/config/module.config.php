<?php

return array(
    'service_manager' => array(
        'dao_factory' => array(
            'FeederRestful\Service\Feed' => array(
                'service' => 'FeederRestful\Service\FeedService',
                'model' => 'FeederRestful\Model\DAO\MongoDB\FeedMongoDBDAO',
            ),
            'FeederRestful\Service\Entry' => array(
                'service' => 'FeederRestful\Service\EntryService',
                'model' => 'FeederRestful\Model\DAO\MongoDB\EntryMongoDBDAO',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'FeederRestful\Controller\Feeder' => 'FeederRestful\Controller\FeederRestfulAPI',
            'FeederRestful\Controller\FeederSchedule' => 'FeederRestful\Controller\FeedScheduleController',
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'feeder-schedule' => array(
                    'options' => array(
                        'route' => 'run cron [<feedId>]',
                        'defaults' => array(
                            'controller' => 'FeederRestful\Controller\FeederSchedule',
                            'action' => 'schedule'
                        ),
                    ),
                ),
                'feeder-check-url' => array(
                    'options' => array(
                        'route' => 'check-url [<url>]',
                        'defaults' => array(
                            'controller' => 'FeederRestful\Controller\FeederSchedule',
                            'action' => 'check-url'
                        ),
                    ),
                ),
                'feeder-search' => array(
                    'options' => array(
                        'route' => 'search [<param>]',
                        'defaults' => array(
                            'controller' => 'FeederRestful\Controller\FeederSchedule',
                            'action' => 'search'
                        ),
                    ),
                ),
            ),
        ),
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'feeder-api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/fdr/api[/[:collection[/[:id[/[:child[/]]]]]]]',
                    'defaults' => array(
                        'controller' => 'FeederRestful\Controller\Feeder',
                    ),
                ),
            ),
            'feeder-api-authenticate' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/fdr/api/auth',
                    'defaults' => array(
                        'controller' => 'FeederRestful\Controller\Feeder',
                        'action' => 'auth'
                    ),
                ),
            ),
            'feeder-api-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/fdr/api/feed/search[/:q]',
                    'defaults' => array(
                        'controller' => 'FeederRestful\Controller\Feeder',
                        'action' => 'feed-search',
                        'q' => null,
                    ),
                    'contraint' => array(
                        'q' => '',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(//Add this config
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
