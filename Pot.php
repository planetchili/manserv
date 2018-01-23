<?php
require_once 'ChiliUtil.php';
require_once 'Side.php';

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

    public function GetOffset() : int
    {
        return $this->index % 7;
    }

    public function GetOpposite() : Pot
    {
        assert( !$this->IsMancala(),'Cannot get opposite of mancala' );
        return new Pot( 12 - $this->index );
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