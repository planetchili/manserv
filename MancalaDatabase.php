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
        $gameData = $this->conn->qfetch( 'SELECT * FROM games WHERE id = '.$gameId );
        assert( count( $gameData ) > 0,"LoadGame id not found" );
        return new GameInfo( 
            (int)$gameData[0]['id'],
            (int)$gameData[0]['turn'],
            (int)$gameData[0]['player0Id'],
            (int)$gameData[0]['player1Id'],
            new Side( (int)$gameData[0]['activeSide'] )
        );
    }

    public function __construct( ChiliSql $conn )
    {
        $this->conn = $conn;
    }
}
?>