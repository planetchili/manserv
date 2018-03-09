<?php
require_once 'MancalaDatabase.php';
require_once 'ChiliGuzz.php';
require_once 'test/ChiliTest.php';

/** @group gCtrl */
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
		return new PHPUnit\DbUnit\DataSet\YamlDataSet( dirname(__FILE__)."/_Fixture.yml" );
	}

	public function setUp()
	{
		parent::setUp();
		$this->mdb = new MancalaDatabase( self::$pdo );
	}
	
	public function testQuery()
	{
		$jar = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'chili','password'=>'chilipass'],$jar );

		$req = ['cmd' => 'query','userId' => 1,'gameId' => 1];	
		$resp = GuzzPost( 'GameController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
		}
		
		$payload = $resp['payload'];
		$this->assertEquals( 0,$payload['turn'],'bad turn #' );
		$this->assertEquals( 0,$payload['activeSide'],'wrong active side #' );
		$this->assertEquals( 1,$payload['winState'],'wrong win state #' );
		$this->assertEquals( [4,4,4,4,4,4,0,4,4,4,4,4,4,0],$payload['board'],'board does not match' );
		$this->assertEquals( [],$payload['history'],'history does not match' );
		$this->assertEquals( 1,$payload['players'][0]['id'],'bad p0 id' );
		$this->assertEquals( 2,$payload['players'][1]['id'],'bad p1 id' );
		$this->assertEquals( 'chili',$payload['players'][0]['name'],'bad p0 name' );
		$this->assertEquals( 'mom',$payload['players'][1]['name'],'bad p1 name' );
	}

	/** @dataProvider dataFailQuery */
	public function testFailQuery( array $post,string $diag )
	{
		$jar = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'chili','password'=>'chilipass'],$jar );

		$resp = GuzzPost( 'GameController.php',$post,$jar );
		$this->assertTrue( $resp['status']['isFail'],'was supposed to fail' );
		$this->assertContains( $diag,$resp['status']['message'],
			'failure diagnostic not appropriate',true
		);		
	}
	public function dataFailQuery() : array
	{
		return [
			[['cmd' => 'butts','gameId' => 1],'command'],
			[['cmd' => 'query','gameId' => 69],'game'],
			[[                 'gameId' => 69],'cmd not set'],
			[['cmd' => 'query',              ],'gameId not set']
		];
	}

	/** @dataProvider dataMove */
	public function testMove( array $sequence )
	{

		foreach( $sequence as $i => $move )
		{
			$jar = GuzzMakeJar();
			GuzzPost( 'LoginController',['cmd'=>'login','userName'=>$move['username'],'password'=>$move['password']],$jar );

			$req = $move['req'];
			$exp = $move['exp'];
	
			$resp = GuzzPost( 'GameController.php',$req,$jar );
			if( $resp['status']['isFail'] )
			{
				$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
			}
			
			$payload = $resp['payload'];
			$this->assertEquals( $exp['turn'],$payload['state']['turn'],'bad turn #'.' @seq ('.$i );
			$this->assertEquals( $exp['activeSide'],$payload['state']['activeSide'],'wrong active side #'.' @seq ('.$i );
			$this->assertEquals( $exp['winState'],$payload['state']['winState'],'wrong win state #'.' @seq ('.$i );
			$this->assertEquals( $exp['board'],$payload['state']['board'],'board does not match'.' @seq ('.$i );
			$this->assertEquals( $exp['history'],$payload['history'],'history does not match'.' @seq ('.$i );
		}
	}
	public function dataMove() : array
	{
		return [
			'simple one move' =>
			[
				[
					['req' => ['cmd' => 'move','gameId' => 1,'pot' => 0],
					'exp' => ['turn' => 1,'activeSide' => 1,'winState' => 1,'board' =>
						[0,5,5,5,5,4,0,4,4,4,4,4,4,0],
						'history' => [['turn' => 0,'pot' => 0]]],
						'username'=>'chili','password'=>'chilipass']
				]
			],
			'mancala open' =>
			[
				[
					['req' => ['cmd' => 'move','gameId' => 1,'pot' => 2],
					'exp' => ['turn' => 1,'activeSide' => 0,'winState' => 1,'board' =>
						[4,4,0,5,5,5,1,4,4,4,4,4,4,0],
						'history' => [['turn' => 0,'pot' => 2]]],
						'username'=>'chili','password'=>'chilipass'],
					['req' => ['cmd' => 'move','gameId' => 1,'pot' => 5],
					'exp' => ['turn' => 2,'activeSide' => 1,'winState' => 1,'board' =>
						[4,4,0,5,5,0,2,5,5,5,5,4,4,0],
						'history' => [['turn' => 1,'pot' => 5]]],
						'username'=>'chili','password'=>'chilipass']
				]
			],
			'bottom wins' =>
			[
				[
					['req' => ['cmd' => 'move','gameId' => 2,'pot' => 1],
					'exp' => ['turn' => 26,'activeSide' => 1,'winState' => 1,'board' =>
						[0,0,0,1,0,0,22,0,1,0,0,0,0,24],
						'history' => [['turn' => 25,'pot' => 1]]],
						'username'=>'chili','password'=>'chilipass'],
					['req' => ['cmd' => 'move','gameId' => 2,'pot' => 8],
					'exp' => ['turn' => 27,'activeSide' => 0,'winState' => 3,'board' =>
						[0,0,0,0,0,0,22,0,0,0,0,0,0,26],
						'history' => [['turn' => 26,'pot' => 8]]],
						'username'=>'mom','password'=>'mompass'],
				]
			],
			'tie' =>
			[
				[
					['req' => ['cmd' => 'move','gameId' => 2,'pot' => 3],
					'exp' => ['turn' => 26,'activeSide' => 1,'winState' => 4,'board' =>
						[0,0,0,0,0,0,24,0,0,0,0,0,0,24],
						'history' => [['turn' => 25,'pot' => 3]]],
						'username'=>'chili','password'=>'chilipass']
				]
			]
		];
	}

	/** @dataProvider dataFailMove */
	public function testFailMove( array $req,string $diag,string $username,string $password )
	{
		$jar = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>$username,'password'=>$password],$jar );

		$resp = GuzzPost( 'GameController.php',$req,$jar );
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
			'not player pot'	=> [['cmd' => 'move','gameId' => 1,'pot' => 8],'pot','chili','chilipass'],
			'not player turn' 	=> [['cmd' => 'move','gameId' => 1,'pot' => 8],'turn','mom','mompass'],
			'no pot'	 		=> [['cmd' => 'move','gameId' => 1           ],'pot','chili','chilipass'],
			'bad gameId'	 	=> [['cmd' => 'move','gameId' =>69,'pot' => 0],'LoadGame id','chili','chilipass']
		];
	}

	public function testUpdate()
	{
		$jar1 = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'chili','password'=>'chilipass'],$jar1 );

		// setup
		GuzzPost( 'GameController.php',['cmd' => 'move','gameId' => 1,'pot' => 2],$jar1 );
		GuzzPost( 'GameController.php',['cmd' => 'move','gameId' => 1,'pot' => 5],$jar1 );


		$jar2 = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'mom','password'=>'mompass'],$jar2 );

		// execute command under test, check for error
		$resp = GuzzPost( 'GameController.php',['cmd' => 'update','gameId' => 1,'turn' => 0],$jar2  );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
		}
		
		// verify results
		$payload = $resp['payload'];
		$this->assertFalse( $payload['upToDate'],'bad upToDate' );
		$this->assertEquals( ['turn' => 0,'pot' => 2],$payload['history'][0],'bad first history move' );
		$this->assertEquals( ['turn' => 1,'pot' => 5],$payload['history'][1],'bad second history move' );
		$this->assertEquals( [4,4,0,5,5,0,2,5,5,5,5,4,4,0],$payload['state']['board'],'bad board state' );
		$this->assertEquals( 1,$payload['state']['activeSide'],'bad active side' );	
		$this->assertEquals( 2,$payload['state']['turn'],'bad turn' );
		$this->assertEquals( 1,$payload['state']['winState'],'bad win state' );	
	}

	public function testUpdateUpToDate()
	{
		$jar1 = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'chili','password'=>'chilipass'],$jar1 );

		// setup
		GuzzPost( 'GameController.php',['cmd' => 'move','gameId' => 1,'pot' => 2],$jar1 );
		GuzzPost( 'GameController.php',['cmd' => 'move','gameId' => 1,'pot' => 5],$jar1 );


		$jar2 = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'mom','password'=>'mompass'],$jar2 );

		// execute command under test, check for error
		$resp = GuzzPost( 'GameController.php',['cmd' => 'update','gameId' => 1,'turn' => 2,'winState'=>WinState::InProgress],$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
		}
		
		// verify results
		$this->assertTrue( $resp['payload']['upToDate'],'bad upToDate' );
	}

	/** @dataProvider dataFailUpdate */
	public function testFailUpdate( array $req,string $diag )
	{
		$jar = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'chili','password'=>'chilipass'],$jar );

		$resp = GuzzPost( 'GameController.php',$req,$jar );
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
	public function dataFailUpdate() : array
	{
		return [
			'bad gameId' 		=> [['cmd' => 'update','gameId' => 7,'turn' => 0],'game'],
			'bad turn'	 		=> [['cmd' => 'update','gameId' => 1,'turn' => 9],'turn'],
			'no turn'	 		=> [['cmd' => 'update','gameId' => 1            ],'turn']
		];
	}

	public function testGetActive()
	{
		$jar = GuzzMakeJar();
		GuzzPost( 'LoginController',['cmd'=>'login','userName'=>'chili','password'=>'chilipass'],$jar );
		
		$resp = GuzzPost( 'GameController.php',['cmd' => 'getactive'],$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'response status [fail] with: '.$resp['status']['message'] );
		}
		
		$payload = $resp['payload'];
		$this->assertEquals( 2,count( $payload ) );
		$this->assertEquals( 1,$payload[0] );
	}
}
?>