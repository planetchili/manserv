<?php
require_once 'ChiliUtil.php';
require_once 'Side.php';
require_once 'Pot.php';
require_once 'WinState.php';

class Board
{
    /** @var int[] */
    private $pots;

    public function DoMove( Pot $move,Side $active_side ) : bool
    {
        assert( $move->GetSide() == $active_side,'Cannot take from opponent pot' );
        assert( !$move->IsMancala(),'Cannot take from mancala' );
        assert( $this->GetPot( $move ) != 0,'Cannot take from empty pot' );

        // remove beads from move pot
        $beads = $this->TakeAllPot( $move );
        // sow all beads but last one
        for( $cur = $move->GetNext( $active_side ); $beads > 1; $beads--,
            $cur = $cur->GetNext( $active_side ) )
        {
            $this->IncrementPot( $cur );
        }
        // sow last bead & check for steal
        if( $this->IncrementPot( $cur ) == 1 && $cur->GetSide() == $active_side
            && !$cur->IsMancala() )
        {
            $stolen = $this->TakeAllPot( $cur->GetOpposite() );
            $this->SetPot( $cur,0 );
            $this->DumpInMancala( $cur->GetSide(),$stolen + 1 );
        }
        // return true if mancala (extra turn)
        return $cur->IsMancala();
    }

    public function CheckIfSideEmpty( Side $side ) : bool
    {
        for( $offset = 0; $offset < 6; $offset++ )
        {
            if( $this->GetPot( Pot::FromSideOffset( $side,$offset ) ) != 0 )
            {
                return false;
            }
        }
        return true;
    }

    public function CollectSide( Side $side ) : int
    {
        $sum = 0;
        for( $offset = 0; $offset < 6; $offset++ )
        {
            $sum += $this->TakeAllPot( Pot::FromSideOffset( $side,$offset ) );
        }
        return $sum;
    }

    /** sweeps side if necessary and returns true if game over */
    public function ProcessSweep() : bool
    {
        if( $this->CheckIfSideEmpty( Side::Top() ) )
        {
            $this->DumpInMancala( Side::Bottom(),
                $this->CollectSide( Side::Bottom() )
            );
            return true;
        }
        else if( $this->CheckIfSideEmpty( Side::Bottom() ) )
        {
            $this->DumpInMancala( Side::Top(),
                $this->CollectSide( Side::Top() )
            );
            return true;
        }
        return false;
    }

    /** Game must be over to call GetWinState() */
    public function GetWinState() : int
    {
        assert( $this->CheckIfSideEmpty( Side::Top() ) && 
            $this->CheckIfSideEmpty( Side::Bottom() )
        );

        $mantop = $this->GetPot( new Pot( 6 ) );
        $manbot = $this->GetPot( new Pot( 13 ) );
        if( $mantop > $manbot )
        {
            return WinState::TopWins;
        }
        else if( $manbot > $mantop )
        {            
            return WinState::BottomWins;
        }
        return WinState::Tie;
    }

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

    public function DumpInMancala( Side $dump_side,int $beads ) : int
    {
        assert( in_range( $beads,0,24 ),'Beads set must be 0~24' );
        $pot = Pot::FromSideOffset( $dump_side,6 );
        $val = $this->GetPot( $pot ) + $beads;
        $this->SetPot( $pot,$val );
        return $val;
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

    public function __construct( array $pots )
    {
        assert( count( $pots ) == 14 );
        $this->pots = $pots;
    }}
?>