<?php
require_once 'RoomPlayer.php';

/** @group gComp */
class RoomPlayerTest extends PHPUnit\Framework\TestCase
{
	/** @doesNotPerformAssertions */
    public function testCtor() : RoomPlayer
    {
        return new RoomPlayer( 69,false,true );
	}
	
	/** @depends testCtor */
	public function testBasicGetters( RoomPlayer $rp ) : RoomPlayer
	{
		$this->assertEquals( 69,$rp->GetUserId(),'getuserid failed' );
		$this->assertEquals( true,$rp->IsReady(),'isready failed' );
		$this->assertEquals( false,$rp->IsOwner(),'isowner failed' );
		return $rp;
	}

	/** @depends clone testBasicGetters */
	public function testSetters( RoomPlayer $rp )
	{
		$rp->MakeOwner();
		$this->assertTrue( $rp->IsOwner(),'makeowner failed' );
		$rp->ClearReady();		
		$this->assertFalse( $rp->IsReady(),'clearready failed' );
		$rp->MakeReady();		
		$this->assertTrue( $rp->IsReady(),'makeready failed' );
	}

	/** @depends testBasicGetters */
	public function testGetUser( RoomPlayer $rp )
	{
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['LoadUserById'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadUserById' )
               ->with( $rp->GetUserId() )
               ->willReturn( new User( 
				   $rp->GetUserId(),'chili',
				   'chili@planetchili.net','qqqqqqq',true
			   ) );
		
		$user = $rp->GetUser( $dbMock );
		$this->assertEquals( $rp->GetUserId(),$user->GetId(),'bad userid' );
		$this->assertEquals( 'chili',$user->GetName(),'bad name' );
	}

	/** @depends testBasicGetters */
	public function testToAssociative( RoomPlayer $rp )
	{	
        $dbMock = $this ->getMockBuilder( MancalaDatabase::class )
                        ->setMethods( ['LoadUserById'] )
                        ->disableOriginalConstructor()
                        ->getMock();
        $dbMock->expects( $this->once() )
               ->method( 'LoadUserById' )
               ->with( $rp->GetUserId() )
               ->willReturn( new User( 
				   69,'chili',
				   'chili@planetchili.net','qqqqqqq',true
			   ) );	

		$this->assertEquals( [
			'name'=>'chili',
			'id'=>69,
			'isOwner'=>false,
			'isReady'=>true
		],
		$rp->ToAssociative( $dbMock ) );
	}
}
?>