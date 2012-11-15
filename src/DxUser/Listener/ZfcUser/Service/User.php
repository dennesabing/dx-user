<?php

namespace DxUser\Listener\ZfcUser\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;

class User implements ListenerAggregateInterface
{

	/**
	 * @var \Zend\Stdlib\CallbackHandler[]
	 */
	protected $listeners = array();

	/**
	 * Attach to an event manager
	 *
	 * @param  EventManagerInterface $events
	 * @param  integer $priority
	 */
	public function attach(EventManagerInterface $events, $priority = 1)
	{
		$this->listeners[] = $events->attach('register.pre', array($this, 'preRegister'), $priority);
		$this->listeners[] = $events->attach('register.post', array($this, 'postRegister'), $priority);
	}

	/**
	 * Detach all our listeners from the event manager
	 *
	 * @param  EventManagerInterface $events
	 * @return void
	 */
	public function detach(EventManagerInterface $events)
	{
		foreach ($this->listeners as $index => $listener)
		{
			if ($events->detach($listener))
			{
				unset($this->listeners[$index]);
			}
		}
	}

	/**
	 * Do something Post. ZfcUser Register
	 * @param type $e 
	 */
	public function postRegister($e)
	{
		
	}
	
	/**
	 * Do something Pre. ZfcUser Register
	 * @param type $e 
	 */
	public function preRegister($e)
	{
		
	}
}