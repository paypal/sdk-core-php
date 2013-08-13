<?php
namespace PayPal\Common;
use PayPal\Core\PPConfigManager;
/**
 * 
 * Container for Call level parameters such as
 * SDK configuration 
 */
class PPApiContext {
	
	/**
	 * @var array Dynamic SDK configuration
	 */
	protected $config;
	
	/**
	 * @var custom SOAPHeader 
	 */
	private $SOAPHeader;
	
	private $httpHeaders;
	
	public function setHttpHeaders($httpHeaders) {
		$this->httpHeaders = $httpHeaders;
		return $this;
	}
	
	public function getHttpHeaders() {
		return $this->httpHeaders;
	}
	
	public function setSOAPHeader($SOAPHeader) {
		$this->SOAPHeader = $SOAPHeader;
		return $this;
	}
	
	public function getSOAPHeader() {
		return $this->SOAPHeader;
	}
	
	public function setConfig($config) {
		$this->config = PPConfigManager::getConfigWithDefaults($config);
		return $this;
	}
	
	public function getConfig() {
		return $this->config;
	}
	
	public function __construct($config=null) {
		$this->config = PPConfigManager::getConfigWithDefaults($config);
	}
}