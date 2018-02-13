<?php
require_once 'ChiliGuzz.php';
require_once 'test/ChiliTest.php';

/** @group gCtrl */
class RoomControllerTest extends ChiliDatabaseTest
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
        return new PHPUnit\DbUnit\DataSet\YamlDataSet( dirname(__FILE__)."\_Fixture.yml" );
    }

    public function setUp()
    {
        parent::setUp();
        $this->mdb = new MancalaDatabase( self::$pdo );
	}

	public function testCreateRoomNopass()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => ''];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				'id'=>1,
				'name'=>'Dog Farts',
				'gameId'=>null,
				'players'=>[]
			],			
			$room
		);		
	}

	public function testCreateRoomPass()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				'id'=>1,
				'name'=>'Dog Farts',
				'gameId'=>null,
				'players'=>[]
			],
			$room
		);		
	}

	/** @depends testCreateRoomNopass */
	public function testJoinRoomNopass()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => ''];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => ''];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				'id'=>1,
				'name'=>'Dog Farts',
				'gameId'=>null,
				'players'=>[['userId'=>1,'isOwner'=>true,'isReady'=>false]]
			],
			$room
		);		
	}

	/** @depends testCreateRoomPass */
	public function testJoin2RoomPass()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'login','userName' => 'mom','password' => 'mompass'];
		$jar2 = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				'id'=>1,
				'name'=>'Dog Farts',
				'gameId'=>null,
				'players'=>[
					['userId'=>1,'isOwner'=>true,'isReady'=>false],
					['userId'=>2,'isOwner'=>false,'isReady'=>false]
				]
			],
			$room
		);		
	}

	/** @doesNotPerformAssertions */
	public function testFailJoinRoomPass()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'pissBIRD'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( !$resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}
	}

	/** @depends testJoin2RoomPass */
	public function testUpdate()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'login','userName' => 'mom','password' => 'mompass'];
		$jar2 = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'update','roomId' => $room['id']];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				'id'=>1,
				'name'=>'Dog Farts',
				'gameId'=>null,
				'players'=>[
					['userId'=>1,'isOwner'=>true,'isReady'=>false],
					['userId'=>2,'isOwner'=>false,'isReady'=>false]
				]
			],
			$room
		);		
	}

	/** @depends testUpdate */
	public function testLeave()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'login','userName' => 'mom','password' => 'mompass'];
		$jar2 = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'leave','roomId' => $room['id']];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'update','roomId' => $room['id']];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				'id'=>1,
				'name'=>'Dog Farts',
				'gameId'=>null,
				'players'=>[
					['userId'=>2,'isOwner'=>true,'isReady'=>false]
				]
			],
			$room
		);		
	}

	
	/** @depends testJoin2RoomPass */
	public function testList()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'login','userName' => 'mom','password' => 'mompass'];
		$jar2 = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'list'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				[
					'id'=>1,
					'name'=>'Dog Farts',
					'engaged'=>false,
					'players'=>['chili','mom']
				]
			],
			$room
		);		
	}

	/** @depends testJoin2RoomPass */
	public function testReady()
	{
		$req = ['cmd' => 'login','userName' => 'chili','password' => 'chilipass'];
		$jar = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'create','name' => 'Dog Farts','password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}
		
		$req = ['cmd' => 'login','userName' => 'mom','password' => 'mompass'];
		$jar2 = GuzzMakeJar();
		$resp = GuzzPost( 'LoginController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'login: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'join','roomId' => $room['id'],'password' => 'password'];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$req = ['cmd' => 'ready','roomId' => $room['id']];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}	

		$req = ['cmd' => 'update','roomId' => $room['id']];
		$resp = GuzzPost( 'RoomController.php',$req,$jar2 );
		if( $resp['status']['isFail'] )
		{
			$this->fail( 'create: response status [fail] with: '.$resp['status']['message'] );
		}

		$room = $resp['payload'];
		$this->assertEquals( 
			[
				'id'=>1,
				'name'=>'Dog Farts',
				'gameId'=>null,
				'players'=>[
					['userId'=>1,'isOwner'=>true,'isReady'=>false],
					['userId'=>2,'isOwner'=>false,'isReady'=>true]
				]
			],
			$room
		);			
	}
}
?>