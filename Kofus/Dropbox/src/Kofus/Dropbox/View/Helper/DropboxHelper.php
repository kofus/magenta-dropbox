<?php

namespace Kofus\Dropbox\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;



class DropboxHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    public function __invoke()
    {
    	return $this->getServiceLocator()->get('KofusDropboxService');
    }
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
    	$this->sm = $serviceLocator;
    }
    
    public function getServiceLocator()
    {
    	return $this->sm->getServiceLocator();
    }
    
    
}


