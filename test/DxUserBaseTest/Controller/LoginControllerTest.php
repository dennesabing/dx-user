<?php

namespace DxUserBaseTest\Controller;

use Dx\PHPUnit\BaseTestCase;
use DxUser\Controller\LoginController;

class LoginControllerTest extends BaseTestCase
{

	public function testAddActionCanBeAccessed()
	{
		$this->controller = new LoginController();
		$this->routeMatch->setParam('action', 'login');
		$this->routeMatch->setParam('id', '1'); //Add this Row

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
	}
}