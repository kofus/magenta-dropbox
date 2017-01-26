<?php
namespace Kofus\Dropbox\Form\Hydrator\Account;

use Zend\Stdlib\Hydrator\HydratorInterface;


class MasterHydrator implements HydratorInterface
{
    public function extract($object)
    {
        return array(
            'title' => $object->getTitle(),
            'access_token' => $object->getAccessToken(),
            'enabled' => $object->isEnabled(),
        );
    }

    public function hydrate(array $data, $object)
    {
        $object->setTitle($data['title']);
        $object->setAccessToken($data['access_token']);
        $object->isEnabled($data['enabled']);
        
        return $object;
    }
    
}