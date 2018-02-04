<?php
require_once 'ChiliSql.php';
require_once 'Side.php';
require_once 'GameInfo.php';
require_once 'Board.php';
require_once 'User.php';

class MancalaDatabase
{
    /** @var ChiliSql */
    private $conn;

    public function LoadGame( int $gameId ) : GameInfo 
    {
        $gameDataArray = $this->conn->qfetcha( 'SELECT * from games where id = '.$gameId );
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
        $qresult = $this->conn->qfetchi(
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
        $this->conn->exec( $sql );
    }

    public function SetupSchema() : void
    {
        $this->conn->exec(
            'CREATE table if not exists games(
                id int primary key auto_increment,
                turn int not null default 0,
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
        $this->conn->exec(
            'CREATE table if not exists histories(
                gameId int not null,
                turn int not null,
                pot int not null,
                primary key( gameId,turn )
            );'
        );
        $this->conn->exec(
            'CREATE table if not exists users (
                id int primary key auto_increment,
                `name` varchar (32) not null unique key,
                email varchar (255) not null unique key,
                passwordHash varchar (255) not null
            );'
        );
    }

    public function ClearSchema() : void
    {
        $this->conn->exec( 'DROP table if exists games,boards,histories,users;' );
    }

    public function CreateNewGame( int $player0Id,int $player1Id,Side $startSide ) : int
    {
        $this->conn->exec( 
            "INSERT into games set 
                player0Id = {$player0Id},
                player1Id = {$player1Id},
                activeSide = {$startSide->GetIndex()};"
        );
        $gameId = $this->conn->lastInsertId();
        $this->UpdateBoard( Board::MakeFresh(),$gameId );
        return $gameId;
    }

    public function ClearBoard( int $gameId ) : void
    {
        $nRowsAffected = $this->conn->exec(
            "DELETE from boards where gameId = {$gameId};"
        );
        assert( $nRowsAffected === 14,'Wrong number of pots for ClearBoard()' );
    }

    public function AddHistoryMove( int $gameId,int $turn,Pot $move ) : void
    {
        $this->conn->exec(
            "INSERT into histories set
                gameId = {$gameId},
                turn = {$turn},
                pot = {$move->GetIndex()};"
        );
    }

    public function AddUser( User $user ) : void
    {
        $stmt = $this->conn->prepare(
            'INSERT into users set
                `name` = :n,
                email = :e,
                passwordHash = :p;'
        );
        $stmt->execute( [
            ':n'=>$user->GetName(),
            ':e'=>$user->GetEmail(),
            ':p'=>$user->GetPasswordHash()
        ] );
    }

    public function LoadUserById( int $userId ) : User
    {
        $qres = $this->conn->qfetcha( 'SELECT * from users where id = '.$userId );
        check( count( $qres ) === 1,'loaduserbyid no users found' );

        $data = $qres[0];
        return new User( $userId,$data['name'],$data['email'],$data['passwordHash'],true );
    }

    public function LoadUserByName( string $name ) : User
    {        
        $name = strtolower( $name );
        $stmt = $this->conn->prepare( 'SELECT * from users where `name` = :n;' );
        $stmt->execute( [':n'=>$name] );
        $qres = $stmt->fetchAll( PDO::FETCH_ASSOC );
        check( count( $qres ) === 1,'loaduserbyname no users found' );

        $data = $qres[0];
        return new User( $data['id'],$name,$data['email'],$data['passwordHash'],true );
    }

    public function GetActiveGamesByUserId( int $userId ) : array
    {
        return array_column( $this->conn->qfetchi( "SELECT id from games where (player0Id = {$userId} or player1Id = {$userId}) and winState = 1;" ),0 );
    }

    public function LoadNewMoves( int $gameId,int $fromTurn ) : array
    {
        return $this->conn->qfetcha( "SELECT turn,pot from histories where gameId = {$gameId} and turn >= {$fromTurn};" );
    }

    public function __construct( ChiliSql $conn )
    {
        $this->conn = $conn;
    }
}
?>