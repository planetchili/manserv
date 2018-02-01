<?php
require_once 'User.php';

class UserTest extends PHPUnit\Framework\TestCase
{
    public function testCtor()
    {
        $user = new User( 1,'Chili','chiLi@planetchili.net','password',false );
        $this->assertEquals( 1,$user->GetId() );
        $this->assertEquals( 'chili',$user->GetName() );        
        $this->assertEquals( 'chili@planetchili.net',$user->GetEmail() );
        $this->assertTrue( password_verify( 'password',$user->GetPasswordHash() ) );
    }
}
?>