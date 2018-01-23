<?php
require_once 'ChiliUtil.php';
require_once 'Side.php';
require_once 'Pot.php';

class Board
{
    /** @var int[] */
    private $pots;

    // public function DoMove( Pot $move,Side $active_side )
    // {
    //     assert( $move->GetSide() == $active_side,'Cannot take from opponent pot' );
    //     assert( !$move->IsMancala(),'Cannot take from mancala' );
    //     assert( $this->GetPot( $move ) != 0,'Cannot take from empty pot' );

        
    // }

    public function GetPot( Pot $pot ) : int
    {
        return $this->pots[$pot->GetIndex()];
    }

    public function SetPot( Pot $pot,int $beads ) : void
    {
        assert( in_range( $beads,0,24 ),'Beads set must be 0~24' );
        $this->pots[$pot->GetIndex()] = $beads;
    }

    public function TakeAllPot( Pot $pot ) : int
    {
        $temp = $this->GetPot( $pot );
        $this->SetPot( $pot,0 );
        return $temp;
    }

    public function IncrementPot( Pot $pot ) : int
    {
        $val = $this->GetPot( $pot ) + 1;
        $this->SetPot( $pot,$val );
        return $val;
    }

    public static function MakeFresh() : Board
    {
        return new Board( [
            4,4,4,4,4,4,0,
            4,4,4,4,4,4,0
        ] );
    }

    protected function __construct( array $pots )
    {
        $this->pots = $pots;
    }
}
?>