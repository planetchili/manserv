<?php
require_once 'Game.php';

/** @group gComp */
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
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadGame' )
               ->with( $gameId )
               ->willReturn( new GameInfo( 
                    $gameId,$turn,$playerIds[0],$playerIds[1],$activeSide
               ) );
        $dbMock->expects( $this->once() )
               ->method( 'LoadBoard' )
               ->with( $gameId )
               ->willReturn( $board );
        
        $game = new Game( $dbMock,$gameId );
        
        $this->assertAttributeEquals( $gameId,'id',$game );
        $this->assertAttributeEquals( $turn,'turn',$game );
        $this->assertAttributeEquals( $playerIds,'playerIds',$game );
        $this->assertAttributeEquals( $activeSide,'activeSide',$game );
        $this->assertAttributeEquals( $board,'board',$game );
    }

    public function testFailCtor()
    {
        $this->expectException( AssertionError::class );

        $gameId = 69;
        $turn = 0;
        $playerIds = [420,420];
        $activeSide = Side::Top();
        $board = new Board( [4,4,4,4,4,4,0,4,4,4,4,4,4,0] );
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['LoadGame'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadGame' )
               ->with( $gameId )
               ->willReturn( new GameInfo( 
                   $gameId,$turn,$playerIds[0],$playerIds[1],$activeSide
               ) );
        
        $game = new Game( $dbMock,$gameId );
    }
    
    /**
     * @dataProvider dataDoMove
     */
    public function testDoMove( Board $board,Side $activeSide,Pot $move,Board $expected_board,int $expect_winState,Side $expected_side )
    {
        $gameId = 69;
        $turn = 43;
        $playerIds = [420,1337];
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['AddHistoryMove','LoadGame','LoadBoard','UpdateBoard','UpdateGame'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadGame' )
               ->with( $gameId )
               ->willReturn( new GameInfo( 
                    $gameId,$turn,$playerIds[0],$playerIds[1],$activeSide
               ) );
        $dbMock->expects( $this->once() )
               ->method( 'LoadBoard' )
               ->with( $gameId )
               ->willReturn( $board );
        $dbMock->expects( $this->once() )
               ->method( 'UpdateBoard' )
               ->with( $expected_board,$gameId );
        $dbMock->expects( $this->once() )
               ->method( 'UpdateGame' )
               ->with( $this->callback( function( GameInfo $actualGame )
                    use ( $gameId,$turn,$expected_side )
               {
                    return $actualGame->GetGameId() === $gameId &&
                        $actualGame->GetTurn() === $turn + 1 &&
                        $actualGame->GetActiveSide() == $expected_side;
               } ) );
        $dbMock->expects( $this->once() )
                ->method( 'AddHistoryMove' )
                ->with( $gameId,$turn,$move );
        
        $game = new Game( $dbMock,$gameId );
        
        $this->assertEquals( $expect_winState != WinState::InProgress,$game->DoMove( $move ) );
        $this->assertEquals( $expect_winState,$game->GetWinState() );
        $this->assertAttributeEquals( $expected_board,'board',$game );
    }
    public function dataDoMove() : array
    {
        return [
            'Mancala' =>
            [new Board([4,4,4,4,4,4,0,4,4,4,4,4,4,0]),Side::Top(),new Pot( 2 ),
             new Board([4,4,0,5,5,5,1,4,4,4,4,4,4,0]),WinState::InProgress,Side::Top()
            ],
            'Normile' =>
            [new Board([4,4,4,4,4,4,0,4,4,4,4,4,4,0]),Side::Top(),new Pot( 0 ),
             new Board([0,5,5,5,5,4,0,4,4,4,4,4,4,0]),WinState::InProgress,Side::Bottom()
            ],
            'Game Overd' =>
            [new Board([0,0,0,0,0,1,12,0,0,1,1,1,0,12]),Side::Top(),new Pot( 5 ),
             new Board([0,0,0,0,0,0,13,0,0,0,0,0,0,15]),WinState::BottomWins,Side::Top()
            ],
        ];
    }
}
?>