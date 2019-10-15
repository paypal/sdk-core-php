<?php
namespace PayPal\Core;

class PPConnectionManager
{
    /**
     * reference to singleton instance
     * @var PPConnectionManager
     */
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new PPConnectionManager();
        }
        return self::$instance;
    }

    /**
     * This function returns a new PPHttpConnection object
     */
    public function getConnection($httpConfig, $config)
    {
        /** @var PPHttpConfig $httpConfig */
        if (isset($config["http.ConnectionTimeOut"])) {
            $httpConfig->setHttpConnectionTimeout($config["http.ConnectionTimeOut"]);
        }
        if (isset($config["http.TimeOut"])) {
            $httpConfig->setHttpTimeout($config["http.TimeOut"]);
        }
        if (isset($config["http.Proxy"])) {
            $httpConfig->setHttpProxy($config["http.Proxy"]);
        }
        if (isset($config["http.Retry"])) {
            $retry = $config["http.Retry"];
            $httpConfig->setHttpRetryCount($retry);
        }
        if (isset($config["http.RetryInformCallback"])) {
            $retryCallback = $config["http.RetryInformCallback"];
            $httpConfig->setRetryInformCallback($retryCallback);
        }

        return new PPHttpConnection($httpConfig, $config);
    }

}
