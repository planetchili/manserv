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
        $gameDataArray = $this->conn->qfetch( 'SELECT * from games where id = '.$gameId );
        assert( count( $gameDataArray ) > 0,"LoadGame id not found" );

        $gameData = $gameDataArray[0];
        return new GameInfo( 
            (int)$gameData['id'],
            (int)$gameData['turn'],
            (int)$gameData['player0Id'],
            (int)$gameData['player1Id'],
            new Side( (int)$gameData['activeSide'] ),
            (int)$gameData['winState']
        );
    }

    public function UpdateGame( GameInfo $gameInfo ) : void
    {
        $this->conn->exec( 
            "UPDATE games
             set    activeSide = {$gameInfo->GetActiveSide()->GetIndex()},
                    turn = turn + 1,
                    winState = {$gameInfo->GetWinState()}
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
            $pots[] = (int)$qresult[$i][0];
        }
        return new Board( $pots );
    }

    public function UpdateBoard( Board $board,int $gameId ) : void
    {
        // build sql to update all pots on board
        $sql = 'INSERT into boards (gameId,potId,beads) values ';
        for( $i = 0; $i < 14; $i++ )
        {
            $sql .= '('.$gameId.','.$i.','.$board->GetPot( new Pot( $i ) ).'),';
        }
        // (trim trailing comma)
        $sql = substr( $sql,0,-1 );
        $sql .= ' on duplicate key update beads = values(beads);';
        // execute sql command
        $nRowsAffected = $this->conn->exec( $sql );
    }

    public function SetupSchema() : void
    {
        $this->conn->exec(
            'CREATE table if not exists games(
                id int primary key auto_increment,
                turn int not null,
                player0Id int not null,
                player1Id int not null,
                activeSide int not null,
                winState int not null default 1
            );'
        );
        $this->conn->exec(
            'CREATE table if not exists boards(
                gameId int not null,
                potId int not null,
                beads int not null,
                primary key( gameId,potId )
            );'
        );
    }

    public function CreateNewGame( int $player0Id,int $player1Id,Side $startSide ) : int
    {
        $result = $this->conn->exec( 
            "INSERT into games set 
                player0Id = {$player0Id},
                player1Id = {$player1Id},
                turn = 0,
                activeSide = {$startSide->GetIndex()};"
        );
        $gameId = $this->conn->lastInsertId();
        $this->UpdateBoard( Board::MakeFresh(),$gameId );
        return $gameId;
    }

    public function __construct( ChiliSql $conn )
    {
        $this->conn = $conn;
    }
}
?>