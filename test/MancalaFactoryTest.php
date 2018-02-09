<?php
require_once 'MancalaFactory.php';

/** @group gComp */
class MancalaFactoryTest extends PHPUnit\Framework\TestCase
{
	public function testMakeRoomWithPassword()
	{
		$roomName = 'turkey time';
		$roomPassword = 'password';
		$roomId = 7;
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['CreateNewRoom'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'CreateNewRoom' )
			   ->with( $roomName,$roomPassword )
			   ->willReturn( $roomId );
		// the SUT
		$factory = new MancalaFactory( $dbMock );
		
		$room = $factory->MakeRoom( $roomName,$roomPassword );
		$this->assertEquals( $roomId,$room->GetId() );
		$this->assertEquals( $roomName,$room->GetName() );
		$this->assertEquals( 0,$room->GetPlayerCount() );
		$this->assertTrue( $room->IsLocked() );
		$this->assertTrue( $room->VerifyPassword( $roomPassword ) );
	}

	public function testMakeRoomWithoutPassword()
	{
		$roomName = 'turkey time';
		$roomId = 7;
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['CreateNewRoom'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'CreateNewRoom' )
			   ->with( $roomName,null )
			   ->willReturn( $roomId );
		// the SUT
		$factory = new MancalaFactory( $dbMock );
		
		$room = $factory->MakeRoom( $roomName );
		$this->assertEquals( $roomId,$room->GetId() );
		$this->assertEquals( $roomName,$room->GetName() );
		$this->assertEquals( 0,$room->GetPlayerCount() );
		$this->assertFalse( $room->IsLocked() );
	}

	public function testMakeGame()
	{
		$userId0 = 3;
		$userId1 = 7;
		$startSide = Side::Top();
		$gameId = 21;
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['CreateNewGame','UpdateBoard'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'CreateNewGame' )
			   ->with( $userId0,$userId1,$startSide )
			   ->willReturn( $gameId );
		$dbMock->expects( $this->once() )
				->method( 'UpdateBoard' )
				->with( Board::MakeFresh(),$gameId );
		// the SUT
		$factory = new MancalaFactory( $dbMock );
		
		$game = $factory->MakeGame( $userId0,$userId1,$startSide );
		$this->assertEquals( $gameId,$game->GetGameId() );
		$this->assertEquals( $userId0,$game->GetPlayerId( Side::Top() ) );
		$this->assertEquals( $userId1,$game->GetPlayerId( Side::Bottom() ) );
		$this->assertEquals( $startSide,$game->GetActiveSide() );
	}

	public function testLoadGame()
	{
		$gameId = 1;
		$gameInfo = new GameInfo( $gameId,1,[69,420],Side::Top() );
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['LoadGameInfo','LoadBoard'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadGameInfo' )
			   ->with( $gameId )
			   ->willReturn( $gameInfo );
		$dbMock->expects( $this->once() )
				->method( 'LoadBoard' )
				->with( $gameId )
				->willReturn( Board::MakeFresh() );
		// the SUT
		$factory = new MancalaFactory( $dbMock );
		
		$game = $factory->LoadGame( $gameId );
		$this->assertEquals( $gameId,$game->GetGameId() );
		$this->assertEquals( $gameInfo->GetPlayerId( Side::Top() ),$game->GetPlayerId( Side::Top() ) );
		$this->assertEquals( $gameInfo->GetPlayerId( Side::Bottom() ),$game->GetPlayerId( Side::Bottom() ) );
		$this->assertEquals( $gameInfo->GetActiveSide(),$game->GetActiveSide() );
	}
}
?>