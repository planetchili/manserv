<?php
require_once 'MancalaDatabase.php';
require_once 'ChiliTest.php';

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
        return new PHPUnit\DbUnit\DataSet\YamlDataSet( dirname(__FILE__)."/DBTestData/MancalaTest.yml" );
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
        $gameInfoActual = $this->mdb->LoadGame( $gameId );
        $this->assertEquals( $gameInfoExpected,$gameInfoActual );
    }
    public function dataLoadGame() : array
    {
        return [
            [1,new GameInfo( 1,0,69,420,new Side( 0 ),WinState::InProgress )],
            [42,new GameInfo( 42,13,11,17,new Side( 1 ),WinState::TopWins )],
            [1666666,new GameInfo( 1666666,24,1,2,new Side( 1 ),WinState::InProgress )]
        ];
    }

    public function testFailLoadGame()
    {
        $this->expectException( AssertionError::class );

        $gameId = 1337;
        $this->mdb->LoadGame( $gameId );
    }

    public function testUpdateGame()
    {
        $gameInfo = new GameInfo( 1,1,6969,6969,Side::Bottom() );
        $this->mdb->UpdateGame( $gameInfo );
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/DBTestData/MancalaExpectUpdateGame.yml"
        );
        $dataSet = $this->getConnection()->createDataSet();
        $this->assertTablesEqual( $expectedDataSet->getTable( 'games' ),$dataSet->getTable( 'games' ) );
    }

    public function testLoadBoard()
    {
        $gameId = 1;
        $board = $this->mdb->LoadBoard( $gameId );
        $expected = new Board( [4,4,4,4,4,4,0,4,4,4,4,4,4,0] );
        $this->assertEquals( $expected,$board );
    }

    public function testFailLoadBoard()
    {
        $this->expectException( AssertionError::class );
        $gameId = 4444;
        $board = $this->mdb->LoadBoard( $gameId );
    }

    public function testUpdateBoard()
    {
        $gameId = 1;
        $board = new Board( [0,5,5,5,5,4,0,4,4,4,4,4,4,0] );
        $this->mdb->UpdateBoard( $board,$gameId );

        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/DBTestData/MancalaExpectUpdateGame.yml"
        );
        $dataSet = $this->getConnection()->createDataSet();
        $this->assertTablesEqual( $expectedDataSet->getTable( 'boards' ),$dataSet->getTable( 'boards' ) );
    }

    public function testGameWithDb()
    {
        $gameId = 1;
        $game = new Game( $this->mdb,$gameId );
        $game->DoMove( new Pot( 0 ) );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/DBTestData/MancalaExpectUpdateGame.yml"
        );
        $dataSet = $this->getConnection()->createDataSet();
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
    }

    public function testCreateNewGame()
    {
        $gameId = $this->mdb->CreateNewGame( 1,2,Side::Top() );
        
        $expectedDataSet = new PHPUnit\DbUnit\DataSet\YamlDataSet(
            dirname(__FILE__)."/DBTestData/MancalaExpectNewGame.yml"
        );
        $dataSet = $this->getConnection()->createDataSet();
        $this->assertDataSetsEqual( $expectedDataSet,$dataSet );
        $this->assertEquals( $gameId,1666667 );
    }
}
?>