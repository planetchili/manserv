<?php
require_once 'Board.php';
require_once 'MancalaDatabase.php';

class Game
{
    /** @var MancalaDatabase */
    private $db;
    /** @var Board */
    private $board;
    /** @var int */
    private $turn;
    /** @var int */
    private $gameId;
    /** @var int[] */
    private $playerIds;
    /** @var Side */
    private $activeSide;

    public function __construct( MancalaDatabase $db,int $gameId )
    {
        // set members from ctor params
        $this->gameId = $gameId;
        $this->db = $db;
        // load game info
        $gameInfo = $db->LoadGame( $gameId );
        $this->turn = $gameInfo['turn'];
        $this->playerIds = $gameInfo['playerIds'];
        $this->activeSide = $gameInfo['activeSide'];
        assert( $this->playerIds[0] != $this->playerIds[1] );
        // load board state
        $this->board = $db->LoadBoard( $gameId );
    }

    /** returns true if game ended */
    public function DoMove( Pot $move ) : bool
    {
        // advance turn counter
        $this->turn++;
        // execute move and switch sides if not mancala
        if( !$this->board->DoMove( $move,$this->activeSide ) )
        {
            $this->activeSide = $this->activeSide->GetOpposite();
        }
        // process sweeping, rval is true if game over
        $isOver = $this->board->ProcessSweep();
        // update board and game
        $this->db->StoreBoard( $this->gameId,$this->board );
        $this->db->UpdateGame( $this->gameId,$this->turn,$this->activeSide );
        // return true if game is over
        return $isOver;
    }

    public function GetWinState() : WinState
    {
        return $this->board->GetWinState();
    }

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
}
?>