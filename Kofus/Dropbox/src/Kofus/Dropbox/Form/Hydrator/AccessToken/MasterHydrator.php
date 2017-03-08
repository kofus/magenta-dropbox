<?php
namespace Kofus\Dropbox\Form\Hydrator\AccessToken;

use Zend\Stdlib\Hydrator\HydratorInterface;


class MasterHydrator implements HydratorInterface
{
    public function extract($object)
    {
        return array(
       
        );
    }

    public function hydrate(array $data, $object)
    {
        print_r($data); die();
        
        return $object;
    }
    
}