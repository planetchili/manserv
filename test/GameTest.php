<?php
require_once 'Game.php';

class GameTest extends PHPUnit\Framework\TestCase
{
    public function testCtor()
    {
        $gameId = 69;
        $turn = 0;
        $playerIds = [420,1337];
        $activeSide = Side::Top();
        $board = new Board( [4,4,4,4,4,4,0,4,4,4,4,4,4,0] );
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['LoadGame','LoadBoard'] )
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadGame' )
               ->with( $this->equalTo( $gameId ) )
               ->willReturn(
                [
                    'turn' => $turn,
                    'playerIds' => $playerIds,
                    'activeSide' => $activeSide
                ] );
        $dbMock->expects( $this->once() )
               ->method( 'LoadBoard' )
               ->with( $this->equalTo( $gameId ) )
               ->willReturn( $board );
        
        $game = new Game( $dbMock,$gameId );
        
        $this->assertAttributeEquals( $gameId,'gameId',$game );
        $this->assertAttributeEquals( $turn,'turn',$game );
        $this->assertAttributeEquals( $playerIds,'playerIds',$game );
        $this->assertAttributeEquals( $activeSide,'activeSide',$game );
        $this->assertAttributeEquals( $board,'board',$game );
    }
    
    /**
     * @dataProvider dataDoMove
     */
    public function testDoMove( Board $board,Side $activeSide,Pot $move,Board $expected_board,bool $expect_end,Side $expected_side )
    {
        $gameId = 69;
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['LoadGame','LoadBoard'] )
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadGame' )
               ->with( $this->equalTo( $gameId ) )
               ->willReturn(
                [
                    'turn' => 0,
                    'playerIds' => [420,1337],
                    'activeSide' => $activeSide
                ] );
        $dbMock->expects( $this->once() )
               ->method( 'LoadBoard' )
               ->with( $this->equalTo( $gameId ) )
               ->willReturn( $board );    
        
        $game = new Game( $dbMock,$gameId );
        
        $this->assertEquals( $expect_end,$game->DoMove( $move ) );
        $this->assertAttributeEquals( $expected_board,'board',$game );
    }
    public function dataDoMove() : array
    {
        return [
            'Mancala' =>
            [new Board([4,4,4,4,4,4,0,4,4,4,4,4,4,0]),Side::Top(),new Pot( 2 ),
             new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),false,Side::Top()
            ],
            'Normile' =>
            [new Board([4,4,4,4,4,4,0,4,4,4,4,4,4,0]),Side::Top(),new Pot( 0 ),
             new Board([0,5,5,5,5,4,0,4,4,4,4,4,4,0]),false,Side::Bottom()
            ],
            'Game Overd' =>
            [new Board([0,0,0,0,0,1,12,0,0,1,1,1,0,12]),Side::Top(),new Pot( 5 ),
             new Board([0,0,0,0,0,0,13,0,0,0,0,0,0,15]),true,Side::Top()
            ],
        ];
    }
}
?>