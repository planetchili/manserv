<?php
require_once __DIR__.'/ChiliUtil.php';
require_once __DIR__.'/WinState.php';

class Side
{
    /** @var int */
    private $side;

    public static function Top() : side
    {
        return new Side( 0 );
    }

    public static function Bottom() : Side
    {
        return new Side( 1 );
    }

    public function IsTop() : bool
    {
        return $this->side == 0;
    }

    public function IsBottom() : bool
    {
        return $this->side == 1;
    }

    public function GetOpposite() : Side
    {
        return new Side( 1 - $this->side );
    }

    public function GetIndex() : int
    {
        return $this->side;
    }

    public function __construct( int $side )
    {
        assert( in_range( $side,0,1 ),'Side value must be 0 or 1' );
        $this->side = $side;
    }

    public function GetWinState() : int
    {
        return ($this->side == 0) ? WinState::TopWins : WinState::BottomWins;
    }

    public function __toString() : string
    {
        return $this->side == 0 ? '{TOP}' : '{BOT}';
    }
}
?>