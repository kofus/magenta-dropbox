<?php
namespace Kofus\Dropbox\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Uri\UriFactory;



class AccountController extends AbstractActionController
{
    
    public function listAction()
    {
        $this->archive()->uriStack()->push();
        
        $uriDropbox = UriFactory::factory('https://www.dropbox.com/oauth2/authorize');
        $uriDropbox->setQuery(array(
        		'response_type' => 'code',
        		'client_id' => 'pryff6oayqwpevb',
        ));
        
        $entities = $this->nodes()->getRepository('DROPACC')->findAll();
        return new ViewModel(array(
        	'accounts' => $entities,
            'uriDropbox' => $uriDropbox
        ));
    }
    
    public function syncAction()
    {
        $account = $this->nodes()->getNode($this->params('id'), 'DROPACC');
        $this->dropbox()
            ->setAccessToken($account->getAccessToken())
            ->syncDownload();        
        
        
    }
    
}
