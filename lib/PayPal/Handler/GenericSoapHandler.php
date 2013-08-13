<?php
namespace PayPal\Handler;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Auth\PPSubjectAuthorization;
use PayPal\Auth\Oauth\AuthSignature;
use PayPal\Core\PPConstants;
use PayPal\Handler\IPPHandler;

class GenericSoapHandler implements IPPHandler {

	private $namespace;
	
	public function __construct($namespace) {
		$this->namespace = $namespace;
	}
	
	public function handle($httpConfig, $request, $options) {
		$httpConfig->setUrl($options['config']['service.EndPoint']);
		$httpConfig->addHeader('Content-Type', 'text/xml');
		$request->addBindingInfo("namespace", $this->namespace);
		if(isset($options['SOAPHeader']))
		{
			$request->addBindingInfo('SOAPHeader' , $options['SOAPHeader']->toXMLString());
		}
		
	}	
}