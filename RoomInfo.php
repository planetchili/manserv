<?php
require_once __DIR__.'/RoomPlayer.php';

class RoomInfo
{
	/** @var int */
	protected $id;
	/** @var string */
	protected $name;	
	/** @var int */
	protected $gameId;
	/** @var string */
	protected $passwordHash;
	/** @var RoomPlayer[] */
	protected $players;

	public function __construct( int $id,string $name,
		?int $gameId,?string $passwordHash,array $players = [] )
	{
		$this->id = $id;
		$this->name = $name;
		$this->gameId = $gameId;
		$this->passwordHash = $passwordHash;
		$this->players = $players;
	}

	public function GetPlayer( int $index ) : RoomPlayer
	{
		return $this->players[$index];
	}

	/** @return RoomPlayer[] */
	public function GetPlayers() : array
	{
		return $this->players;
	}

	public function GetPlayerCount() : int
	{
		return count( $this->players );
	}

	public function GetId() : int
	{
		return $this->id;
	}

	public function GetName() : string
	{
		return $this->name;
	}

	public function IsEngaged() : bool
	{
		return $this->gameId != null;
	}

	public function IsLocked() : bool
	{
		return $this->passwordHash != null;
	}

	public function VerifyPassword( string $password ) : bool
	{
		return password_verify( $password,$this->passwordHash );
	}

	public function GetGameId() : int
	{
		assert( $this->gameId != null,'get gameid called when room not engaged' );
		return $this->gameId;
	}
}
?>