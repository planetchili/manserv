<?php
require_once 'ChiliSql.php';
require_once 'Side.php';
require_once 'GameInfo.php';

class MancalaDatabase
{
    /** @var ChiliSql */
    private $conn;

    public function LoadGame( int $gameId ) : GameInfo 
    {
        $gameDataArray = $this->conn->qfetch( 'SELECT * FROM games WHERE id = '.$gameId );
        assert( count( $gameDataArray ) > 0,"LoadGame id not found" );

        $gameData = $gameDataArray[0];
        return new GameInfo( 
            (int)$gameData['id'],
            (int)$gameData['turn'],
            (int)$gameData['player0Id'],
            (int)$gameData['player1Id'],
            new Side( (int)$gameData['activeSide'] )
        );
    }

    public function UpdateGame( GameInfo $gameInfo ) : void
    {

    }

    public function __construct( ChiliSql $conn )
    {
        $this->conn = $conn;
    }
}
?>