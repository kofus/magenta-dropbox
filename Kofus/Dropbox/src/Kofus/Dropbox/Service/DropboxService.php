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
        
        'default' => 'https://api.dropboxapi.com/2/',
        'files/download' => 'https://content.dropboxapi.com/2/'
    );

    public function api($method, array $params=array())
    {
        $client = $this->getHttpClient();
        if (isset($this->apiUrls[$method])) {
            $url = $this->apiUrls[$method];
        } else {
            $url = $this->apiUrls['default'];
        }
        $client->setUri($url . '/' . $method);
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
            throw new \Exception('Dropbox API Exception');
        $body = $response->getBody();
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
    
   
    
    protected function getHttpClient()
    {
    	$client = new HttpClient();
    	//if ($this->config()->get('webservice.ppplus.http_client_options', array()))
    		//$client->setOptions($this->config()->get('webservice.ppplus.http_client_options', array()));
    	return $client;
    }
    
    
    
    
    
    

    
    
    
    
	
	
}