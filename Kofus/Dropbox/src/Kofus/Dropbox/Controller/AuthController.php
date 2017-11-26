<?php
namespace Kofus\Dropbox\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController
{
    public function indexAction()
    {
        // Assemble uri
        $uri = \Zend\Uri\UriFactory::factory('https://www.dropbox.com/oauth2/authorize');
        $uri->setQuery(array('response_type' => 'code', 'client_id' => 'rzx6l3vm4veqdtj'));
        
        // Form
        $form = $this->fb()->setConfig(array(
            'sections' => array(
                'code' => array(
                    'fieldset' => 'Kofus\Dropbox\Form\Fieldset\Auth\CodeFieldset',
                )
            ),
            'element_options' => array(
                'column-size' => 'sm-12',
                'label_attributes' => array(
                    'class' => 'col-sm-12'
                )
            )
        ))->buildForm();
        
        // Handle submit
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $value = $form->get('code')->get('code')->getValue();
                $response = $this->dropbox()->oauth2(array(
                    'code' => $value,
                    'grant_type' => 'authorization_code'
                ));
                $this->dropbox()->setAccessToken($response['access_token']);
            }
        }
        
        
        return new ViewModel(array(
            'uri' => $uri,
            'form' => $form,
            'accessToken' => $this->dropbox()->getAccessToken(false)
        ));
        
    }

    
}
