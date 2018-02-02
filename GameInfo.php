<?php
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

    public function __construct( int $id,int $turn,int $player0Id,int $player1Id,
        Side $activeSide,int $winState = WinState::InProgress )
    {
        $this->id = $id;
        $this->turn = $turn;
        $this->playerIds = [$player0Id,$player1Id];
        $this->activeSide = $activeSide;
        $this->winState = $winState;
    }
}
?>