<?php
return array(
    'nodes' => array(

        'available' => array(
            'DROPIMG' => array(
                'label' => 'Image (Dropbox)',
                'entity' => 'Kofus\Dropbox\Entity\DropboxImageEntity',
                'controllers' => array(
                    'Kofus\Dropbox\Controller\Browser'
                ),
                /*
                'form' => array(
                    'default' => array(
                        'fieldsets' => array(
                            'master' => array(
                                'class' => 'Kofus\Calendar\Form\Fieldset\Calendar\MasterFieldset',
                                'hydrator' => 'Kofus\Calendar\Form\Hydrator\Calendar\MasterHydrator'
                            ),
                        )
                    )
                ) */
            ),

            
        )
        
    )
);