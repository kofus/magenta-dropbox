<?php

namespace Kofus\Dropbox\Service;

use Zend\Http\Header;
use Zend\Http\Headers;
use Zend\Json\Json;
use Zend\Http\Client as HttpClient;

use Kofus\System\Service\AbstractService;


class DropboxService extends AbstractService
{
    protected $apiUrls = array(
        
        'api' => 'https://api.dropboxapi.com/2/',
        'content' => 'https://content.dropboxapi.com/2/'
    );
    
    public function content($method, array $params=array())
    {
        $client = $this->getHttpClient();
        $client->setUri($this->apiUrls['content'] . '/' . $method);
        $headers = new Headers();
        $headers->addHeader(new Header\Authorization('Bearer ' . $this->getAccessToken()));
        $headers->addHeader(new Header\GenericHeader('Dropbox-API-Arg', Json::encode($params)));
        $headers->addHeader(new Header\ContentType(''));

        $client->setHeaders($headers);
        $client->setMethod('POST');

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
        $headers->addHeader(new Header\Authorization('Bearer ' . $this->getAccessToken()));
        $headers->addHeader(new Header\ContentType('application/json'));
        $client->setHeaders($headers);
        if ($params)
        	$client->setRawBody(Json::encode($params));
        $client->setMethod('POST');
        $response = $client->send();
        $archive = $this->getServiceLocator()->get('KofusArchiveService');
        $archive->http('dropbox')->add($client);
        
        if ($response->getStatusCode() >= 300)
            throw new \Exception('Dropbox API Exception: ' . $response->getContent());
        $body = $response->getContent();
       	if ($body)
        	return Json::decode($response->getBody(), 1);
    }
    
    public function getAccessToken()
    {
        $accessToken = $this->config()->get('dropbox.access_token');
        if (! $accessToken)
            throw new \Exception('No access token found for dropbox');
        return $accessToken;
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
    
   
    
    protected function getHttpClient()
    {
    	$client = new HttpClient();
    	//if ($this->config()->get('webservice.ppplus.http_client_options', array()))
    		//$client->setOptions($this->config()->get('webservice.ppplus.http_client_options', array()));
    	return $client;
    }
    
    
    
    
    
    

    
    
    
    
	
	
}