<?php

class User
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $email;
    /** @var string */
    private $passwordHash;

    public function __construct( int $id,string $name,string $email,string $password,bool $hashed = false )
    {
        // set members from ctor params
        $this->id = $id;
        $this->name = strtolower( $name );
        $this->email = strtolower( $email );
        $this->passwordHash = $hashed ? $password : password_hash( $password,PASSWORD_DEFAULT );
    }

    public function GetId() : int
    {
        return $this->id;
    }

    public function GetName() : string
    {
        return $this->name;
    }

    public function GetEmail() : string
    {
        return $this->email;
    }

    public function GetPasswordHash() : string
    {
        return $this->passwordHash;
    }

    public function VerifyPassword( string $password ) : bool
    {
        return password_verify( $password,$this->GetPasswordHash() );
    }

    public function ToArray() : array
    {
        return ['id'=>$this->id,'name'=>$this->name,'email'=>$this->email];
    }
}
?>