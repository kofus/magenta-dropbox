<?php

namespace Kofus\Dropbox\Service;

use Zend\Http\Header;
use Zend\Http\Headers;
use Zend\Json\Json;
use Zend\Http\Client as HttpClient;


use Kofus\System\Service\AbstractService;
use Kofus\Dropbox\Db\Sqlite\DropboxDb;


class DropboxService extends AbstractService
{
    protected $apiUrls = array(
        
        'api' => 'https://api.dropboxapi.com/2/',
        'content' => 'https://content.dropboxapi.com/2/'
    );
    
    public function content($method, array $params=array(), $streamFilename=null)
    {
        $client = $this->getHttpClient();
        $client->setUri($this->apiUrls['content'] . '/' . $method);
        $headers = new Headers();
        $headers->addHeader(new Header\Authorization('Bearer ' . $this->getAccessToken(true)));
        $headers->addHeader(new Header\GenericHeader('Dropbox-API-Arg', Json::encode($params)));
        $headers->addHeader(new Header\ContentType(''));

        $client->setHeaders($headers);
        $client->setMethod('POST');
        if ($streamFilename)
            $client->setStream($streamFilename);

        $response = $client->send();
        $archive = $this->getServiceLocator()->get('KofusArchiveService');
        $archive->http('dropbox')->add($client);
        
        if ($response->getStatusCode() >= 300)
        	throw new \Exception('Dropbox API Exception: ' . $response->getContent());
        return $response->getContent();
        
    }

    public function api($method, array $params=array())
    {
        $client = $this->getHttpClient();
        $client->setUri($this->apiUrls['api'] . '/' . $method);
        $headers = new Headers();
        $headers->addHeader(new Header\Authorization('Bearer ' . $this->getAccessToken(true)));
        $headers->addHeader(new Header\ContentType('application/json'));
        $client->setHeaders($headers);
        if ($params)
        	$client->setRawBody(Json::encode($params));
        $client->setMethod('POST');
        $response = $client->send();
        $archive = $this->getServiceLocator()->get('KofusArchiveService');
        $archive->http('dropbox')->add($client);
        
        if ($response->getStatusCode() >= 300) {
            print_r($headers->toArray()); die();
            throw new \Exception('Dropbox API Exception: ' . $response->getContent());
        }
        $body = $response->getContent();
       	if ($body)
        	return Json::decode($response->getBody(), 1);
    }
    
    protected $accessToken;
    
    public function getAccessToken($throwException=false)
    {
        if (! $this->accessToken) {
            $settings = $this->getServiceLocator()->get('KofusSettings');
            $this->accessToken = $settings->getSystemValue('kofus_dropbox.access_token');
        }
        
        if ($throwException && ! $this->accessToken)
            throw new \Exception('Dropbox access token must be provided before first api call');
        
        return $this->accessToken;
    }
    
    public function setAccessToken($value)
    {
        $this->accessToken = $value;
        
        $settings = $this->getServiceLocator()->get('KofusSettings');
        $settings->setSystemValue('kofus_dropbox.access_token', $value);
        
        return $this;
    }
    
    public function syncDownload()
    {
        if (! is_dir('data/dropbox/files')) 
        	mkdir('data/dropbox/files', 0777, true);
        
        $db = DropboxDb::open('data/dropbox/files.db');
        
        $response = $this->api('files/list_folder', array(
        		'path' => $this->config()->get('dropbox.path', ''),
        		'recursive' => true,
                'include_deleted' => true,
        		'include_media_info' => false
        ));
        
        foreach ($response['entries'] as $entry) {
            $filename = 'data/dropbox/files/' . md5($entry['path_lower']);
            
            if ($entry['.tag'] == 'deleted') {
                $db->deleteLocalFile($entry);
                if (file_exists($filename)) {
                    unlink($filename);
                    print 'Deleted ' . $entry['path_lower'] . '<br>';
                }
                
            } elseif ($entry['.tag'] == 'file') {
            
                $local = $db->getLocalFile($entry['path_lower']);
                
                // Add + download
                if (! $local) {
                    $content = $this->content('files/download', array('path' => $entry['id']));
                    file_put_contents($filename, $content);
                    $db->addLocalFile($entry);
                    print 'Added ' . $entry['path_lower'] . '<br>';
                    
                // Update + download
                } elseif ($local && $local['modified'] < $entry['server_modified']) {
                    $content = $this->content('files/download', array('path' => $entry['id']));
                    file_put_contents($filename, $content);
                    $db->updateLocalFile($entry);
                    print 'Updated ' . $entry['path_lower'] . '<br>';
                }
            }
        }

        foreach ($db->getLocalFiles() as $localfile)
            print $localfile['path'] . '<br>';
        
        die('DONE');
    }
    
    
    const SYNC_MODE_ADD = 2;
    const SYNC_MODE_UPDATE = 3;
    const SYNC_MODE_DELETE = 5;
    
