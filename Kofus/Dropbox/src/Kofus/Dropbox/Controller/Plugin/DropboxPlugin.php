<?php

namespace Kofus\Dropbox\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;


class DropboxPlugin extends AbstractPlugin
{
    protected $plugin;
    
    public function __invoke()
    {
        if (! $this->plugin) 
            $this->plugin = $this->getController()->getServiceLocator()->get('KofusDropboxService');
        return $this->plugin;
	}

}