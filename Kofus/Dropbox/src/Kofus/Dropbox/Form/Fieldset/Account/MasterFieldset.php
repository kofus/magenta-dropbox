<?php
namespace Kofus\Dropbox\Form\Fieldset\Account;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MasterFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {
        $el = new Element\Text('title', array(
            'label' => 'Konto-Name'
        ));
        $this->add($el);
        
        $el = new Element\Text('access_token', array(
            'label' => 'Freischalt-Code'
        ));
        $this->add($el);
        
        $el = new Element\Checkbox('enabled', array(
            'label' => 'enabled?'
        ));
        $this->add($el);
    }

    public function getInputFilterSpecification()
    {
        return array(
            'title' => array(
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            'access_token' => array(
            		'required' => true,
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

