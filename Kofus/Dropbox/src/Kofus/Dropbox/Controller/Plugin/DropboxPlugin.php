<?php

namespace Kofus\Dropbox\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class DropboxPlugin extends AbstractPlugin
{
    protected $service;
    
    public function __invoke()
    {
    	if (! $this->service)
    		$this->service = $this->getController()->getServiceLocator()->get('KofusDropboxService');
    	return $this->service;
    }
    
}