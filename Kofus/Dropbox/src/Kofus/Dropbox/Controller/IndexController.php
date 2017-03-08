<?php
namespace Kofus\Dropbox\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Uri\UriFactory;



class IndexController extends AbstractActionController
{
    
    public function indexAction()
    {
        $this->archive()->uriStack()->push();
        
        $uriDropbox = UriFactory::factory('https://www.dropbox.com/oauth2/authorize');
        $uriDropbox->setQuery(array(
        		'response_type' => 'code',
        		'client_id' => 'pryff6oayqwpevb',
        ));
        
        return new ViewModel(array(
            'dropbox' => $this->dropbox(),
            'uriDropbox' => $uriDropbox
        ));
    }
    
    public function accesstokenAction()
    {
        $form = $this->formBuilder()
            ->addFieldset(new \Kofus\Dropbox\Form\Fieldset\AccessToken\MasterFieldset())
            ->setLabelSize('col-sm-3')->setFieldSize('sm-9')
            ->buildForm();
        
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $accessToken = $form->get('dropbox')->get('access_token')->getValue();
                $this->dropbox()->setAccessToken($accessToken);
                $this->flashMessenger()->addSuccessMessage('Änderungen gespeichert.');
                return $this->redirect()->toUrl($this->archive()->uriStack()->pop());
            }
        } else {
            $form->get('dropbox')->get('access_token')->setValue($this->dropbox()->getAccessToken());
        }
        
        return new ViewModel(array(
        	'form' => $form
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
