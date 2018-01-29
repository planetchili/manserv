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
    
    public function testLoadGame()
    {
        $gameId = 1;
        $gameInfoActual = $this->mdb->LoadGame( $gameId );
        $gameInfoExpected = new GameInfo( 1,0,69,420,new Side( 0 ) );
        $this->assertEquals( $gameInfoExpected,$gameInfoActual );
    }
}
?>