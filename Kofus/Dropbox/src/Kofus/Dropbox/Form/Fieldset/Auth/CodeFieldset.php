<?php
namespace Kofus\Dropbox\Form\Fieldset\Auth;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CodeFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {
        $el = new Element\Text('code', array(
            'label' => 'Code'
        ));
        $this->add($el);
        
        $el = new Element\Submit('submit', array('label' => 'Jetzt freischalten'));
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

