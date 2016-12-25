<?php

namespace Kofus\Dropbox\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Http\Client as HttpClient;
use Zend\Json\Json;
use Zend\Http\Header;
use Zend\Http\Headers;


class BrowserController extends AbstractActionController
{
    public function listAction()
    {
        $path = $this->config()->get('dropbox.path');

        $response = $this->dropbox()->api('files/list_folder', array(
            'path' => '',
            'recursive' => true,
            'include_media_info' => true
        ));
        return new ViewModel(array(
        	'entries' => $response['entries']
        ));
    }
    
    public function syncAction()
    {
        $response = $this->dropbox()->api('files/list_folder', array(
        		'path' => '',
        		'recursive' => true,
        		'include_media_info' => true
        ));
        foreach ($response['entries'] as $entry) {
           
            $filename = 'data/dropbox' . $entry['path_lower'];
            if (! file_exists($filename)) {
                $tmp = $this->dropbox()->api('files/download', array(
                	'path' => $entry['id']
                ));
                print_r($tmp); die();
            }
            print $filename . '<br>'; 
        }
        die();
    }
    
}
