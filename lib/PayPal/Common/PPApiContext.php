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
	 * 
	 * @var array Dynamic SDK configuration
	 */
	protected $config;
	
	public function setConfig($config) {
		$this->config = PPConfigManager::getConfigWithDefaults($config);
	}
	
	public function getConfig() {
		return $this->config;
	}
	
	public function __construct($config=null) {
		$this->config = PPConfigManager::getConfigWithDefaults($config);
		
	}
}