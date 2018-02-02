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

	/** @dataProvider dataFailQuery */
	public function testFailQuery( array $post,string $diag )
	{
		$resp = GuzzPost( 'GameController.php',$post );
		$this->assertTrue( $resp['status']['isFail'],'was supposed to fail' );
		$this->assertContains( $diag,$resp['status']['message'],
			'failure diagnostic not appropriate',true
		);		
	}
	public function dataFailQuery() : array
	{
		return [
			[['cmd' => 'butts','userId' => 1,'gameId' => 1],'command'],
			[['cmd' => 'query','userId' => 69,'gameId' => 1],'user'],
			[['cmd' => 'query','userId' => 1,'gameId' => 69],'game'],
			[[                 'userId' => 1,'gameId' => 69],'cmd not set'],
			[['cmd' => 'query',              'gameId' => 69],'userId not set'],
			[['cmd' => 'query','userId' => 1               ],'gameId not set']
		];
	}

	/** @dataProvider dataMove */
	public function testMove( array $sequence )
	{
		foreach( $sequence as $move )
		{
			$req = $move['req'];
			$exp = $move['exp'];

			$resp = GuzzPost( 'GameController.php',$req );
			if( $resp['status']['isFail'] )
			{
				$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
			}
			
			$payload = $resp['payload'];
			$this->assertEquals( $exp['turn'],$payload['turn'],'bad turn #' );
			$this->assertEquals( $exp['activeSide'],$payload['activeSide'],'wrong active side #' );
			$this->assertEquals( $exp['winState'],$payload['winState'],'wrong win state #' );
			$this->assertEquals( $exp['board'],$payload['board'],'board does not match' );
		}
	}
	public function dataMove() : array
	{
		return [
			'simple one move' =>
			[
				[
					['req' => ['cmd' => 'move','userId' => 1,'gameId' => 1,'pot' => 0],
					'exp' => ['turn' => 1,'activeSide' => 1,'winState' => 1,'board' =>
						[0,5,5,5,5,4,0,4,4,4,4,4,4,0]]]
				]
			],
			'mancala open' =>
			[
				[
					['req' => ['cmd' => 'move','userId' => 1,'gameId' => 1,'pot' => 2],
					'exp' => ['turn' => 1,'activeSide' => 0,'winState' => 1,'board' =>
						[4,4,0,5,5,5,1,4,4,4,4,4,4,0]]],
					['req' => ['cmd' => 'move','userId' => 1,'gameId' => 1,'pot' => 5],
					'exp' => ['turn' => 2,'activeSide' => 1,'winState' => 1,'board' =>
						[4,4,0,5,5,0,2,5,5,5,5,4,4,0]]]
				]
			]
		];
	}
}
?>