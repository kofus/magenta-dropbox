<?php
namespace Kofus\Dropbox\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Uri\UriFactory;


class AuthController extends AbstractActionController
{
    
    public function indexAction()
    {
        $uri = UriFactory::factory('https://www.dropbox.com/oauth2/authorize');
        $uri->setQuery(array(
        	'response_type' => 'code',
            'client_id' => 'pryff6oayqwpevb',
        ));
        print $uri; die();
    }
    
}
