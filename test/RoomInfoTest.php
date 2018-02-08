<?php
require_once 'RoomInfo.php';

class RoomInfoTest extends PHPUnit\Framework\TestCase
{
	/** @doesNotPerformAssertions */
    public function testCtor() : RoomInfo
    {
		return new RoomInfo( 420,'chili game',null,null,
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 1,false,false )
		] );
	}
	
	/** @depends testCtor */
	public function testBasicGetters( RoomInfo $ri ) : RoomInfo
	{
		$this->assertEquals( 420,$ri->GetId(),'getid failed' );
		$this->assertEquals( 'chili game',$ri->GetName(),'getname failed' );
		$this->assertEquals( 2,$ri->GetPlayerCount(),'getplayercount failed' );
		$this->assertFalse( $ri->IsEngaged(),'isengaged should be false' );
		$this->assertFalse( $ri->IsLocked(),'islocked should be false' );
		$this->assertEquals( new RoomPlayer( 1,false,false ),$ri->GetPlayer( 1 ) );
		$this->assertEquals( [
				new RoomPlayer( 69,true,true ),
				new RoomPlayer( 1,false,false )
			],$ri->GetPlayers()
		);
		return $ri;
	}

	/** @depends testCtor */
	public function testEngageLockTrue()
	{
		$ri = new RoomInfo( 420,'chili game',13,'dummyhash',
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
		$ri = new RoomInfo( 420,'chili game',13,
			'$2y$10$.39qCFqFUwiB870rFCXlHOi'.
			'o3598qLPgPB7IpWRReDeGt755A0v2m',
		[
			new RoomPlayer( 69,true,true ),
			new RoomPlayer( 1,false,false )
		] );
		
		$this->assertTrue( $ri->VerifyPassword( 'chilipass' ) );
	}
	// TODO: test failures
}
?>