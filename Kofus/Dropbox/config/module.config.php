<?php
namespace Kofus\Dropbox;

return array(
    
    'controllers' => array(
        'invokables' => array(
            'Kofus\Dropbox\Controller\Auth' => 'Kofus\Dropbox\Controller\AuthController',
        )
    ),
    'user' => array(
        'controller_mappings' => array(
            'Kofus\Dropbox\Controller\Auth' => 'KofusDropbox',
        )
    ),
    
    'router' => array(
        'routes' => array(
            'kofus_dropbox' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/:language/' . KOFUS_ROUTE_SEGMENT . '/dropbox[/:controller[/:action[/:id]]]',
                    'constraints' => array(
                        'language' => '[a-z][a-z]'
                    ),
                    'defaults' => array(
                        'language' => 'de',
                        '__NAMESPACE__' => 'Kofus\Dropbox\Controller',
                        'controller' => 'index',
                        'action' => 'index',
                    )
                )
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
    ), 
    
    'controller_plugins' => array(
        'invokables' => array(
            'dropbox' => 'Kofus\Dropbox\Controller\Plugin\DropboxPlugin'
        )
    ),
    
    'service_manager' => array(
        'invokables' => array(
            'KofusDropboxService' => 'Kofus\Dropbox\Service\DropboxService',
            'KofusDropboxSyncCron' => 'Kofus\Dropbox\Cron\DropboxSyncCron',
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


