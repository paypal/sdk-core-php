<?php

namespace PayPal\Core;

use PayPal\Log\PayPalLogFactory;
use PayPal\Log\PPLogFactory;
use Psr\Log\LoggerInterface;

/**
 * Simple Logging Manager.
 * This can be configured with any PSR logger via log.AdapterFactory config
 */
class PPLoggingManager
{
    /**
     * The logger to be used for all messages
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Logger Name
     *
     * @var string
     */
    private $loggerName;

    /**
     * Config map
     */
    private $config;

    public function __construct($loggerName, $config = null)
    {
        $config = PPConfigManager::getInstance()->getConfigWithDefaults($config);
        // Checks if custom factory defined, and is it an implementation of @PPLogFactory
        $factory = '\PayPal\Log\PPDefaultLogFactory';
        if (array_key_exists('log.AdapterFactory', $config)
            && in_array('PayPal\Log\PPLogFactory', class_implements($config['log.AdapterFactory']))) {
            $factory = $config['log.AdapterFactory'];
        }

        /** @var PPLogFactory $factoryInstance */
        $factoryInstance = new $factory($config);
        $this->logger = $factoryInstance->getLogger($loggerName);
        $this->loggerName = $loggerName;
        $this->config = $config;
    }

    /**
     * Log Error
     *
     * @param string $message
     */
    public function error($message)
    {
        $this->logger->error($message);
    }

    /**
     * Log Warning
     *
     * @param string $message
     */
    public function warning($message)
    {
        $this->logger->warning($message);
    }

    /**
     * Log Info
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->logger->info($message);
    }

    /**
     * Log Fine
     *
     * @param string $message
     */
    public function fine($message)
    {
        $this->info($message);
    }

    /**
     * Log Debug
     *
     * @param string $message
     */
    public function debug($message)
    {
        // Disable debug in live mode.
        if (array_key_exists('mode', $this->config) && $this->config['mode'] != 'live') {
            $this->logger->debug($message);
        }
    }

    /**
     * Fetch the logger
     */
    public function getLogger()
    {
        return $this->logger;
	}
}
