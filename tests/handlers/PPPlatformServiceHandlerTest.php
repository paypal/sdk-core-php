<?php
use PayPal\Auth\PPSignatureCredential;
use PayPal\Core\PPConstants;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;
use PayPal\Handler\PPPlatformServiceHandler;

class PPPlatformServiceHandlerTest extends PHPUnit_Framework_TestCase {
	
	protected function setup() {
		
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testHeadersAdded() {
		
		$options = array('config' => array('mode' => 'sandbox'), 'serviceName' => 'AdaptivePayments', 'apiMethod' => 'ConvertCurrency');
		$req = new PPRequest(new StdClass(), 'NV');
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler();
		$handler->handle($httpConfig, $req, $options);		
		
		$this->assertEquals(4, count($httpConfig->getHeaders()), "Basic headers not added");
		
		$cred = new PPSignatureCredential('user', 'pass', 'sig');
		$cred->setApplicationId('appId');
		$req->setCredential($cred);
		$handler->handle($httpConfig, $req, $options);
		
		$this->assertEquals(5, count($httpConfig->getHeaders()), "Application Id header not added.");
	}
	
	/**
	 * @test
	 */
	public function testEndpoint() {
		$serviceName = 'AdaptivePayments';
		$apiMethod = 'ConvertCurrency';
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler();
		
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				array('config' => array('mode' => 'sandbox'), 'serviceName' => $serviceName, 'apiMethod' => $apiMethod)
		);
		$this->assertEquals(PPConstants::PLATFORM_SANDBOX_ENDPOINT . "$serviceName/$apiMethod", $httpConfig->getUrl());
		
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				array('config' => array('mode' => 'live'), 'serviceName' => $serviceName, 'apiMethod' => $apiMethod)
		);
		$this->assertEquals(PPConstants::PLATFORM_LIVE_ENDPOINT . "$serviceName/$apiMethod", $httpConfig->getUrl());
		
		
		$customEndpoint = 'http://myhost/';
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				array('config' => array('service.EndPoint' => $customEndpoint), 'serviceName' => $serviceName, 'apiMethod' => $apiMethod)
		);
		$this->assertEquals("$customEndpoint$serviceName/$apiMethod", $httpConfig->getUrl(), "Custom endpoint not processed");
		
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				array('config' => array())
		);
	}
	
}