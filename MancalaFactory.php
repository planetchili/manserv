<?php
require_once __DIR__.'/Side.php';
require_once __DIR__.'/GameInfo.php';
require_once __DIR__.'/Game.php';
require_once __DIR__.'/IMancalaDatabase.php';
require_once __DIR__.'/Room.php';
require_once __DIR__.'/Board.php';
require_once __DIR__.'/User.php';

class MancalaFactory
{
	/** @var IMancalaDatabase */
	private $db;

	public function __construct( IMancalaDatabase $db )
	{
		$this->db = $db;
	}

	public function MakeRoom( string $name,?string $password = null ) : IRoom
	{
		$passwordHash = ($password == null) ? null : password_hash( $password,PASSWORD_DEFAULT );
		$roomId = $this->db->CreateNewRoom( $name,$password );
		// TODO: reorder room ctor params and add default values
		return new Room( $roomId,$name,null,$passwordHash );
	}

	public function MakeGame( int $userId0,int $userId1,Side $startSide ) : Game
	{
		$board = Board::MakeFresh();
		$gameId = $this->db->CreateNewGame( $userId0,$userId1,$startSide );
		$this->db->UpdateBoard( $board,$gameId );
		return new Game( $gameId,0,[$userId0,$userId1],$startSide,
			WinState::InProgress,$board,$this->db
		);
	}

	public function LoadGame( int $gameId ) : Game
	{
		$gameInfo = $this->db->LoadGameInfo( $gameId );
		$board = $this->db->LoadBoard( $gameId );
		return Game::FromInfo( $gameInfo,$board,$this->db );
	}

	public function MakeUser( string $name,string $email,string $password ) : User
	{
		$passwordHash = password_hash( $password,PASSWORD_DEFAULT );
		return new User( 
			$this->db->CreateNewUser( $name,$email,$passwordHash ),
			$name,
			$email,
			$passwordHash,true
		);
	}

	public function LoadUserByName( string $name ) : User
	{
		return $this->db->LoadUserByName( $name );
	}

	public function LoadUserById( int $userId ) : User
	{
		return $this->db->LoadUserById( $userId );
	}
}
?>