<?php
use PayPal\Common\PPApiContext;
use PayPal\Core\PPConstants;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;
use PayPal\Handler\PPOpenIdHandler;

class PPOpenIdHandlerTest extends PHPUnit_Framework_TestCase {
	
	protected function setup() {
		
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testInvalidConfiguration() {
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'unknown', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler($apiContext);
	
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$handler->handle($httpConfig, 'payload', array('path' => '/path'));
		
		
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler($apiContext);
		
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$handler->handle($httpConfig, 'payload', array('path' => '/path'));
	}
	
	/**
	 * @test
	 */
	public function testHeadersAdded() {
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'sandbox', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		
		$handler = new PPOpenIdHandler($apiContext);
		$handler->handle($httpConfig, 'payload', array());
		
		$this->assertArrayHasKey('Authorization', $httpConfig->getHeaders());
		$this->assertArrayHasKey('User-Agent', $httpConfig->getHeaders());			
		$this->assertContains('PayPalSDK', $httpConfig->getHeader('User-Agent'));
	}
	
	/**
	 * @test
	 */
	public function testModeBasedEndpoint() {
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'sandbox', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));		
		$handler = new PPOpenIdHandler($apiContext);
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path'));		
		$this->assertEquals(PPConstants::REST_SANDBOX_ENDPOINT . "path", $httpConfig->getUrl());
		
		
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'live', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler($apiContext);
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path'));
		$this->assertEquals(PPConstants::REST_LIVE_ENDPOINT . "path", $httpConfig->getUrl());
	}
	
	/**
	 * @test
	 */
	public function testCustomEndpoint() {
		$customEndpoint = 'http://mydomain';
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('service.EndPoint' => $customEndpoint, 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler($apiContext);
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path'));
		$this->assertEquals("$customEndpoint/path", $httpConfig->getUrl());
		
		
		$customEndpoint = 'http://mydomain/';
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('service.EndPoint' => $customEndpoint, 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler($apiContext);
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path'));
		$this->assertEquals("${customEndpoint}path", $httpConfig->getUrl());
		
		
		$customEndpoint = 'http://mydomain';
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('service.EndPoint' => 'xyz', 'openid.EndPoint' => $customEndpoint, 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler($apiContext);
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path'));
		$this->assertEquals("$customEndpoint/path", $httpConfig->getUrl());
	}
	
}