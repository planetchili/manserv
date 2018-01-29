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
                id int primary key,
                turn int not null,
                player0Id int not null,
                player1Id int not null,
                activeSide int not null
            );'
        );
    }

    public static function tearDownAfterClass()
    {
        // cleanup schema
        // games table
        self::$dbc->query( 'DROP TABLE games' );
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
}
?>