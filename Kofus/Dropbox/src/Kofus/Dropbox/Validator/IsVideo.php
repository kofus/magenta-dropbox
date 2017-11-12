<?php

namespace Kofus\Dropbox\Validator;

class IsVideo extends \Zend\Validator\AbstractValidator
{
    const NO_FILE = 'no_file';
    const NO_VIDEO  = 'no_video';

    
    protected $messageTemplates = array(
    		self::NO_FILE => "Dropbox entry is not a file",
    		self::NO_VIDEO  => "Dropbox entry is not a video",
    );
    
    public function isValid($value)
    {
        if (! is_array($value))
            throw new \Exception('Dropbox entry must be an array');
        
    	$this->setValue($value);
    	
    	$isValid = true;
    	
    	$entry = $value;
    	
    	if (! isset($entry['.tag']) 
    	   || $entry['.tag'] != 'file') {
    		  $this->error(self::NO_FILE);
    		  $isValid = false;
    	}
    	
    	if ($isValid) {
    	   $pathInfo = pathinfo($entry['path_lower']);
    	   $extension = $pathInfo['extension'];
    	   if (! in_array($extension, array('m4v', 'mp4', 'webm'))) {
        	   $this->error(self::NO_VIDEO);
        	   $isValid = false;
    	   }
    	   
    	}
    	
    	return $isValid;
    }    
}