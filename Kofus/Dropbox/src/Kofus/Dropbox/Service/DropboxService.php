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
        //$archive = $this->getServiceLocator()->get('KofusArchiveService');
        //$archive->http('dropbox')->add($client);
        
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
            print_r($headers->toArray()); 
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
            $this->accessToken = $settings->getSystemValue('dropbox.access_token');
        }
        
        if ($throwException && ! $this->accessToken)
            throw new \Exception('Dropbox access token must be provided before first api call');
        
        return $this->accessToken;
    }
    
    public function setAccessToken($value)
    {
        $this->accessToken = $value;
        
        $settings = $this->getServiceLocator()->get('KofusSettings');
        $settings->setSystemValue('dropbox.access_token', $value);
        
        return $this;
    }
    
   
    
    
    const SYNC_MODE_ADD = 2;
    const SYNC_MODE_UPDATE = 3;
    const SYNC_MODE_DELETE = 5;
    
    protected function getEntries($path)
    {
        
        $response = $this->api('files/list_folder', array(
        		'path' => $path,
        		'recursive' => true,
        		'include_media_info' => false
        ));

        $entries = $response['entries'];
        
        while ($response['has_more']) {
            $response = $this->api('files/list_folder/continue', array(
            		'cursor' => $response['cursor']
            ));
            $entries = array_merge($entries, $response['entries']);
        }
        return $entries;
    }
    
    public function sync(array $options=array())
    {
        // Deploy option defaults
        if (! isset($options['mode'])) 
            $options['mode'] = self::SYNC_MODE_ADD * self::SYNC_MODE_UPDATE;
        if (! isset($options['repository']))
            throw new \Exception('A media file node type must be provided for storing Dropbox files');        
        
 

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
    	
    	$allEntries = $this->getEntries('/IT/Junge Operette/Bilder/Dropbox-App');
    	

    	foreach ($allEntries as $entry) {
    	    
    	    // Skip irrelevant entries according to provided validator
   	        if (! $validator->isValid($entry)) 
   	            continue;
   	        
    		$entries[$entry['path_lower']] = $entry;
    
    		$entryEntity = $this->nodes()->getRepository('DBE')->findOneBy(array('path' => $entry['path_lower']));
    		if (! $entryEntity)
    		    $entryEntity = $this->nodes()->createNode('DBE');
		    if ($entryEntity->getRevision() == $entry['rev'])
		        continue;
    
    		// Download
    		print str_pad('DOWNLOAD', 15) . $entry['path_lower'] . "\n";
    
    		$filename = 'data/media/files/' . md5($entry['path_lower']);
    		if (! is_dir(dirname($filename))) {
    		    $success = mkdir(dirname($filename), 0777, true);
    		    if ($success === false)
    		        throw new \Exception('Could not create directory ' . dirname($filename));
    		}
    		$this->content('files/download', array('path' => $entry['path_lower']), $filename);
    		//$success = file_put_contents($filename, $content);
    		//if ($success === false)
    		    //throw new \Exception('Could not write file ' . $filename);
    		$mimeType = finfo_file($finfo, $filename);
    		
    		
   		    $fileEntity = $entryEntity->getFile();
    
    		// Create entity
    		if (! $fileEntity) {
    			print str_pad('ADD', 15) . $entry['path_lower'] . "\n";
    			$fileEntity = $this->nodes()->createNode($options['repository']);
    		} else {
    		    print str_pad('UPDATE', 15) . $entry['path_lower'] . "\n";
    		}
    		
    
    		// Update entity
    		$pathInfo = pathinfo($entry['name']);
    		$fileEntity
    		  ->setFilename(md5($entry['path_lower']))
    		  ->setTitle($pathInfo['filename'])
    		  ->setFilesize($entry['size'])
    		;
    		
    		$entryEntity
        		->setMediaInfo($entry)
        		->setPath($entry['path_lower'])
        		->setRevision($entry['rev'])
        		->setTimestampModified(new \DateTime())
        		->setFile($fileEntity)
    		;
    		if (isset($entry['media_info']['metadata']['dimensions'])) {
    			$fileEntity->setHeight($entry['media_info']['metadata']['dimensions']['height']);
    			$fileEntity->setWidth($entry['media_info']['metadata']['dimensions']['width']);
    		}
    		$fileEntity->setMimeType($mimeType);
    		$this->em()->persist($fileEntity);
    		$this->em()->persist($entryEntity);
    		$this->em()->flush();
    		
    		 
    		$entities[$entry['path_lower']] = $entryEntity;
    	}
    
    	// Delete
    	foreach ($this->nodes()->getRepository('DBE')->findAll() as $entryEntity) {
    		if (! isset($entities[$entryEntity->getPath()])) {
    			print 'Deleting ' . $entryEntity->getPath() . ' ' . $fileEntity->getTitle() . ' ' . $fileEntity->getPath() . PHP_EOL;
                unlink($fileEntity->getPath());
    			
    			$this->getServiceLocator()->get('KofusMediaService')->clearCache($fileEntity);
    			$this->nodes()->deleteNode($entryEntity);
    			$this->nodes()->deleteNode($fileEntity);
    		}
    	}
    	
    	return array();
    	
    }
    
    public function getImages($path)
    {
        $qb = $this->nodes()->createQueryBuilder('DBE');
        $qb->where('n.path LIKE :path')
            ->setParameter('path', $path . '/%');
        
        $images = array();
        foreach ($qb->getQuery()->getResult() as $entryEntity) {
            $file = $entryEntity->getFile();
            if ($file instanceof \Kofus\Media\Entity\ImageEntity)
                $images[] = $file; 
        }
            
        return $images;
    }
    
   
    
    protected function getHttpClient()
    {
    	$client = new HttpClient();
    	//if ($this->config()->get('webservice.ppplus.http_client_options', array()))
    		//$client->setOptions($this->config()->get('webservice.ppplus.http_client_options', array()));
    	return $client;
    }
    
    
    
    
    
    

    
    
    
    
	
	
}