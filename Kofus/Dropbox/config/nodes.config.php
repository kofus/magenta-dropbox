<?php
return array(
    'nodes' => array(
        'enabled' => array('DROPACC'),
        
        'available' => array(
            'DROPIMG' => array(
                'label' => 'Image (Dropbox)',
                'entity' => 'Kofus\Dropbox\Entity\DropboxImageEntity',
                'controllers' => array(
                    'Kofus\Dropbox\Controller\Browser'
                ),
            ),
            'DROPACC' => array(
                'label' => 'Dropbox Account',
                'label_pl' => 'Dropbox Accounts',
                'entity' => 'Kofus\Dropbox\Entity\DropboxAccountEntity',
                'controllers' => array(
                    'Kofus\Dropbox\Controller\Account'
                ),
                 'form' => array(
                 		'default' => array(
                 				'fieldsets' => array(
                 						'master' => array(
                 								'class' => 'Kofus\Dropbox\Form\Fieldset\Account\MasterFieldset',
                 								'hydrator' => 'Kofus\Dropbox\Form\Hydrator\Account\MasterHydrator'
                 						),
                 				)
                 		)
                 ) 
                
            )
        )
        
    )
    
);