    public function sync(array $options=array())
    {
        // Deploy option defaults
        if (! isset($options['mode'])) 
            $options['mode'] = self::SYNC_MODE_ADD * self::SYNC_MODE_UPDATE;
        if (! isset($options['repository']))
            throw new \Exception('A media file node type must be provided for storing Dropbox files');        
        
    	$response = $this->api('files/list_folder', array(
    			'path' => '',
    			'recursive' => true,
    			'include_media_info' => true
    	));
    	
    	$validator = new \Zend\Validator\ValidatorChain();
    	if (isset($options['validators'])) {
    	   foreach ($options['validators'] as $array) {
    	       if (! isset($array['options'])) $array['options'] = array();
    	       $validator->attachByName($array['name'], $array['options']);
    	   }
    	}
    
    	$finfo = finfo_open(FILEINFO_MIME);
    	$entities = array();
    	$entries = array();
    	$messages = array();
    
    	foreach ($response['entries'] as $entry) {
    
    	    // Skip irrelevant entries according to provided validator
   	        if (! $validator->isValid($entry)) continue;
   	        
    		$entries[$entry['id']] = $entry;
    
    		$entity = $this->nodes()->getRepository($options['repository'])->findOneBy(array('dropboxEntryId' => $entry['id']));
    		if ($entity && $entity->getDropboxRevision() == $entry['rev'])
    			continue;
    
    		// Download
    		$messages[] = 'Downloading ' . $entry['path_lower'];
    
    		$filename = 'data/media/files/' . md5($entry['id']);
    		$content = $this->content('files/download', array('path' => $entry['id']));
    		if (! is_dir(dirname($filename))) {
    		    $success = mkdir(dirname($filename), 0777, true);
    		    if ($success === false)
    		        throw new \Exception('Could not create directory ' . dirname($filename));
    		}
    		$success = file_put_contents($filename, $content);
    		if ($success === false)
    		    throw new \Exception('Could not write file ' . $filename);
    		$mimeType = finfo_file($finfo, $filename);
    
    		// Create entity
    		if (! $entity) {
    			$messages[] = 'Adding ' . $entry['path_lower'];
    			$entity = $this->nodes()->createNode($options['repository']);
    		}
    
    		// Update entity
    		$messages[] = 'Updating ' . $entry['path_lower'];
    		$entity->setDropboxEntryId($entry['id'])
        		->setDropboxMediaInfo($entry)
        		->setFilename(md5($entry['id']))
        		->setFilesize($entry['size'])
        		->setDropboxPath($entry['path_lower'])
        		->setDropboxRevision($entry['rev'])
        		->setDropboxTimestampModified(new \DateTime());
    		if (isset($entry['media_info']['metadata']['dimensions'])) {
    			$entity->setHeight($entry['media_info']['metadata']['dimensions']['height']);
    			$entity->setWidth($entry['media_info']['metadata']['dimensions']['width']);
    		}
    		$entity->setMimeType($mimeType);
    		$this->em()->persist($entity);
    		$this->em()->flush();
    		
    		 
    		$entities[$entry['id']] = $entity;
    	}
    
    	// Delete
    	foreach ($this->nodes()->getRepository($options['repository'])->findAll() as $entity) {
    		if (! isset($entries[$entity->getDropboxEntryId()])) {
    			$messages[] = 'Deleting ' . $entity->getDropboxEntryId();
    			unlink($entity->getPath());
    			$this->getServiceLocator()->get('KofusMediaService')->clearCache($entity);
    			$this->nodes()->deleteNode($entity);
    		}
    	}
    	
    	return $messages;
    
    
    }
    
    public function getImages($path)
    {
        $qb = $this->nodes()->createQueryBuilder('DROPIMG');
        $qb->where('n.dropboxPath LIKE :path')
            ->setParameter('path', $path . '/%');
        return $qb->getQuery()->getResult();
    }
    
   
    
    protected function getHttpClient()
    {
    	$client = new HttpClient();
    	//if ($this->config()->get('webservice.ppplus.http_client_options', array()))
    		//$client->setOptions($this->config()->get('webservice.ppplus.http_client_options', array()));
    	return $client;
    }
    
    
    
    
    
    

    
    
    
    
	
	
}