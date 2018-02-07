<?php
require_once 'ChiliGuzz.php';
require_once 'test/ChiliTest.php';

class LoginControllerTest extends ChiliDatabaseTest
{
    /** @var MancalaDatabase */
    private $mdb;

    public static function setUpBeforeClass()
    {
        // create SUT conn (also doubles as schema setup conn)
        self::$pdo = new ChiliSql( self::SCHEMA,self::USER,self::PASSWORD );
        // clear schema
        self::$pdo->exec( 'DROP table if exists games,boards;' );
        // setup schema
        (new MancalaDatabase( self::$pdo ))->SetupSchema();
    }

    public static function tearDownAfterClass()
    {
        // cleanup schema
        // drop games,boards
        (new MancalaDatabase( self::$pdo ))->ClearSchema();
        // cleanup SUT conn
        self::$pdo = null;
    }

    protected function getDataSet()
    {
        return new PHPUnit\DbUnit\DataSet\YamlDataSet( dirname(__FILE__)."/_Fixture.yml" );
    }

    public function setUp()
    {
        parent::setUp();
        $this->mdb = new MancalaDatabase( self::$pdo );
	}
	
	public function testLogin()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$resp = GuzzPost( 'LoginController.php',$req );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
		}
		
		$this->assertEquals(
			['id'=>1,'name'=>'chili','email'=>'chili@planetchili.net'],
			$resp['payload']
		);
	}

	public function testGetUserId()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'getuser'];
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'getid: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$this->assertEquals(
			['id'=>1,'name'=>'chili','email'=>'chili@planetchili.net'],
			$resp['payload']
		);
	}

	/** @doesNotPerformAssertions */
	public function testLoginLogout()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'logout'];
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'getid: response status [fail] with: '.$resp['status']['message'] );
		}
	}

	public function testFailLogin()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilibutt'];
		$resp = GuzzPost( 'LoginController.php',$req );
		$this->assertTrue( $resp['status']['isFail'],'should have failed bad pass' );

		$req = ['cmd' => 'login','userName' => 'chirteli','password' => 'chilipass'];
		$resp = GuzzPost( 'LoginController.php',$req );
		$this->assertTrue( $resp['status']['isFail'],'should have failed bad name' );
	}

	public function testFailDoubleLogin()
	{
		$jar = GuzzMakeJar();
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		$this->assertFalse( $resp['status']['isFail'],'failed to login correctly' );

		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		$this->assertTrue( $resp['status']['isFail'],'should have failed double login' );
	}

	public function testFailLogout()
	{
		$jar = GuzzMakeJar();
		$req = ['cmd' => 'logout'];
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		$this->assertTrue( $resp['status']['isFail'],'should have failed logout' );
	}

	public function testGetUserIdAfterLogout()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'logout'];
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'getid: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'getuserid'];
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		$this->assertTrue( $resp['status']['isFail'],'should have failed getuid after logout' );		
	}
}
?>