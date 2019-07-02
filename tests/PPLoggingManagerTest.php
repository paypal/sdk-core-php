<?php

use PayPal\Log\PPLogFactory;
use PayPal\Log\PPLogger;
use PayPal\Core\PPLoggingManager;
use PHPUnit\Framework\TestCase;
/**
 * Test class for PPLoggingManager.
 */
class PPLoggingManagerTest extends TestCase
{
    /**
     * @var PPLoggingManager
     */
    protected $logging_manager;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->logging_manager = new PPLoggingManager('InvoiceTest');
    }

    /**
     * @test
     */
    public function testError()
    {
    	 $this->logging_manager->error('Test Error Message');
    }

    /**
     * @test
     */
    public function testWarning()
    {
         $this->logging_manager->warning('Test Warning Message');
    }

    /**
     * @test
     */
    public function testInfo()
    {
        $this->logging_manager->info('Test info Message');
    }

    /**
     * @test
     */
    public function testFine()
    {
       $this->logging_manager->fine('Test fine Message');
    }

    /**
     * @test
     */
    public function testGetLogger()
    {
        $logger = $this->logging_manager->getLogger();
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $logger);
        $this->assertInstanceOf('\PayPal\Log\PPLogger', $logger);
    }

    /**
     * @test
     */
    public function testGetLoggerConfigured()
    {
        $factory = 'TestLogFactory';
        $this->assertContains('PayPal\Log\PPLogFactory', class_implements($factory));
        $logging_manager = new PPLoggingManager('InvoiceTest', array(
            'log.AdapterFactory' => $factory
        ));

        $logger = $logging_manager->getLogger();
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $logger);
        $this->assertInstanceOf('\TestLogger', $logger);
    }
}

class TestLogFactory implements PPLogFactory {
    public function getLogger($className) {
        return new TestLogger($className);
    }
}

class TestLogger extends PPLogger {
}