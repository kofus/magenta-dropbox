<?php
namespace Kofus\Dropbox\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorAwareInterface;


class BrowserController extends AbstractActionController
{
    
    protected $documentRoot = 'data/dropbox/';
    
    public function indexAction()
    {
        $this->archive()->uriStack()->push();
        $path = (string) urldecode($this->params('id'));
        $response = $this->dropbox()->api('files/list_folder', array(
        		'path' => $path,
        		'recursive' => false,
        		'include_media_info' => true
        ));
        return new ViewModel(array(
        	'entries' => $response['entries'],
            'response' => $response
        ));
    }
    
    public function downloadAction()
    {
        $path = (string) urldecode($this->params('id'));
        if (! preg_match('/id\:([a-zA-Z0-9]+)/', $path, $matches))
            throw new \Exception('Not a valid dropbox id: ' . $path);
            
        $dropboxId = $matches[1];
        
        $filename = $this->documentRoot . $dropboxId;
        
        $content = $this->dropbox()->content('files/download', array('path' => $path));
        if (! is_dir(dirname($filename))) {
        	$success = mkdir(dirname($filename), 0777, true);
        	if ($success === false)
        		throw new \Exception('Could not create directory ' . dirname($filename));
        }
        $success = file_put_contents($filename, $content);
        if ($success === false)
        	throw new \Exception('Could not write file ' . $filename);
        
        
        return $this->redirect()->toUrl($this->archive()->uriStack()->pop());
    }
    
    
}
