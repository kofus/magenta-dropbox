<?php
namespace Kofus\Dropbox;

return array(
    'controllers' => array(
        'invokables' => array(
            'Kofus\Calendar\Controller\Index' => 'Kofus\Calendar\Controller\IndexController',
            'Kofus\Calendar\Controller\Calendar' => 'Kofus\Calendar\Controller\CalendarController'
        )
    ),
    'user' => array(
        'controller_mappings' => array(
            'Kofus\Calendar\Controller\Index' => 'Frontend',
            'Kofus\Calendar\Controller\Calendar' => 'Backend'
        )
    ),
    
    'router' => array(
        'routes' => array(
            'kofus_dropbox' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/:language/' . KOFUS_ROUTE_SEGMENT . '/dropbox/:controller/:action[/:id[/:id2]]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'language' => '[a-z][a-z]'
                    ),
                    'defaults' => array(
                        'language' => 'de',
                        '__NAMESPACE__' => 'Kofus\Calendar\Controller'
                    )
                ),
                'may_terminate' => true
            )
        )
    ),
    
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php'
            )
        )
    ),
    /*
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/' . str_replace('\\', '/', __NAMESPACE__) . '/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        )
    ), */
    
    'controller_plugins' => array(
        'invokables' => array()
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'KofusCalendarService' => 'Kofus\Calendar\Service\CalendarService'
        )
    ),
    
    'view_manager' => array(
        'controller_map' => array(
            'Kofus\Dropbox' => true
        ),
        'module_layouts' => array(
            'Kofus\Dropbox' => 'kofus/layout/admin'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);


