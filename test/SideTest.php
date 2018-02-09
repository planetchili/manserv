<?php
require_once 'Side.php';

/** @group gComp */
class SideTest extends PHPUnit\Framework\TestCase
{
    /** @var Side */
    protected $top;
    /** @var Side */
    protected $bottom;

    public function setUp()
    {
        $this->top = Side::Top();
        $this->bottom = Side::Bottom();
    }

    public function testConstructInt()
    {
        $this->assertEquals( $this->top,new Side( 0 ) );
        $this->assertEquals( $this->bottom,new Side( 1 ) );
    }

    public function testFailConstructIntLow()
    {
        $this->expectException( AssertionError::class );
        $dummy = new Side( -42 );
    }

    public function testFailConstructIntHigh()
    {
        $this->expectException( AssertionError::class );
        $dummy = new Side( 69 );
    }

    public function testOpposite()
    {
        $this->assertEquals( $this->top,$this->bottom->GetOpposite() );
        $this->assertEquals( $this->bottom,$this->top->GetOpposite() );
    }

    public function testTesters()
    {
        $this->assertTrue( $this->top->IsTop() );
        $this->assertTrue( $this->bottom->IsBottom() );
    }

    public function testCompare()
    {
        $this->assertTrue( $this->top == new Side( 0 ) );
        $this->assertTrue( $this->bottom == new Side( 1 ) );
        $this->assertTrue( $this->top != new Side( 1 ) );
        $this->assertTrue( $this->bottom != new Side( 0 ) );
    }

    public function testGetIndex()
    {
        $this->assertEquals( 0,$this->top->GetIndex() );        
        $this->assertEquals( 1,$this->bottom->GetIndex() );
    }

    public function testtoString()
    {
        $this->assertEquals( '{TOP}',(string)Side::Top() );
        $this->assertEquals( '{BOT}',(string)Side::Bottom() );
    }
}
?>