<?php
require_once 'Board.php';

class BoardTest extends PHPUnit\Framework\TestCase
{
    /** @var Board */
    protected $fresh;

    public function setUp()
    {
        $this->fresh = Board::MakeFresh();
    }

    public function testGetPot()
    {
        $this->assertEquals( 4,$this->fresh->GetPot( new Pot( 0 ) ) );
        $this->assertEquals( 0,$this->fresh->GetPot( new Pot( 13 ) ) );
    }

    public function testSetPot()
    {
        $pot = new Pot( 9 );
        $this->fresh->setPot( $pot,12 );
        $this->assertEquals( 12,$this->fresh->GetPot( $pot ) );
    }

    public function testFailSetPot()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->SetPot( new Pot( 6 ),25 );
    }

    public function testIncrementPot()
    {
        $four = new Pot( 7 );
        $zero = new Pot( 13 );
        $this->assertEquals( 5,$this->fresh->IncrementPot( $four ) );
        $this->assertEquals( 5,$this->fresh->GetPot( $four ) );
        $this->assertEquals( 1,$this->fresh->IncrementPot( $zero ) );
        $this->assertEquals( 1,$this->fresh->GetPot( $zero ) );
    }

    public function testTakeAllPot()
    {
        $four = new Pot( 0 );
        $zero = new Pot( 6 );
        $this->assertEquals( 4,$this->fresh->TakeAllPot( $four ) );
        $this->assertEquals( 0,$this->fresh->GetPot( $four ) );
        $this->assertEquals( 0,$this->fresh->TakeAllPot( $zero ) );
        $this->assertEquals( 0,$this->fresh->GetPot( $zero ) );
    }
}
?>