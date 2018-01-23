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
        assert( $this->index != 0 && $this->index != 6,'Cannot get opposite of mancala' );
        return 12 - $this->index;
    }
    
    public function __construct( int $index )
    {
        assert( in_range( $index,0,13 ),'Pot index must be 0~13' );
        $this->index = $index;
    }

    public static function FromSideOffset( Side $side,int $offset ) : Pot
    {
        return new Pot( $side-> )
    }
}
?>