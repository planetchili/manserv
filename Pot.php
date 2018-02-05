<?php
require_once __DIR__.'/ChiliUtil.php';
require_once __DIR__.'/Side.php';

class Pot
{
    /** @var int */
    private $index;

    public function IsMancala() : bool
    {
        return ($this->index + 1) % 7 == 0;
    }

    public function GetSide() : Side
    {
        return new Side( intdiv( $this->index,7 ) );
    }

    public function GetIndex() : int
    {
        return $this->index;
    }

    public function GetNext( Side $side ) : Pot
    {
        assert( !($this->GetSide() != $side && $this->IsMancala()),'Next origin cannot be other mancala' );
        if( $this->GetOffset() == 5 && $this->GetSide() != $side )
        {
            return new Pot( ($this->index + 2) % 14 );
        }
        else
        {
            return new Pot( ($this->index + 1) % 14 );
        }
    }

    public function GetOffset() : int
    {
        return $this->index % 7;
    }

    public function GetOpposite() : Pot
    {
        assert( !$this->IsMancala(),'Cannot get opposite of mancala' );
        return new Pot( 12 - $this->index );
    }

    public function __toString() : string
    {
        return brace( $this->index.$this->GetSide().$this->GetOffset().($this->IsMancala() ? '(MAN)' : '') );
    }
    
    public function __construct( int $index )
    {
        assert( in_range( $index,0,13 ),'Pot index must be 0~13' );
        $this->index = $index;
    }

    public static function FromSideOffset( Side $side,int $offset ) : Pot
    {
        assert( in_range( $offset,0,6 ),'Offset must be 0~6' );
        return new Pot( $side->GetIndex() * 7 + $offset );
    }
}
?>