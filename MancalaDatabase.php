<?php
require_once 'ChiliSql.php';
require_once 'Side.php';
require_once 'GameInfo.php';
require_once 'Board.php';

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
        $this->conn->exec( 
            "UPDATE games
             set    activeSide = {$gameInfo->GetActiveSide()->GetIndex()},
                    turn = turn + 1
             where  id = {$gameInfo->GetGameId()}"
        );
    }

    public function LoadBoard( int $gameId ) : Board
    {
        $qresult = $this->conn->qfetch(
            "SELECT beads from boards where gameId = {$gameId}
             order by potId"
        );
        assert( count( $qresult ) === 14,"Wrong number of pots/game not found in LoadBoard" );

        for( $i = 0; $i < 14; $i++ )
        {
            $pots[] = $qresult[$i][0];
        }
        return new Board( $pots );
    }

    public function __construct( ChiliSql $conn )
    {
        $this->conn = $conn;
    }
}
?>