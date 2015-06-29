<?php

/**
 * Test class for PPArrayUtil.
 *
 */
class PPArrayUtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testIsArrayAssocEmptyArray()
    {
    	$this->assertFalse(PPArrayUtil::isAssocArray(array()));
    }
}