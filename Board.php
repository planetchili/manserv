<?php
require_once 'ChiliUtil.php';
require_once 'Side.php';

class Board
{
    /** @var int */
    private $pots;

    private function GetNextPotIndex( Side $active_side ) : int
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