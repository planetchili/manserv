<?php
require_once 'MancalaDatabase.php';
require_once 'test/ChiliTest.php';

/** @group gDb */
class MancalaDatabaseTest extends ChiliDatabaseTest
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
    
    /**
     * @dataProvider dataLoadGame
     */
    public function testLoadGame( int $gameId,GameInfo $gameInfoExpected )
    {
        $gameInfoActual = $this->mdb->LoadGameInfo( $gameId );
        $this->assertEquals( $gameInfoExpected,$gameInfoActual );
    }
    public function dataLoadGame() : array
    {
        return [
            [1,new GameInfo( 1,0,[69,420],new Side( 0 ),WinState::InProgress )],
            [42,new GameInfo( 42,13,[11,17],new Side( 1 ),WinState::TopWins )],
            [1666666,new GameInfo( 1666666,24,[1,2],new Side( 1 ),WinState::InProgress )]
        ];
    }
    
    /** @expectedException AssertionError */
    public function testFailLoadGame()
    {
        $gameId = 1337;
        $this->mdb->LoadGameInfo( $gameId );
    }

    public function testUpdateGame()
    {
        $gameInfo = new GameInfo( 1,1,[69,420],Side::Bottom() );
        $this->mdb->UpdateGame( $gameInfo );
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/UpdateGame.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['games'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testLoadBoard()
    {
        $gameId = 1;
        $board = $this->mdb->LoadBoard( $gameId );
        $expected = new Board( [4,4,4,4,4,4,0,4,4,4,4,4,4,0] );
        $this->assertEquals( $expected,$board );
    }

    /** @expectedException AssertionError */
    public function testFailLoadBoard()
    {
        $gameId = 4444;
        $board = $this->mdb->LoadBoard( $gameId );
    }

    public function testUpdateBoard()
    {
        $gameId = 1;
        $board = new Board( [0,5,5,5,5,4,0,4,4,4,4,4,4,0] );
        $this->mdb->UpdateBoard( $board,$gameId );

        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/UpdateBoard.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['boards'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testGameWithDb()
    {
        $gameId = 1;
        $factory = new MancalaFactory( $this->mdb );
        $game = $factory->LoadGame( $gameId );
        $game->DoMove( new Pot( 0 ) );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/AddHistoryMoveFull.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['users','games','boards','histories'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testCreateNewGame()
    {
        $gameId = $this->mdb->CreateNewGame( 1,2,Side::Top() );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/NewGame.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['games'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
        $this->assertEquals( $gameId,1666667 );
    }

    public function testClearBoard()
    {
        $this->mdb->ClearBoard( 1 );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/ClearBoard.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['boards'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    /** @expectedException AssertionError */
    public function testFailClearBoard()
    {
        $this->mdb->ClearBoard( 6969 );
    }

    public function testAddHistoryMove()
    {
        $this->mdb->AddHistoryMove( 1,0,new Pot( 3 ) );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/AddHistoryMove.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['histories'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    /** @expectedException PDOException */
    public function testFailAddHistoryMove()
    {
        $this->mdb->AddHistoryMove( 42,0,new Pot( 3 ) );
    }

    public function testCreateNewUser()
    {
        $userId = $this->mdb->CreateNewUser( 'sPoot','spoot@hotmail.cOm','$hash$test.' );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/CreateNewUser.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['users'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
        $this->assertEquals( 4,$userId );
    }

    /** @expectedException PDOException */
    public function testFailCreateNewUser()
    {
        $this->mdb->CreateNewUser( 'chili','chili@planetchili.net','$hash$test.' );
    }

    public function testLoadUserById()
    {
        $exp = new User( 3,'chili','chili@planetchili.net','nothashedohshitwhatup',true );
        $act = $this->mdb->LoadUserById( $exp->GetId() );
        $this->assertEquals( $exp,$act );
    }

    /** @expectedException ChiliException */
    public function testFailLoadUserById()
    {
        $act = $this->mdb->LoadUserById( 69 );
    }

    public function testLoadUserByName()
    {
        $exp = new User( 3,'chili','chili@planetchili.net','nothashedohshitwhatup',true );
        $act = $this->mdb->LoadUserByName( 'ChiLi' );
        $this->assertEquals( $exp,$act );
    }

    /** @expectedException ChiliException */
    public function testFailLoadUserByName()
    {
        $act = $this->mdb->LoadUserByName( 'ChiL6i' );
    }

    public function testLoadNewMoves()
    {
        $moves = $this->mdb->LoadNewMoves( 42,0 );
        $this->assertEquals( 1,count( $moves ) );
        $this->assertEquals( 0,$moves[0]['turn'] );
        $this->assertEquals( 4,$moves[0]['pot'] );
    }

    public function testGetActiveGamesByUserId()
    {
        $this->assertEquals( 1,count( $this->mdb->GetActiveGamesByUserId( 69 ) ) );
        $this->assertEquals( 1,$this->mdb->GetActiveGamesByUserId( 69 )[0] );
    }

    public function testAddMembership()
    {
        $player = new RoomPlayer( 3,false );
        $roomId = 4;
        $this->mdb->AddMembership( $player,$roomId );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/AddMembership.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['memberships'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testCreateNewRoom()
    {
        $roomId1 = $this->mdb->CreateNewRoom( 'ducks and bitts','$hash$test.' );
        $roomId2 = $this->mdb->CreateNewRoom( 'sticks and stones',null );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/AddRoom.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['rooms'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
        $this->assertEquals( 1,$roomId1 );
        $this->assertEquals( 2,$roomId2 );
    }

    public function testRemoveMembership()
    {
        $player = new RoomPlayer( 3,false );
        $roomId = 4;
        $this->mdb->AddMembership( $player,$roomId );
        $this->mdb->RemoveMembership( 2,4 );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/RemoveMembership.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['memberships'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testDestroyRoom()
    {
        $roomId1 = $this->mdb->CreateNewRoom( 'ducks and bitts','$hash$test.' );
        $roomId2 = $this->mdb->CreateNewRoom( 'sticks and stones',null );
        $this->mdb->DestroyRoom( $roomId1 );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/DestroyRoom.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['rooms'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testUpdateRoom()
    {
        $roomId1 = $this->mdb->CreateNewRoom( 'ducks and bitts','$hash$test.' );
        $roomId2 = $this->mdb->CreateNewRoom( 'sticks and stones',null );
        $room2 = new Room( $roomId2,'sticks and stones',69,null,$this->mdb,[] );
        $this->mdb->UpdateRoom( $room2 );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/UpdateRoom.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['rooms'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testUpdateMembership()
    {
        $player = new RoomPlayer( 3,false );
        $roomId = 4;
        $this->mdb->AddMembership( $player,$roomId );
        $player->MakeReady();
        $this->mdb->UpdateMembership( $player,$roomId );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/UpdateMembership.yml"
        );
        $dataSet = $this->getConnection()->createDataSet( ['memberships'] );
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    /** @depends testAddMembership */
    public function testLoadPlayers()
    {
        $roomId = 4;
        $this->mdb->AddMembership( new RoomPlayer( 5,false ),$roomId );
        $players = $this->mdb->LoadPlayers( $roomId );
        
        $this->assertEquals( [new RoomPlayer( 2,true ),new RoomPlayer( 5,false )],$players );
    }

    /** @depends testLoadPlayers */
    /** @depends testCreateNewRoom */
    public function testLoadRoom()
    {
        $roomId = $this->mdb->CreateNewRoom( 'test game',null );
        $this->mdb->AddMembership( new RoomPlayer( 2,false,true ),$roomId );
        $this->mdb->AddMembership( new RoomPlayer( 5,false ),$roomId );
        $room = $this->mdb->LoadRoom( $roomId );
        
        $this->assertEquals( 
            new Room( $roomId,'test game',null,null,$this->mdb,[new RoomPlayer( 2,false,true ),new RoomPlayer( 5,false )] ),
            $room );
    }

    /** @depends testCreateNewRoom */
    public function testListRoom()
    {
        $oneId = $this->mdb->CreateNewUser( 'one','onemail','onepass' );
        $twoId = $this->mdb->CreateNewUser( 'two','twomail','twopass' );

        $roomaId = $this->mdb->CreateNewRoom( 'room a',null );
        $roombId = $this->mdb->CreateNewRoom( 'room b','bpass' );

        $this->mdb->AddMembership( new RoomPlayer( 2,true,true ),$roomaId );
        $this->mdb->AddMembership( new RoomPlayer( 3,false ),$roomaId );
        $this->mdb->AddMembership( new RoomPlayer( $oneId,true,true ),$roombId );
        $this->mdb->AddMembership( new RoomPlayer( $twoId,false,true ),$roombId );

        $roomb = $this->mdb->LoadRoom( $roombId );
        $roomb->EngageGame();

        $list = $this->mdb->ListRooms();
        
        $this->assertEquals( 
            [
                [
                    'id'=>1,
                    'name'=>'room a',
                    'engaged'=>false,
                    'players'=>['mom','chili'],
                    'locked'=>false
                ],
                [
                    'id'=>2,
                    'name'=>'room b',
                    'engaged'=>true,
                    'players'=>['one','two'],
                    'locked'=>true
                ]
            ],
            $list
        );
    }

    /** @depends testCreateNewRoom */
    public function testLoadRoomFromUserId()
    {
        $oneId = $this->mdb->CreateNewUser( 'one','onemail','onepass' );
        $twoId = $this->mdb->CreateNewUser( 'two','twomail','twopass' );

        $roomaId = $this->mdb->CreateNewRoom( 'room a',null );
        $roombId = $this->mdb->CreateNewRoom( 'room b','bpass' );

        $this->mdb->AddMembership( new RoomPlayer( 2,true,true ),$roomaId );
        $this->mdb->AddMembership( new RoomPlayer( 3,false ),$roomaId );
        $this->mdb->AddMembership( new RoomPlayer( $oneId,true,true ),$roombId );
        $this->mdb->AddMembership( new RoomPlayer( $twoId,false,true ),$roombId );

        $roomb = $this->mdb->LoadRoom( $roombId );
        $gameId = $roomb->EngageGame();
        

        $roomt1 = $this->mdb->LoadRoomFromUserId( 3 );
        $this->assertEquals( $roomaId,$roomt1->GetId() );

        $roomt2 = $this->mdb->LoadRoomFromUserId( $oneId );
        $this->assertEquals( $roombId,$roomt2->GetId() );
        $this->assertEquals( $gameId,$roomt2->GetGameId() );
    }

    // TODO: add more involved tests for room stuff
}
?>