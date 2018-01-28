<?php
require_once 'GameInfo.php';
require_once 'Board.php';
require_once 'MancalaDatabase.php';

class Game extends GameInfo
{
    /** @var MancalaDatabase */
    private $db;
    /** @var Board */
    private $board;

    public function __construct( MancalaDatabase $db,int $gameId )
    {
        // set members from ctor params
        $this->gameId = $gameId;
        $this->db = $db;
        // load game info
        $gameInfo = $db->LoadGame( $gameId );
        $this->turn = $gameInfo->GetTurn();
        $this->playerIds = $gameInfo->GetPlayerIds();
        $this->activeSide = $gameInfo->GetActiveSide();
        assert( $this->playerIds[0] != $this->playerIds[1],"same player may not occupy both slots!" );
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
}
?>