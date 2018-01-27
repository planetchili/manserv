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

    public function testDumpInMancala()
    {
        $man = new Pot( 6 );
        $this->assertEquals( 5,$this->fresh->DumpInMancala( Side::Top(),5 ) );
        $this->assertEquals( 5,$this->fresh->GetPot( $man ) );
        $this->assertEquals( 8,$this->fresh->DumpInMancala( Side::Top(),3 ) );
        $this->assertEquals( 8,$this->fresh->GetPot( $man ) );
    }

    public function testFailDumpInMancala()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->DumpInMancala( Side::Top(),25 );
    }

    /**
     * @dataProvider dataDoMove
     */
    public function testDoMove( Board $board,Side $active_side,Pot $move,
        Board $expected_board,bool $extra_turn_expected )
    {
        $this->assertEquals( $extra_turn_expected,
            $board->DoMove( $move,$active_side )
        );
        $this->assertEquals( $expected_board,$board );
    }
    public function dataDoMove() : array
    {
        return [
            'Top Mancala'=>
            [Board::MakeFresh(),Side::Top(),new Pot( 2 ),
             new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),true
            ],
            'Top Around Own'=>
            [new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),Side::Top(),new Pot( 5 ),
             new Board([4,4,0,5,5,0,2,5,5,5,5,4,4,0]),false
            ],
            'Bottom Mancala'=>
            [new Board([4,4,0,5,5,0,2,5,5,5,5,4,4,0]),Side::Bottom(),new Pot( 8 ),
             new Board([4,4,0,5,5,0,2,5,0,6,6,5,5,1]),true
            ],
            'Bottom Around Own'=>
            [new Board([4,4,0,5,5,0,2,5,0,6,6,5,5,1]),Side::Bottom(),new Pot( 12 ),
             new Board([5,5,1,6,5,0,2,5,0,6,6,5,0,2]),false
            ],
            'Top Steal'=>
            [new Board([5,5,1,6,5,0,2,5,0,6,6,5,0,2]),Side::Top(),new Pot( 0 ),
             new Board([0,6,2,7,6,0,8,0,0,6,6,5,0,2]),false
            ],
            'Bottom Steal (Empty)'=>
            [new Board([2,8,0,2,9,2,9,1,1,1,0,7,2,4]),Side::Bottom(),new Pot( 9 ),
             new Board([2,8,0,2,9,2,9,1,1,0,0,7,2,5]),false
            ],
            'Top Around World'=>
            [new Board([3,8,0,0,1,4,10,2,2,1,0,9,3,5]),Side::Bottom(),new Pot( 11 ),
             new Board([4,9,1,1,2,5,10,3,2,1,0,0,4,6]),false
            ],
            'Bottom Around Own (Otherside Empty)'=>
            [new Board([0,0,2,0,0,2,15,1,5,0,3,13,3,5]),Side::Bottom(),new Pot( 12 ),
             new Board([1,1,2,0,0,2,15,1,5,0,3,13,0,6]),false
            ],
            'Bottom Around World (Return)'=>
            [new Board([1,1,2,0,0,2,15,1,5,0,3,13,0,6]),Side::Bottom(),new Pot( 11 ),
             new Board([2,0,3,1,1,3,15,2,6,1,4,0 ,1,10]),false
            ]
        ];
    }

    public function testFailSideDoMove1()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->DoMove( new Pot( 7 ),Side::Top() );
    }
    public function testFailSideDoMove2()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->DoMove( new Pot( 0 ),Side::Bottom() );
    }
    public function testFailMancalaDoMove1()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->DoMove( new Pot( 6 ),Side::Top() );
    }
    public function testFailMancalaDoMove2()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->DoMove( new Pot( 13 ),Side::Bottom() );
    }
    public function testFailEmptyDoMove()
    {
        $zero = new Pot( 0 );
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->TakeAllPot( $zero );
        $this->fresh->DoMove( $zero,Side::Top() );
    }

    /**
     * @dataProvider dataCheckIfSideEmpty
     */
    public function testCheckIfSideEmpty( Board $board,Side $side,bool $expect_empty )
    {
        $this->assertEquals( $expect_empty,$board->CheckIfSideEmpty( $side ) );
    }
    public function dataCheckIfSideEmpty() : array
    {
        return [
            [new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),Side::Top(),false],
            [new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),Side::Bottom(),false],
            [new Board([0,0,0,1,0,0,23,4,4,4,4,4,4,0]),Side::Top(),false],
            [new Board([4,4,4,4,4,4,0,0,0,0,1,0,0,23]),Side::Bottom(),false],
            [new Board([0,0,0,0,0,0,24,4,4,4,4,4,4,0]),Side::Top(),true],
            [new Board([4,4,4,4,4,4,0,0,0,0,0,0,0,24]),Side::Bottom(),true]
        ];
    }

    /**
     * @dataProvider dataCollectSide
     */
    public function testCollectSide( Board $board,Side $side,int $expected_beads )
    {
        $this->assertEquals( $expected_beads,$board->CollectSide( $side ) );
    }
    public function dataCollectSide() : array
    {
        return [
            [new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]), Side::Top(),23],
            [new Board([4,4,0,2,5,5,1,4,1,10,4,4,4,0]),Side::Bottom(),27],
            [new Board([0,0,0,0,0,0,23,4,4,4,4,4,4,0]),Side::Top(),0],
            [new Board([4,4,4,4,4,4,0,0,0,0,0,0,0,23]),Side::Bottom(),0]
        ];
    }

    /**
     * @dataProvider dataProcessSweep
     */
    public function testProcessSweep( Board $board,Board $expected_board,
        bool $expected_state )
    {
        $this->assertEquals( $expected_state,$board->ProcessSweep() );
        $this->assertEquals( $expected_board,$board );
    }
    public function dataProcessSweep() : array
    {
        return [
            [new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),
             new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),false
            ],
            [new Board([0,0,0,0,0,0,1,4,4,4,4,4,4,0 ]),
             new Board([0,0,0,0,0,0,1,0,0,0,0,0,0,24]),true
            ],
            [new Board([4,4,4,4,4,4,0 ,0,0,0,0,0,0,1]),
             new Board([0,0,0,0,0,0,24,0,0,0,0,0,0,1]),true
            ],
        ];
    }

    /**
     * @dataProvider dataGetWinState
     */
    public function testGetWinState( Board $board,int $expected_state )
    {
        $this->assertEquals( $expected_state,$board->GetWinState() );
    }
    public function dataGetWinState() : array
    {
        return [
            [new Board([0,0,0,0,0,0,24,0,0,0,0,0,0,1]),WinState::TopWins],
            [new Board([0,0,0,0,0,0,1,0,0,0,0,0,0,24]),WinState::BottomWins],
            [new Board([0,0,0,0,0,0,12,0,0,0,0,0,0,12]),WinState::Tie]
        ];
    }

    public function testFailGetWinState()
    {
        $this->expectException( PHPUnit\Framework\Error\Error::class );
        $this->fresh->GetWinState();
    }
}
?>