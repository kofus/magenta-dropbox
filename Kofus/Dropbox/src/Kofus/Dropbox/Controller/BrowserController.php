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
        
        $finfo = finfo_open(FILEINFO_MIME);
        
        
        foreach ($response['entries'] as $entry) {
            if ($entry['.tag'] != 'file') continue;
            print $entry['id']; 
            
            $content = $this->dropbox()->content('files/download', array('path' => $entry['id']));
            $filename = 'data/media/uploads/' . md5($entry['id']);
            file_put_contents($filename, $content);
            $mimeType = finfo_file($finfo, $filename);
            
            $entity = $this->nodes()->getRepository('DROPIMG')->findOneBy(array('dropboxEntryId' => $entry['id']));
            if (! $entity)
                $entity = new \Kofus\Dropbox\Entity\DropboxImageEntity();
            $entity->setDropboxEntryId($entry['id']);
            $entity->setDropboxMediaInfo($entry);
            $entity->setFilename(md5($entry['id']));
            $entity->setFilesize($entry['size']);
            if (isset($entry['media_info']['metadata']['dimensions'])) {
                $entity->setHeight($entry['media_info']['metadata']['dimensions']['height']);
                $entity->setWidth($entry['media_info']['metadata']['dimensions']['width']);
            }
            $entity->setMimeType($mimeType);

            $this->em()->persist($entity);
        }
        $this->em()->flush();
        die();
    }
    
}
