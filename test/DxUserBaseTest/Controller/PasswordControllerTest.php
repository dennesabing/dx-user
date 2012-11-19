<?php

namespace DxUserBaseTest\Controller;

use Dx\PHPUnit\BaseTestCase;
use DxUser\Controller\PasswordController;

class PasswordControllerTest extends BaseTestCase
{

	public function setup()
	{
		$this->dropDb = FALSE;
		$this->entities = array(
			'DxCdRace\Entity\User',
			'DxUser\Entity\UserCodes'
		);
		parent::setup();
		$controller = new PasswordController();
		$controller->getPluginManager()
				->setInvokableClass('zfcuserauthentication', 'ZfcUser\Controller\Plugin\ZfcUserAuthentication');
	}

	public function testForgottenPassword()
	{
//		$response = $this->dispatch('DxUser\Controller\Password', 'password', FALSE);
//		$this->response = NULL;
//        $this->controller = new PasswordController();
//        $this->request    = new Request();
//        $this->routeMatch = new RouteMatch(array('controller' => 'DxUser\Controller\Password'));
//		$this->routeMatch->setParam('action', 'password');
//        $this->event      = new MvcEvent();
//        $this->event->setRouteMatch($this->routeMatch);
//        $this->controller->setEvent($this->event);		
//		
//        $result = $this->controller->dispatch($this->request, $this->response);
//        $response = $this->controller->getResponse();
//		$this->assertEquals(200, $response->getStatusCode());
//		$url = '/password';
//		$this->dispatch($url, FALSE);
//		$this->controller = new PasswordController();
//		$this->event =  $this->application->getMvcEvent();
//		$this->request = $this->event->getRequest();
//		$this->routeMatch = $this->eventManager->trigger(MvcEvent::EVENT_ROUTE, $this->event)->first();
//		$this->event->setRouteMatch($this->routeMatch);
//		$this->controller->setEvent($this->event);
		
//		$this->routeMatch = $this->eventManager->trigger(MvcEvent::EVENT_ROUTE, $this->event)->first();
//		$this->viewModel = $this->eventManager->trigger(MVcEvent::EVENT_DISPATCH, $this->event)->first();
//		$responseEvent = ($renderView ? MvcEvent::EVENT_RENDER : MvcEvent::EVENT_FINISH);
//		$this->response = $this->eventManager->trigger($responseEvent, $this->event)->first();
		
//		$this->routeMatch->setParam('action', 'password');
//		$this->request->setUri('/password');
//		$result = $this->controller->dispatch($this->request);
//		$response = $this->controller->getRespones();
//		$this->assertEquals(200, $response->getStatusCode());
//		$this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
//		$vars = $result->getVariables();
		
//		$url = '/password';
//		$this->dispatch($url);
	}

//	public function testSubmitForgottenPassword()
//	{
//		$url = '/password';
//		$this->dispatch($url);
//
//		// fetch content of the page 
//		$html =$this->reponse->getBody();
//
//		// parse page content, find the hash value prefilled to the hidden element
//		$dom = new Zend_Dom_Query($html);
//		$csrf = $dom->query('#csrf')->current()->getAttribute('value');
//
//		// reset tester for one more request
//		$this->resetRequest()->resetResponse();
//
//		// now include $csrf value parsed from form, to the next request
//		$this->request->setMethod('POST')
//				->setPost(array('title' => 'MyNewTitle',
//					'body' => 'Body',
//					'csrf' => $csrf));
//		$this->dispatch($url);
//	}

	public function hashPassword($password)
	{
		$zfUserOption = $this->getServiceManager()->get('zfcuser_module_options');
		$bcrypt = new Bcrypt;
		$bcrypt->setCost($zfUserOption->getPasswordCost());
		$pass = $bcrypt->create($password);
		return $pass;
	}

	public function getUserJuan()
	{
		$u = new User();
		$u->setEmail('juan@amigoas.com');
		$u->setUsername($u->getEmail());
		$u->setDisplayName('Juan Tamad');
		$u->setPassword($this->hashPassword('abc123'));
		return $u;
	}

}