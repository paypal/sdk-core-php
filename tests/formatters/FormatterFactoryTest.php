<?php
use PayPal\Core\Formatter\FormatterFactory;
class FormatterFactoryTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @test
	 */
	public function testValidBinding() {
		$this->assertEquals('PayPal\Core\Formatter\PPNVPFormatter', get_class(FormatterFactory::factory('NV')));
		$this->assertEquals('PayPal\Core\Formatter\PPSOAPFormatter', get_class(FormatterFactory::factory('SOAP')));
	}
	
	/**
	 * @test
	 */
	public function testInvalidBinding() {
		$this->setExpectedException('\InvalidArgumentException');
		FormatterFactory::factory('Unknown');
	}
}