<?php

namespace PayPal\Log;

use Psr\Log\LoggerInterface;

/**
 * Class PayPalDefaultLogFactory
 *
 * This factory is the default implementation of Log factory.
 *
 * @package PayPal\Log
 */
class PPDefaultLogFactory implements PPLogFactory
{

    /** @var array|null */
    private $config;

    public function __construct($config = null) {
        $this->config = $config;
    }

    /**
     * Returns logger instance implementing LoggerInterface.
     *
     * @param string $className
     * @return LoggerInterface instance of logger object implementing LoggerInterface
     */
    public function getLogger($className)
    {
        return new PPLogger($className, $this->config);
    }
}
