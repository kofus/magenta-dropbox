<?php

namespace Kofus\Dropbox\Validator;

class IsImage extends \Zend\Validator\AbstractValidator
{
    const NO_FILE = 'no_file';
    const NO_IMAGE  = 'no_upper';

    
    protected $messageTemplates = array(
    		self::NO_FILE => "Dropbox entry is not a file",
    		self::NO_IMAGE  => "Dropbox entry is not an image",
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
    
    	if (! isset($entry['media_info']['metadata']['.tag'])
            || $entry['media_info']['metadata']['.tag'] != 'photo') {
    	
    	    $this->error(self::NO_IMAGE);
    	    $isValid = false;
    	}
    	
    	return $isValid;
    }    
}