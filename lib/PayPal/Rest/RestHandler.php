<?php
namespace PayPal\Rest;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Handler\IPPHandler;
use PayPal\Core\PPCredentialManager;
use PayPal\Core\PPConstants;
use PayPal\Exception\PPMissingCredentialException;
use PayPal\Exception\PPInvalidCredentialException;
use PayPal\Exception\PPConfigurationException;
use PayPal\Common\PPUserAgent;

/**
 * 
 * API handler for all REST API calls
 */
class RestHandler implements IPPHandler {

	private $sdkName;	
	private $sdkVersion;
	
	/**
	 * @param string $sdkName
	 * @param string $sdkVersion
	 */
	public function __construct($sdkName='rest-sdk-php', $sdkVersion='') {
		$this->sdkName = $sdkName;
		$this->sdkVersion = $sdkVersion;
	}
	
	public function handle($httpConfig, $request, $options) {
	
		$apiContext = $options['apiContext'];	
		$credential = $apiContext->getCredential();
		$config = $apiContext->getConfig();
		
		if($credential == NULL) {
			
			try {
				// Try picking credentials from the config file
				$credMgr = PPCredentialManager::getInstance($config);
				$credValues = $credMgr->getCredentialObject();
			} catch (PPMissingCredentialException $ex) {
				// Argh: swallow missing credential exception. 
				// We may have come here because the API call does not require
				// authentication and sent an explicit no credentials configuration 
			}
			if(isset($credValues) && is_array($credValues)) {
				$credential = new OAuthTokenCredential($credValues['clientId'], $credValues['clientSecret']);
			}
		}
		
		$httpConfig->setUrl(
			rtrim( trim($this->_getEndpoint($config)), '/') . 
				(isset($options['path']) ? $options['path'] : '')
		);
		
		if(!array_key_exists("User-Agent", $httpConfig->getHeaders())) {
			$httpConfig->addHeader("User-Agent", PPUserAgent::getValue($this->sdkName, $this->sdkVersion));
		}		
		if(!is_null($credential) && $credential instanceof OAuthTokenCredential) {
			$httpConfig->addHeader('Authorization', "Bearer " . $credential->getAccessToken($config));
		}
		if($httpConfig->getMethod() == 'POST' || $httpConfig->getMethod() == 'PUT') {
			$httpConfig->addHeader('PayPal-Request-Id', $apiContext->getRequestId());
		}
		
	}
	
	private function _getEndpoint($config) {
		if (isset($config['service.EndPoint'])) {
			return $config['service.EndPoint'];
		} else if (isset($config['mode'])) {
			switch (strtoupper($config['mode'])) {
				case 'SANDBOX':
					return PPConstants::REST_SANDBOX_ENDPOINT;
					break;
				case 'LIVE':
					return PPConstants::REST_LIVE_ENDPOINT;
					break;
				default:
					throw new PPConfigurationException('The mode config parameter must be set to either sandbox/live');
					break;
			}
		} else {
			throw new PPConfigurationException('You must set one of service.endpoint or mode parameters in your configuration');
		}
	}

}
