<?php
require_once __DIR__.'/ChiliSql.php';

class Session
{	
	/** @var User */
	private $user = null;
	/** @var MancalaDatabase */
	private $db = null;


	public function __construct( MancalaDatabase $db )
	{
		$this->db = $db;

		session_start();
		if( array_key_exists( 'user',$_SESSION ) )
		{
			$this->user = $_SESSION['user'];
		}
	}

	public function Login( string $userName,string $password ) : void
	{
		if( !$this->IsLoggedIn() )
		{
			try
			{
				$this->user = $this->db->LoadUserByName( $userName );
				if( password_verify( $password,$this->user->GetPasswordHash() ) )
				{
					$_SESSION['user'] = $this->user;
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
		$this->user = null;
		unset( $_SESSION['user'] );
	}

	public function Logout() : void
	{
		assert( $this->IsLoggedIn(),'logout when not logged in' );
		$this->ClearSession();
	}

	public function IsLoggedIn() : bool
	{
		return $this->user != null;
	}

	public function GetUser() : User
	{
		assert( $this->IsLoggedIn(),'getuser when not logged in' );
		return $this->user;
	}

	public function GetUserId() : int
	{
		return $this->user->GetId();
	}
}
?>