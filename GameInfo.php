<?php
require_once __DIR__.'/Side.php';

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
    /** @var int */
    protected $winState;

    public function GetActiveSide() : Side
    {
        return $this->activeSide;
    }

    public function GetTurn() : int
    {
        return $this->turn;
    }

    //TODO: rename getid
    public function GetGameId() : int
    {
        return $this->id;
    }

    public function GetPlayerId( Side $side ) : int
    {
        return $this->playerIds[$side->GetIndex()];
    }

    public function GetPlayerIds() : array
    {
        return $this->playerIds;
    }

    public function GetSideFromId( int $userId ) : ?Side
    {
        if( $userId === $this->playerIds[0] )
        {
            return Side::Top();
        }
        else if( $userId === $this->playerIds[1] )
        {
            return Side::Bottom();
        }
        else
        {
            return null;
        }
    }

    public function GetWinState() : int
    {
        return $this->winState;
    }

    public function HasUserId( int $userId ) : bool
    {
        array_search( $userId,$this->playerIds ) !== false;
    }

    public function __construct( int $id,int $turn,array $userIds,
        Side $activeSide,int $winState = WinState::InProgress )
    {
        $this->id = $id;
        $this->turn = $turn;
        $this->playerIds = $userIds;
        $this->activeSide = $activeSide;
        $this->winState = $winState;
        assert( $this->playerIds[0] != $this->playerIds[1],'same player may not occupy both slots!' );
    }
}
?>