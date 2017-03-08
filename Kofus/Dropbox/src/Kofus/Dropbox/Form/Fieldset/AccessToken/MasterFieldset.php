<?php
namespace Kofus\Dropbox\Form\Fieldset\AccessToken;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MasterFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {
        $this->setName('dropbox');
        
        $el = new Element\Text('access_token', array(
            'label' => 'Freischalt-Code'
        ));
        $this->add($el);
        
        $el = new Element\Submit('submit', array('label' => 'Speichern'));
        $this->add($el);
    }

    public function getInputFilterSpecification()
    {
        return array(
            'access_token' => array(
            		'required' => false,
            		'filters' => array(
            				array(
            						'name' => 'Zend\Filter\StringTrim'
            				)
            		)
            ),
        )
        ;
    }

}

