<?php
require_once 'ChiliSql.php';

class Session
{	
	/** @var int */
	private $userId = null;
	/** @var string */
	private $password = null;
	/** @var MancalaDatabase */
	private $db = null;


	public function __construct( MancalaDatabase $db )
	{
		$this->db = $db;

		session_start();
		if( array_key_exists( 'userId',$_SESSION ) )
		{
			$this->userId = $_SESSION['userId'];
			$this->password = $_SESSION['password'];

			if( !$this->VerifyPassword() )
			{
				$this->ClearSession();
			}
		}
	}

	private function VerifyPassword( ?int $userId = null ) : bool
	{
		assert( $this->userId != null,'tried to verify password when login not established' );
		return password_verify( $this->password,$this->db->LoadUserById( $this->userId )->GetPasswordHash() );
	}

	public function Login( string $userName,string $password ) : void
	{
		if( !$this->IsLoggedIn() )
		{
			try
			{
				$user = $this->db->LoadUserByName( $userName );
				$this->userId = $user->GetId();
				$this->password = $password;
				if( $this->VerifyPassword() )
				{
					$_SESSION['userId'] = $this->userId;
					$_SESSION['password'] = $password;
					return;
				}
			}
			catch( ChiliException $e )
			{}
		}
		
		$this->ClearSession();
		throw new ChiliException( 'failed to login (bad name/pass)' );
	}

	private function ClearSession() : void
	{
		$this->userId = null;
		$this->password = null;
		unset( $_SESSION['userId'] );
		unset( $_SESSION['password'] );
	}

	public function Logout() : void
	{
		assert( $this->IsLoggedIn(),'logout when not logged in' );
		$this->ClearSession();
	}

	public function IsLoggedIn() : bool
	{
		return $this->userId != null;
	}

	public function GetUserId() : int
	{
		assert( $this->IsLoggedIn(),'getuserid when not logged in' );
		return $this->userId;
	}
}
?>