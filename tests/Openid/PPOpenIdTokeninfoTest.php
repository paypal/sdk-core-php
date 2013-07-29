<?php 
use PayPal\Auth\Openid\PPOpenIdTokeninfo;
/**
 * Test class for PPOpenIdTokeninfo.
 *
 */
class PPOpenIdTokeninfoTest extends \PHPUnit_Framework_TestCase {
	
	public $token;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->token = new PPOpenIdTokeninfo();
		$this->token->setAccessToken("Access token")
					->setExpiresIn(900)
					->setRefreshToken("Refresh token")
					->setIdToken("id token")
					->setScope("openid address")
					->setTokenType("Bearer");
	}
	
	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}
	
	/**
	 * @test
	 */
	public function testSerializationDeserialization() {				
		$tokenCopy = new PPOpenIdTokeninfo();
		$tokenCopy->fromJson($this->token->toJson());
		
		$this->assertEquals($this->token, $tokenCopy);
	}
	
	/**
	 * @test
	 */
	public function testInvalidParamAuthCodeCall() {
		$this->setExpectedException('PayPal\Exception\PPConnectionException');
		PPOpenIdTokeninfo::createFromAuthorizationCode(array());
	
	}
	
	/**
	 * @test
	 */
	public function testInvalidParamRefreshTokenCall() {
	
		$this->setExpectedException('PayPal\Exception\PPConnectionException');
		$token = new PPOpenIdTokeninfo();
		$token->setRefreshToken('refresh');
		$token->createFromRefreshToken(array());
	
	}
}