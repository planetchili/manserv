<?php
require_once 'MancalaDatabase.php';
require_once 'ChiliGuzz.php';
require_once 'test/ChiliTest.php';

class GameControllerTest extends ChiliDatabaseTest
{
	/** @var MancalaDatabase */
	private $mdb;

	public static function setUpBeforeClass()
	{
		// create SUT conn (also doubles as schema setup conn)
		self::$pdo = new ChiliSql( self::SCHEMA,self::USER,self::PASSWORD );
		// clear schema
		(new MancalaDatabase( self::$pdo ))->ClearSchema();
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
		return new PHPUnit\DbUnit\DataSet\YamlDataSet( dirname(__FILE__)."/DBTestData/ControllerTest.yml" );
	}

	public function setUp()
	{
		parent::setUp();
		$this->mdb = new MancalaDatabase( self::$pdo );
	}
	
	public function testQuery()
	{
		$req = ['cmd' => 'query','userId' => 1,'gameId' => 1];	
		$resp = GuzzPost( 'GameController.php',$req );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
		}
		
		$payload = $resp['payload'];
		$this->assertEquals( 0,$payload['turn'],'bad turn #' );
		$this->assertEquals( 0,$payload['activeSide'],'wrong active side #' );
		$this->assertEquals( 1,$payload['winState'],'wrong win state #' );
		$this->assertEquals( [4,4,4,4,4,4,0,4,4,4,4,4,4,0],$payload['board'],'board does not match' );
		$this->assertEquals( 1,$payload['players'][0]['id'],'bad p0 id' );
		$this->assertEquals( 2,$payload['players'][1]['id'],'bad p1 id' );
		$this->assertEquals( 'chili',$payload['players'][0]['name'],'bad p0 name' );
		$this->assertEquals( 'mom',$payload['players'][1]['name'],'bad p1 name' );
	}
}
?>