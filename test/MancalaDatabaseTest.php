<?php
require_once 'MancalaDatabase.php';
require_once 'ChiliTest.php';

class MancalaDatabaseTest extends ChiliDatabaseTest
{
    /** @var ChiliSql */
    private static $dbc;
    /** @var MancalaDatabase */
    private $mdb;

    public static function setUpBeforeClass()
    {
        // create SUT conn (also doubles as schema setup conn)
        self::$dbc = new ChiliSql( self::SCHEMA,self::USER,self::PASSWORD );
        // setup schema
        // games table
        self::$dbc->exec(
            'CREATE table games(
                id int primary key auto_increment,
                turn int not null,
                player0Id int not null,
                player1Id int not null,
                activeSide int not null
            );'
        );
        self::$dbc->exec(
            'CREATE table boards(
                gameId int not null,
                potId int not null,
                beads int not null,
                primary key( gameId,potId )
            );'
        );
    }

    public static function tearDownAfterClass()
    {
        // cleanup schema
        // drop games,boards
        self::$dbc->query( 'DROP TABLE games,boards' );
        // cleanup SUT conn
        self::$dbc = null;
    }

    protected function getDataSet()
    {
        return new PHPUnit\DbUnit\DataSet\YamlDataSet( dirname(__FILE__)."/DBTestData/MancalaTest.yml" );
    }

    public function setUp()
    {
        parent::setUp();
        $this->mdb = new MancalaDatabase( self::$dbc );
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
            [1,new GameInfo( 1,0,69,420,new Side( 0 ) )],
            [42,new GameInfo( 42,13,11,17,new Side( 1 ) )],
            [1666666,new GameInfo( 1666666,24,1,2,new Side( 1 ) )]
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
}
?>