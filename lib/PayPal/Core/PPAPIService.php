<?php
namespace PayPal\Core;
use PayPal\Core\PPLoggingManager;
use PayPal\Formatter\FormatterFactory;
use PayPal\Core\PPRequest;
use PayPal\Core\PPHttpConfig;
use PayPal\Handler\PPAuthenticationHandler;
use PayPal\Auth\PPTokenAuthorization;

class PPAPIService {

	public $endpoint;
	public $config;	
	public $serviceName;
	public $apiContext;
	private $logger;
	private $handlers = array();
	private $serviceBinding;
	private $port;
	private $apiMethod;
	private $SOAPHeader;
	
	public function __construct($port, $serviceName, $serviceBinding, $apiContext, $handlers=array()) {
		$this->apiContext = $apiContext;
		$this->config = $apiContext->getConfig();
		$this->SOAPHeader = $apiContext->getSOAPHeader();
		$this->serviceName = $serviceName;
		$this->port = $port;

		$this->logger = new PPLoggingManager(__CLASS__, $this->config);
		$this->handlers = $handlers;
		$this->serviceBinding = $serviceBinding;
		
	}

	public function setServiceName($serviceName) {
		$this->serviceName = $serviceName;
	}

	/**
	 * Register additional handlers to run before
	 * executing this call
	 *
	 * @param IPPHandler $handler
	 */
	public function addHandler($handler) {
		$this->handlers[] = $handler;
	}

	/**
	 * Execute an api call
	 *
	 * @param string $apiMethod	Name of the API operation (such as 'Pay')
	 * @param object $params Request object 
	 * @param string $apiUsername
	 *
	 * @return array containing request and response
	 */
	public function makeRequest($apiMethod, $params, $apiUsername = null) {
		
		$this->apiMethod = $apiMethod;
		if(is_string($apiUsername) || is_null($apiUsername)) {
			// $apiUsername is optional, if null the default account in config file is taken
			$credMgr = PPCredentialManager::getInstance($this->config);
			$apiCredential = clone($credMgr->getCredentialObject($apiUsername ));
		} else {
			$apiCredential = $apiUsername; //TODO: Aargh
		}
	    if((isset($this->config['accessToken']) && isset($this->config['tokenSecret']))) {
			$apiCredential->setThirdPartyAuthorization(
					new PPTokenAuthorization($this->config['accessToken'], $this->config['tokenSecret']));
		}


		// Set up request object / headers and run handlers
		$request = new PPRequest($params, $this->serviceBinding);
		$request->setCredential($apiCredential);
		$httpConfig = new PPHttpConfig(null, PPHttpConfig::HTTP_POST);
		if($this->apiContext->getHttpHeaders() != null) {
			$httpConfig->setHeaders($this->apiContext->getHttpHeaders());
		}
		$this->runHandlers($httpConfig, $request);

		
		// Serialize request object to a string according to the binding configuration
		$formatter = FormatterFactory::factory($this->serviceBinding);
		$payload = $formatter->toString($request);
		
		// Execute HTTP call
		$connection = PPConnectionManager::getInstance()->getConnection($httpConfig, $this->config);
		$this->logger->info("Request: $payload");
		$response = $connection->execute($payload);
		$this->logger->info("Response: $response");

		return array('request' => $payload, 'response' => $response);
	}

	private function runHandlers($httpConfig, $request) {
	
		$options = $this->getOptions();
		
		foreach($this->handlers as $handlerClass) {
			$handlerClass->handle($httpConfig, $request, $options);
		}
	}
	
	private function getOptions()
	{
		return array(
			'port'=> $this->port,
			'serviceName' => $this->serviceName,
			'serviceBinding' => $this->serviceBinding,
			'config' => $this->config,
			'apiMethod' => $this->apiMethod,
			'SOAPHeader' => $this->SOAPHeader
		);
	}	
}
