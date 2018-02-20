<?php
require_once 'Room.php';

/** @group gComp */
class RoomTest extends PHPUnit\Framework\TestCase
{
	/** @doesNotPerformAssertions */
    public function testCtor() : Room
    {
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->disableOriginalConstructor()
                        ->getMock();
        return new Room( 420,'chili game',null,null,$dbMock,
		[			
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false )
		] );
	}
	
	/** @depends testCtor */
	public function testBasicGetters( Room $ri ) : Room
	{
		$this->assertEquals( 420,$ri->GetId(),'getid failed' );
		$this->assertEquals( 'chili game',$ri->GetName(),'getname failed' );
		$this->assertEquals( 2,$ri->GetPlayerCount(),'getplayercount failed' );
		$this->assertFalse( $ri->IsEngaged(),'isengaged should be false' );
		$this->assertFalse( $ri->IsLocked(),'islocked should be false' );
		$this->assertEquals( new RoomPlayer( 11,false,false ),$ri->GetPlayers()[1] );
		$this->assertEquals( [
				new RoomPlayer( 69,true,true ),
				new RoomPlayer( 11,false,false )
			],$ri->GetPlayers()
		);
		return $ri;
	}

	/** @depends testCtor */
	public function testEngageLockTrue()
	{
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->disableOriginalConstructor()
                        ->getMock();
		$ri = new Room( 420,'chili game',13,'dummyhash',$dbMock,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 1,false,false )
		] );

		$this->assertTrue( $ri->IsEngaged(),'isengaged should be true' );
		$this->assertTrue( $ri->IsLocked(),'islocked should be true' );
	}

	/** @depends testCtor */
	public function testPasswordVerify()
	{
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->disableOriginalConstructor()
                        ->getMock();
		$ri = new Room( 420,'chili game',13,
			'$2y$10$.39qCFqFUwiB870rFCXlHOi'.
			'o3598qLPgPB7IpWRReDeGt755A0v2m',$dbMock,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 1,false,false )
		] );
		
		$this->assertTrue( $ri->VerifyPassword( 'chilipass' ) );
	}

	// TODO: test failures

	/** @depends clone testBasicGetters */
	public function testAddPlayer()
	{
		$player = new RoomPlayer( 13,false );
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['AddMembership'] )
                        ->disableOriginalConstructor()
                        ->getMock();
		$rp = new Room( 420,'chili game',null,null,$dbMock,
		[			
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false )
		] );
        $dbMock->expects( $this->once() )
               ->method( 'AddMembership' )
               ->with( $player,$rp->GetId() );
		
		$rp->AddPlayer( $player->GetUserId(),$dbMock );
		$this->assertEquals( 3,$rp->GetPlayerCount() );
	}

	/** @depends clone testBasicGetters */
	public function testRemovePlayer()
	{
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['RemoveMembership','UpdateMembership'] )
                        ->disableOriginalConstructor()
                        ->getMock();
		
		$rp = new Room( 420,'chili game',null,null,$dbMock,
		[			
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false )
		] );
		$player = $rp->GetPlayers()[0];
        $dbMock->expects( $this->once() )
               ->method( 'RemoveMembership' )
			   ->with( $player->GetUserId(),$rp->GetId() );
		$dbMock->expects( $this->once() )
			   ->method( 'UpdateMembership' )
			   ->with( new RoomPlayer( 11,true,false ),420 );
		
		$rp->RemovePlayer( $player->GetUserId(),$dbMock );
		$this->assertEquals( 1,$rp->GetPlayerCount() );
		$this->assertEquals( 11,$rp->GetPlayers()[0]->GetUserId() );
		$this->assertTrue( $rp->GetPlayers()[0]->IsOwner() );
	}

	/** @depends clone testBasicGetters */
	public function testEngageGame()
	{
		// needed for engage game (starting player selection)
		srand( 69 );
		$exSide = new Side( rand( 0,1 ) );
		// mock the db
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['UpdateRoom','CreateNewGame'] )
                        ->disableOriginalConstructor()
						->getMock();
		// make expected room
		$exRoom = new Room( 420,'chili game',1337,null,$dbMock,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false,true )
		] );
		$dbMock->expects( $this->once() )
				->method( 'CreateNewGame' )
				->with( 69,11,$exSide )
				->willReturn( 1337 );		
        $dbMock->expects( $this->once() )
               ->method( 'UpdateRoom' )
			   ->with( $exRoom );
	
		$rp = new Room( 420,'chili game',null,null,$dbMock,
		[			
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false )
		] );
		// ready player 1
		$rp->GetPlayers()[1]->MakeReady();

		// seed with same to ensure expected start side
		srand( 69 );
		$this->assertEquals( 1337,$rp->EngageGame( $dbMock ) );
		$this->assertTrue( $rp->IsEngaged() );
		$this->assertEquals( 1337,$rp->GetGameId() );
	}

	/** @depends clone testBasicGetters */
	public function testClearGame()
	{
		// mock db
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['UpdateRoom'] )
                        ->disableOriginalConstructor()
						->getMock();

		$room = new Room( 420,'chili game',69,null,$dbMock,
		[			
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false,true )
		] );
		$dbMock->expects( $this->once() )
				->method( 'UpdateRoom' )
				->with( $room );

		$room->ClearGame( $dbMock );
		$this->assertFalse( $room->IsEngaged() );
	}

	public function testReadyPlayer()
	{
		// mock the db
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['UpdateMembership'] )
                        ->disableOriginalConstructor()
						->getMock();
		// make expected room
		$rp = new Room( 420,'chili game',1337,null,$dbMock,
		[
			new RoomPlayer( 69,true ),
			new RoomPlayer( 11,false )
		] );
		$dbMock->expects( $this->once() )
				->method( 'UpdateMembership' )
				->with( new RoomPlayer( 69,true,true ),420 );	
	
		// ready player 0
		$rp->ReadyPlayer( 69 );
		$this->assertTrue( $rp->GetPlayers()[0]->IsReady() );
	}

	// might be a problem
	public function testUnreadyPlayer()
	{
		// mock the db
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['UpdateMembership'] )
                        ->disableOriginalConstructor()
						->getMock();
		// make expected room
		$rp = new Room( 420,'chili game',1337,null,$dbMock,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false,true )
		] );
		$dbMock->expects( $this->once() )
				->method( 'UpdateMembership' )
				->with( new RoomPlayer( 69,true,false ),420 );	
	
		// ready player 0
		$rp->UnreadyPlayer( 69 );
		$this->assertFalse( $rp->GetPlayers()[0]->IsReady() );
	}
	
	/** @depends testBasicGetters */
	public function testToAssociative()
	{
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
						->disableOriginalConstructor()
						->setMethods( ['LoadUserById'] )
						->getMock();		
		$dbMock ->expects( $this->exactly( 2 ) )
				->method( 'LoadUserById' )
				->withConsecutive( [69],[11] )
				->willReturnOnConsecutiveCalls(
					new User( 69,'chili','diiik','$hash',true ),
					new User( 11,'mom','diiik','$hash',true )
				);
        $ri = new Room( 420,'chili game',null,null,$dbMock,
		[			
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 11,false )
		] );

		$this->assertEquals( [
				'id'=>420,
				'name'=>'chili game',
				'gameId'=>null,
				'players'=>
					[			
						[
							'name'=>'chili',
							'isOwner'=>true,
							'isReady'=>true
						],			
						[
							'name'=>'mom',
							'isOwner'=>false,
							'isReady'=>false
						]
					]
			],
			$ri->ToAssociative()
		);
	}
}
?>