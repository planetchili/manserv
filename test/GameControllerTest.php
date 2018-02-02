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
		foreach( $sequence as $i => $move )
		{
			$req = $move['req'];
			$exp = $move['exp'];

			$resp = GuzzPost( 'GameController.php',$req );
			if( $resp['status']['isFail'] )
			{
				$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
			}
			
			$payload = $resp['payload'];
			$this->assertEquals( $exp['turn'],$payload['turn'],'bad turn #'.' @seq ('.$i );
			$this->assertEquals( $exp['activeSide'],$payload['activeSide'],'wrong active side #'.' @seq ('.$i );
			$this->assertEquals( $exp['winState'],$payload['winState'],'wrong win state #'.' @seq ('.$i );
			$this->assertEquals( $exp['board'],$payload['board'],'board does not match'.' @seq ('.$i );
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
			],
			'bottom wins' =>
			[
				[
					['req' => ['cmd' => 'move','userId' => 1,'gameId' => 2,'pot' => 1],
					'exp' => ['turn' => 26,'activeSide' => 1,'winState' => 1,'board' =>
						[0,0,0,1,0,0,22,0,1,0,0,0,0,24]]],
					['req' => ['cmd' => 'move','userId' => 2,'gameId' => 2,'pot' => 8],
					'exp' => ['turn' => 27,'activeSide' => 0,'winState' => 3,'board' =>
						[0,0,0,0,0,0,22,0,0,0,0,0,0,26]]],
				]
			],
			'tie' =>
			[
				[
					['req' => ['cmd' => 'move','userId' => 1,'gameId' => 2,'pot' => 3],
					'exp' => ['turn' => 26,'activeSide' => 1,'winState' => 4,'board' =>
						[0,0,0,0,0,0,24,0,0,0,0,0,0,24]]]
				]
			]
		];
	}

	/** @dataProvider dataFailMove */
	public function testFailMove( array $req,string $diag )
	{
		$resp = GuzzPost( 'GameController.php',$req );
		if( $resp['status']['isFail'] )
		{
			$this->assertContains( $diag,$resp['status']['message'],
				'failure diagnostic not appropriate',true
			);	
		}
		else 
		{
			$this->fail( 'expected error not emitted' );
		}
	}
	public function dataFailMove() : array
	{
		return [
			'bad userId' 		=> [['cmd' => 'move','userId' => 8,'gameId' => 1,'pot' => 0],'user'],
			'not player pot'	=> [['cmd' => 'move','userId' => 1,'gameId' => 1,'pot' => 8],'pot'],
			'not player turn' 	=> [['cmd' => 'move','userId' => 2,'gameId' => 1,'pot' => 8],'turn'],
			'no pot'	 		=> [['cmd' => 'move','userId' => 2,'gameId' => 1           ],'pot'],
			'bad gameId'	 	=> [['cmd' => 'move','userId' => 1,'gameId' =>69,'pot' => 0],'LoadGame id']
		];
	}

	public function testUpdate()
	{
		// setup
		GuzzPost( 'GameController.php',['cmd' => 'move','userId' => 1,'gameId' => 1,'pot' => 2] );
		GuzzPost( 'GameController.php',['cmd' => 'move','userId' => 1,'gameId' => 1,'pot' => 5] );

		// execute command under test, check for error
		$resp = GuzzPost( 'GameController.php',['cmd' => 'update','userId' => 2,'gameId' => 1,'turn' => 0] );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
		}
		
		// verify results
		$payload = $resp['payload'];
		$this->assertFalse( $payload['upToDate'],'bad upToDate' );
		$this->assertEquals( ['turn' => 0,'pot' => 2],$payload['moves'][0],'bad first history move' );		
	}
}
?>