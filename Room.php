<?php
require_once __DIR__.'/IRoom.php';
require_once __DIR__.'/IMancalaDatabase.php';

class Room implements IRoom
{
	/** @var int */
	private $id;
	/** @var string */
	private $name;	
	/** @var int */
	private $gameId;
	/** @var string */
	private $passwordHash;
	/** @var RoomPlayer[] */
	private $players;

	public function AddPlayer( int $userId,MancalaDatabase $db ) : void
	{
		$player = ($this->GetPlayerCount() == 0) ?
			new RoomPlayer( $userId,true ) :
			new RoomPlayer( $userId,false );
		$this->players[] = $player;
		
		$db->AddMembership( $player,$this->id );
	}

	public function RemovePlayer( int $userId,MancalaDatabase $db ) : void
	{
		$owner_removed = false;
		$this->players = array_values( array_filter( $this->players,
		function( RoomPlayer $player ) use ( $userId,&$owner_removed )
		{
			if( $userId == $player->GetUserId() )
			{
				$owner_removed = $player->IsOwner();
				return false;
			}
			return true;
		} ) );

		if( $owner_removed && $this->GetPlayerCount() > 0 )
		{
			$this->players[0]->MakeOwner( $db );
		}

		$db->RemoveMembership( $userId,$this->id );
	}

	public function EngageGame( MancalaDatabase $db ) : int
	{
		assert( !$this->IsEngaged(),'tried to engage when game in progress' );
		assert( $this->GetPlayerCount() >= 2,'not enough players to engage game' );
		assert( $this->GetPlayer( 0 )->IsReady() && $this->GetPlayer( 1 )->IsReady(),'tried to engage when both players not ready' );

		$this->gameId = $db->CreateNewGame( 
			$this->GetPlayer( 0 )->GetUserId(),
			$this->GetPlayer( 1 )->GetUserId(),
			new Side( rand( 0,1 ) )
		);

		$db->UpdateRoom( $this );

		return $this->gameId;
	}

	public function ClearGame( IMancalaDatabase $db ) : void
	{
		assert( $this->IsEngaged(),'tried to clear game when game not in progress' );		
		$this->gameId = null;
		$db->UpdateRoom( $this );
	}

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