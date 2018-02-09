<?php
require_once 'User.php';

/** @group gComp */
class UserTest extends PHPUnit\Framework\TestCase
{
    public function testCtorGetters()
    {
        $user = new User( 1,'Chili','chiLi@planetchili.net','password',false );
        $this->assertEquals( 1,$user->GetId() );
        $this->assertEquals( 'chili',$user->GetName() );        
        $this->assertEquals( 'chili@planetchili.net',$user->GetEmail() );
        $this->assertTrue( password_verify( 'password',$user->GetPasswordHash() ) );
        $this->assertTrue( $user->VerifyPassword( 'password' ) );
        $this->assertFalse( $user->VerifyPassword( 'pissword' ) );
    }

    public function testToArray()
    {
        $user = new User( 1,'Chili','chiLi@planetchili.net','password',false );
        $this->assertEquals(
            [
                'id' => 1,
                'name' => 'chili',
                'email' => 'chili@planetchili.net'
            ],$user->ToArray()
        );
    }
}
?>