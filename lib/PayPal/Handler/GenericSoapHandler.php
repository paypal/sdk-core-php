<?php
namespace PayPal\Handler;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Auth\PPSubjectAuthorization;
use PayPal\Auth\Oauth\AuthSignature;
use PayPal\Core\PPConstants;
use PayPal\Handler\IPPHandler;
use Urn\Ebay\Api\PayPalAPI\PayPalAPIInterfaceService;

class GenericSoapHandler implements IPPHandler {

	private $namespace;
	
	public function __construct($namspace) {
		$this->namespace = $namspace;
	}
	
	public function handle($httpConfig, $request, $options) {
		$httpConfig->setUrl($options['config']['service.EndPoint']);
		$httpConfig->addHeader('Content-Type', 'text/xml');
		$request->addBindingInfo("namespace", "$this->namespace");
		if(isset($options['securityHeader']))
		{
			$request->addBindingInfo('securityHeader' , $options['securityHeader']->toXMLString());
		}
		
	}	
}