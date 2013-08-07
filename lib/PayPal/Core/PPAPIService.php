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
	private $securityHeader;
	public function __construct($port, $serviceName, $serviceBinding, $handlers=array(), $apiContext) {
		$this->apiContext = $apiContext;
		$this->config = $apiContext->getConfig();
		$this->securityHeader = $apiContext->securityHeader;
		$this->serviceName = $serviceName;
		$this->port = $port;

		$this->logger = new PPLoggingManager(__CLASS__, $this->config);
		$this->handlers = $handlers;
		$this->serviceBinding = $serviceBinding;
		
	}

	public function setServiceName($serviceName) {
		$this->serviceName = $serviceName;
	}

	public function addHandler($handler) {
		$this->handlers[] = $handler;
	}

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


		$request = new PPRequest($params, $this->serviceBinding);
		$request->setCredential($apiCredential);
		$httpConfig = new PPHttpConfig(null, PPHttpConfig::HTTP_POST);
		if(isset($this->apiContext->httpHeaders))
		{
			$httpConfig->setHeaders($this->apiContext->httpHeaders);
		}
		$this->runHandlers($httpConfig, $request);

		$formatter = FormatterFactory::factory($this->serviceBinding);
		$payload = $formatter->toString($request);
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
			'securityHeader' => $this->securityHeader
		);
	}	
}
