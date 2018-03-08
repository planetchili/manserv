<?php
require_once __DIR__.'/GameInfo.php';
require_once __DIR__.'/Board.php';
require_once __DIR__.'/IMancalaDatabase.php';

class Game extends GameInfo
{
    /** @var IMancalaDatabase */
    private $db;
    /** @var Board */
    private $board;

    // TODO: playerId -> userId
    public function __construct( int $gameId,int $turn,array $playerIds,
        Side $activeSide,int $winState,Board $board,IMancalaDatabase $db )
    {
        parent::__construct( $gameId,$turn,
            $playerIds,$activeSide,$winState
        );
        // set members unique to Game
        $this->board = $board;
        $this->db = $db;
    }

    /** returns true if game ended */
    public function DoMove( Pot $move ) : bool
    {
        assert( $this->GetWinState() === WinState::InProgress );
        // remember number for this turn
        $curTurn = $this->GetTurn();
        // advance turn counter
        $this->turn++;
        // execute move and switch sides if not mancala
        if( !$this->board->DoMove( $move,$this->activeSide ) )
        {
            $this->activeSide = $this->activeSide->GetOpposite();
        }
        // process sweeping, rval is true if game over
        $isOver = $this->board->ProcessSweep();
        // set winstate
        if( $isOver )
        {
            $this->winState = $this->board->GetWinState();
        }
        // update board and game, add to move history
        $this->db->UpdateBoard( $this->board,$this->id );
        $this->db->UpdateGame( $this );
        $this->db->AddHistoryMove( $this->GetGameId(),$curTurn,$move );
        // return true if game is over
        return $isOver;
    }

    public function DumpBoard() : array
    {
        return $this->board->ToArray();
    }

    public function ForfeitUserId( int $userId ) : void
    {
        $this->winState = $this->GetSideFromId( $userId )->GetForfeitState();
        $this->db->UpdateGame( $this );        
    }

    public static function FromInfo( GameInfo $gameInfo,Board $board,IMancalaDatabase $db ) : Game
    {
        return new Game( $gameInfo->GetGameId(),$gameInfo->GetTurn(),
            $gameInfo->GetPlayerIds(),$gameInfo->GetActiveSide(),
            $gameInfo->GetWinState(),$board,$db
        );
    }
}
?>