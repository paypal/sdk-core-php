<?php
namespace PayPal\Core;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
/**
 * Simple Logging Manager.
 * This does an error_log for now
 * Potential frameworks to use are PEAR logger, log4php from Apache
 */

class PPLoggingLevel {

	// FINE Logging Level
	const FINE = 3;

	// INFO Logging Level
	const INFO = 2;

	// WARN Logging Level
	const WARN = 1;

	// ERROR Logging Level
	const ERROR = 0;
}

class PPLoggingManager {

	// Default Logging Level
	const DEFAULT_LOGGING_LEVEL = 0;

	// Log enabled
	private $isLoggingEnabled;

	// Configured logging level
	private $loggingLevel;

	// Configured logging file
	private $loggerFile;

	//monolog instance
	private $monolog;

	private static $monologObj;

	public function __construct($loggerName, $config = null) {
		if(!isset(self::$monologObj))
		{
			$this->monolog = new Logger($loggerName);
			if($config == null) {
				$config = PPConfigManager::getInstance()->getConfigHashmap();
			}
			$this->isLoggingEnabled = (array_key_exists('log.LogEnabled', $config) && $config['log.LogEnabled'] == '1');
				
			if($this->isLoggingEnabled) {
				$this->loggerFile = ($config['log.FileName']) ? $config['log.FileName'] : ini_get('error_log');
				$loggingLevel = strtoupper($config['log.LogLevel']);
				$this->loggingLevel = (isset($loggingLevel) && defined(__NAMESPACE__."\\PPLoggingLevel::$loggingLevel")) ? constant(__NAMESPACE__."\\PPLoggingLevel::$loggingLevel") : PPLoggingManager::DEFAULT_LOGGING_LEVEL;
			}
		}
	}

	public static function PPlogger($monologObj)
	{
		self::$monologObj = $monologObj;
	}
	public function error($message) {
		if(isset(self::$monologObj))
		{
			self::$monologObj->addError($message);
		}
		else
		{
			$this->monolog->pushHandler(new StreamHandler($this->loggerFile, Logger::ERROR));
			$this->monolog->addError($message);
		}
	}

	public function warning($message) {
		if(isset(self::$monologObj))
		{
			self::$monologObj->addWarning($message);
		}
		else
		{
			$this->monolog->pushHandler(new StreamHandler($this->loggerFile, Logger::WARNING));
			$this->monolog->addWarning($message);
		}
	}

	public function info($message) {
		if(isset(self::$monologObj))
		{
			self::$monologObj->addInfo($message);
		}
		else
		{
			$this->monolog->pushHandler(new StreamHandler($this->loggerFile, Logger::INFO));
			$this->monolog->addInfo($message);
		}
	}

	public function fine($message) {
		if(isset(self::$monologObj))
		{
			self::$monologObj->addDebug($message);
		}
		else
		{
			$this->monolog->pushHandler(new StreamHandler($this->loggerFile, Logger::DEBUG));
			$this->monolog->addDebug($message);
		}
	}

}