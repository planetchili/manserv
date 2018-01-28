<?php
require_once 'ChiliSql.php';
require_once 'Side.php';

class GameInfo
{
    /** @var int */
    protected $id;
    /** @var int */
    protected $turn;
    /** @var int[] */
    protected $playerIds;
    /** @var Side */
    protected $activeSide;

    public function GetActiveSide() : Side
    {
        return $this->activeSide;
    }

    public function GetTurn() : int
    {
        return $this->turn;
    }

    public function GetGameId() : int
    {
        return $this->gameId;
    }

    public function GetPlayerId( Side $side ) : int
    {
        return $this->playerIds[$side->GetIndex()];
    }

    public function GetPlayerIds() : array
    {
        return $this->playerIds;
    }

    public function __construct( int $id,int $turn,int $player0Id,int $player1Id,Side $activeSide )
    {
        $this->id = $id;
        $this->turn = $turn;
        $this->playerIds = [$player0Id,$player1Id];
        $this->activeSide = $activeSide;
    }
}

class MancalaDatabase
{
    // /** @var ChiliSql */
    // private $conn;

    // public function LoadGame( int $gameId ) : GameInfo 
    // {
        
    // }
}
?>