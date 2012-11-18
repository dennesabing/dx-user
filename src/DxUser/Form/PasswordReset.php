<?php

namespace DxUser\Form;

use Dxapp\Form\ProvidesEventsForm;

class PasswordReset extends ProvidesEventsForm
{
	/**
	 * Constructor
	 * @param string|array $xmlFile array or filename of the xmlFile
	 */
	public function __construct($formName = NULL, $xmlFile = NULL, $moduleOptions = array())
	{
		parent::__construct();
		$this->setName($formName);
		$this->setModuleOptions($moduleOptions);
		$this->formFromXml($this->getModuleOptions()->getXmlFormFolder() . '/' . $xmlFile);
		$this->getEventManager()->trigger('init', $this);
	}	
}
