<?php
namespace PayPal\Handler;

use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;

interface IPPHandler
{
    /**
     * @param PPHttpConfig $httpConfig
     * @param PPRequest    $request
     * @param array        $options
     *
     * @return
     */
    public function handle($httpConfig, $request, $options);
}
