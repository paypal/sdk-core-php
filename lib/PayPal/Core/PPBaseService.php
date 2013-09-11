<?php
namespace PayPal\Core;
use PayPal\Core\PPAPIService;
use PayPal\Common\PPApiContext;

class PPBaseService {

    // SDK Name
	protected  static $SDK_NAME = "paypal-php-sdk";
	// SDK Version
	protected static $SDK_VERSION = "2.1.96";
	
	private $serviceName;
	private $serviceBinding;
	private $handlers;

	protected $config;		
	protected $lastRequest;
	protected $lastResponse;

		
    public function getLastRequest() {
		return $this->lastRequest;
	}
    public function getLastResponse() {
		return $this->lastResponse;
	}

	public function getServiceName() {
		return $this->serviceName;
	}

	public function __construct($serviceName, $serviceBinding, $config=null) {
		$this->serviceName = $serviceName;
		$this->serviceBinding = $serviceBinding;
		$this->config = $config;
	}

	/**
	 * 
	 * @param string $method - API method to call
	 * @param object $requestObject Request object 
	 * @param apiContext $apiContext object containing credential and SOAP headers
	 * @param mixed $apiUserName - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object created dynamically 		
	 */
	public function call($port, $method, $requestObject, $apiContext, $handlers) {

		if($apiContext == null)
		{
			$apiContext = new PPApiContext(PPConfigManager::getConfigWithDefaults($this->config));
		}
 		if($apiContext->getConfig() == null )
		{			
			$apiContext->setConfig(PPConfigManager::getConfigWithDefaults($this->config));
		}
	
		$service = new PPAPIService($port, $this->serviceName,
				$this->serviceBinding, $apiContext, $handlers);
		$ret = $service->makeRequest($method, new PPRequest($requestObject, $this->serviceBinding));
		$this->lastRequest = $ret['request'];
		$this->lastResponse = $ret['response'];
		return $this->lastResponse;
	}


}
