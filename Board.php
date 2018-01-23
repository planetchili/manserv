<?php
require_once 'ChiliUtil.php';
require_once 'Side.php';
require_once 'Pot.php';

class Board
{
    /** @var array */
    private $pots;

    private function GetNextPotIndex( Pot $pot,Side $active_side ) : int
    {
        
    }

    protected function InitPots() : void
    {
        $this->pots = [
            4,4,4,4,4,4,0,
            4,4,4,4,4,4,0
        ];
    }

    // protected function LoadPotsFromDB( ChiliSql $conn,int $gameId ) : void
    // {

    // }
}
?>