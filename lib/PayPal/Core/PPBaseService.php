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
		
	protected $lastRequest;
	protected $lastResponse;

	/**
	 * Compute the value that needs to sent for the PAYPAL_REQUEST_SOURCE
	 * parameter when making API calls
	 */
	public static function getRequestSource()
	{
		return str_replace(" ", "-", self::$SDK_NAME) . "-" . self::$SDK_VERSION;
	}
	
    public function getLastRequest() {
		return $this->lastRequest;
	}
    public function setLastRequest($lastRqst) {
		$this->lastRequest = $lastRqst;
	}
    public function getLastResponse() {
		return $this->lastResponse;
	}
    public function setLastResponse($lastRspns) {
		$this->lastResponse = $lastRspns;
	}

	public function __construct($serviceName, $serviceBinding, $handlers=array()) {
		$this->serviceName = $serviceName;
		$this->serviceBinding = $serviceBinding;
		$this->handlers = $handlers;
	}

	public function getServiceName() {
		return $this->serviceName;
	}

	/**
	 * 
	 * @param string $method - API method to call
	 * @param object $requestObject Request object 
	 * @param apiContext $apiContext object containing credential and SOAP headers
	 * @param mixed $apiUserName - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object created dynamically 		
	 */
	public function call($port, $method, $requestObject,  $apiContext = null, $apiUserName = NULL) {
		if($apiContext == null)
		{
			$apiContext = new PPApiContext(PPConfigManager::getConfigWithDefaults());
		}
 		else if($apiContext->getConfig() == null )
		{
			$apiContext->setConfig(PPConfigManager::getConfigWithDefaults());
		} 
		foreach($this->handlers as $handlerClass) {
			if($handlerClass == 'PayPal\Handler\GenericSoapHandler')
			{
				$handlers[] = new $handlerClass($this->xmlNamespacePrefixProvider());
			}
			else
			{
				$handlers[] = new $handlerClass();
			}
		}
		$service = new PPAPIService($port, $this->serviceName,
				$this->serviceBinding, $apiContext, $handlers);
		$ret = $service->makeRequest($method, $requestObject, $apiUserName);
		$this->lastRequest = $ret['request'];
		$this->lastResponse = $ret['response'];
		return $this->lastResponse;
	}
}