<?php

namespace DxUser\Form;

use ZfcBase\InputFilter\ProvidesEventsInputFilter;
use ZfcUser\Module as ZfcUser;
use ZfcUser\Options\RegistrationOptionsInterface;
use Zend\InputFilter\InputFilter;

class PasswordResetFilter extends ProvidesEventsInputFilter
{
    protected $options;

    public function __construct($emailValidator, $options)
    {
        $this->setOptions($options);
		$fsMain = new InputFilter();
        $fsMain->add(array(
            'name'       => 'email',
            'required'   => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress'
                ),
                $emailValidator
            ),
        ));
		$this->add($fsMain, 'fsMain');

        $this->getEventManager()->trigger('init', $this);
    }

    /**
     * set options
     *
     * @param $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * get options
     *
     * @return
     */
    public function getOptions()
    {
        return $this->options;
    }
}
