<?php
require_once 'Room.php';

/** @group gComp */
class RoomTest extends PHPUnit\Framework\TestCase
{
	/** @doesNotPerformAssertions */
    public function testCtor() : Room
    {
        return new Room( 420,'chili game',null,null,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false )
		] );
	}

	/** @depends clone testCtor */
	public function testAddPlayer( Room $rp )
	{
		$player = new RoomPlayer( 13,false );
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['AddMembership'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'AddMembership' )
               ->with( $player,$rp->GetId() );
		
		$rp->AddPlayer( $player->GetUserId(),$dbMock );
		$this->assertEquals( 3,$rp->GetPlayerCount() );
	}

	/** @depends clone testCtor */
	public function testRemovePlayer( Room $rp )
	{
		$player = $rp->GetPlayer( 0 );
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['RemoveMembership'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'RemoveMembership' )
               ->with( $player->GetUserId(),$rp->GetId() );
		
		$rp->RemovePlayer( $player->GetUserId(),$dbMock );
		$this->assertEquals( 1,$rp->GetPlayerCount() );
		$this->assertEquals( 11,$rp->GetPlayer( 0 )->GetUserId() );
		$this->assertTrue( $rp->GetPlayer( 0 )->IsOwner() );
	}

	/** @depends clone testCtor */
	public function testEngageGame( Room $rp )
	{
		// needed for engage game (starting player selection)
		srand( 69 );
		$exSide = new Side( rand( 0,1 ) );
		// make expected room
		$exRoom = new Room( 420,'chili game',1337,null,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false,true )
		] );
		// ready player 1
		$rp->GetPlayer( 1 )->MakeReady();
		// mock the db
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['UpdateRoom','CreateNewGame'] )
                        ->disableOriginalConstructor()
						->getMock();
		$dbMock->expects( $this->once() )
				->method( 'CreateNewGame' )
				->with( 69,11,$exSide )
				->willReturn( 1337 );		
        $dbMock->expects( $this->once() )
               ->method( 'UpdateRoom' )
			   ->with( $exRoom );
		// seed with same to ensure expected start side
		srand( 69 );
		$this->assertEquals( 1337,$rp->EngageGame( $dbMock ) );
		$this->assertTrue( $rp->IsEngaged() );
		$this->assertEquals( 1337,$rp->GetGameId() );
	}

	/** @depends clone testCtor */
	public function testClearGame()
	{
		// make engaged game
		$room = new Room( 420,'chili game',1337,null,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false,true )
		] );
		$room->ClearGame();
		$this->assertFalse( $room->IsEngaged() );
	}
}
?>