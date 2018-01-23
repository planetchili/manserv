<?php
require_once 'Pot.php';

class PotTest extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataGetOffset
     */
    public function testGetOffset( Pot $pot,int $offset )
    {
        $this->assertEquals( $offset,$pot->GetOffset() );
    }
    public function dataGetOffset() : array
    {
        return [
            [new Pot( 13 ),6],
            [new Pot( 11 ),4],
            [new Pot( 8  ),1],
            [new Pot( 5  ),5],
            [new Pot( 3  ),3],
            [new Pot( 0  ),0]
        ];
    }
    
    public function testIsMancala()
    {
        $this->assertTrue( (new Pot( 6 ))->IsMancala() );
        $this->assertTrue( (new Pot( 13 ))->IsMancala() );
        $this->assertFalse( (new Pot( 0 ))->IsMancala() );
        $this->assertFalse( (new Pot( 5 ))->IsMancala() );
        $this->assertFalse( (new Pot( 7 ))->IsMancala() );
        $this->assertFalse( (new Pot( 12 ))->IsMancala() );
    }

    /**
     * @dataProvider dataGetSide
     */
    public function testGetSide( Pot $pot,Side $side )
    {
        $this->assertEquals( $side,$pot->GetSide() );
    }
    public function dataGetSide() : array
    {
        return [
            [new Pot( 13 ),new Side( 1 )],
            [new Pot( 11 ),new Side( 1 )],
            [new Pot( 8  ),new Side( 1 )],
            [new Pot( 5  ),new Side( 0 )],
            [new Pot( 3  ),new Side( 0 )],
            [new Pot( 0  ),new Side( 0 )]
        ];
    }

    /**
     * @dataProvider dataGetOpposite
     */
    public function testGetOpposite( Pot $input,Pot $opposite )
    {
        $this->assertEquals( $opposite,$input->GetOpposite() );
    }
    public function dataGetOpposite() : array
    {
        return [
            [new Pot( 12 ),new Pot( 0 )],
            [new Pot( 11 ),new Pot( 1 )],
            [new Pot( 8  ),new Pot( 4 )],
            [new Pot( 5  ),new Pot( 7 )],
            [new Pot( 3  ),new Pot( 9 )],
            [new Pot( 0  ),new Pot( 12 )]
        ];
    }

    public function testFailGetOpposite()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        (new Pot( 6 ))->GetOpposite();
    }
    
    /**
     * @dataProvider dataFromSide
     */
    public function testFromSide( Side $side,int $offset,int $index )
    {
        $this->assertEquals( new Pot( $index ),
            Pot::FromSideOffset( $side,$offset )
        );
    }
    public function dataFromSide() : array
    {
        return [
            [Side::Top(),   0,0],
            [Side::Top(),   4,4],
            [Side::Top(),   6,6],
            [Side::Bottom(),0,7],
            [Side::Bottom(),2,9],
            [Side::Bottom(),6,13]
        ];
    }

    public function testFailFromSide()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        Pot::FromSideOffset( Side::Top(),7 );
    }
}
?>