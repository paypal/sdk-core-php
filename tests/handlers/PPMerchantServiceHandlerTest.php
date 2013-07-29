<?php
use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPCertificateCredential;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Handler\PPMerchantServiceHandler;
use PayPal\Core\PPConstants;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;

class PPMerchantServiceHandlerTest extends PHPUnit_Framework_TestCase {
	
	protected function setup() {
		
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testHeadersAdded() {
		
		$options = array('config' => array('mode' => 'sandbox'), 'serviceName' => 'DoExpressCheckout', 'port' => 'apiAA');
		$req = new PPRequest(new StdClass(), 'SOAP');
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler();
		$handler->handle($httpConfig, $req, $options);		
		
		$this->assertEquals(4, count($httpConfig->getHeaders()), "Basic headers not added");
		
	}
	
	/**
	 * @test
	 */
	public function testModeBasedEndpointForSignatureCredential() {
		$apiMethod = 'DoExpressCheckout';
		$port = 'apiAA';
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler();
		$req = new PPRequest(new StdClass(), 'SOAP');
		$req->setCredential(new PPSignatureCredential('a', 'b', 'c'));
		
		$handler->handle($httpConfig, $req,
			array('config' => array('mode' => 'sandbox'), 'apiMethod' => $apiMethod, 'port' => $port)
		);
		$this->assertEquals(PPConstants::MERCHANT_SANDBOX_SIGNATURE_ENDPOINT, $httpConfig->getUrl());
		
		
		$handler->handle($httpConfig, $req,
				array('config' => array('mode' => 'live'), 'apiMethod' => $apiMethod, 'port' => $port)
		);
		$this->assertEquals(PPConstants::MERCHANT_LIVE_SIGNATURE_ENDPOINT, $httpConfig->getUrl());
		
		
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NVP'),
				array('config' => array())
		);
	}
	
	
	/**
	 * @test
	 */
	public function testModeBasedEndpointForCertificateCredential() {
		$apiMethod = 'DoExpressCheckout';
		$port = 'apiAA';
	
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler();
		$req = new PPRequest(new StdClass(), 'SOAP');
		$req->setCredential(new PPCertificateCredential('a', 'b', 'c'));
	
		$handler->handle($httpConfig, $req,
				array('config' => array('mode' => 'sandbox'), 'apiMethod' => $apiMethod, 'port' => $port)
		);
		$this->assertEquals(PPConstants::MERCHANT_SANDBOX_CERT_ENDPOINT, $httpConfig->getUrl());
	
	
		$handler->handle($httpConfig, $req,
				array('config' => array('mode' => 'live'), 'apiMethod' => $apiMethod, 'port' => $port)
		);
		$this->assertEquals(PPConstants::MERCHANT_LIVE_CERT_ENDPOINT, $httpConfig->getUrl());
	
	
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NVP'),
				array('config' => array())
		);
	}
	
	
	public function testCustomEndpoint() {
		$apiMethod = 'DoExpressCheckout';
		$port = 'apiAA';
		$customEndpoint = 'http://myhost/';
		$options = array('config' => array('service.EndPoint' => $customEndpoint), 'apiMethod' => $apiMethod, 'port' => $port);
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler();
		
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'SOAP'), $options			
		);
		$this->assertEquals("$customEndpoint", $httpConfig->getUrl(), "Custom endpoint not processed");
	
		$options['config']['service.EndPoint'] = 'abc';
		$options['config']["service.EndPoint.$port"] = $customEndpoint;
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'SOAP'), $options
		);
		$this->assertEquals("$customEndpoint", $httpConfig->getUrl(), "Custom endpoint not processed");
	
	}
	
}