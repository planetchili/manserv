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
        $gameData = $this->conn->query( 'SELECT * FROM games WHERE id = '.$gameId );
        assert( count( $gameData ) > 0,"LoadGame id not found" );
        return new GameInfo( 
            (int)$gameData['id'],
            (int)$gameData['turn'],
            (int)$gameData['player0Id'],
            (int)$gameData['player1Id'],
            new Side( (int)$gameData['activeSide'] )
        );
    }
}
?>