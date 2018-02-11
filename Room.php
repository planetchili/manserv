<?php
require_once __DIR__.'/IRoom.php';
require_once __DIR__.'/IReadonlyRoomPlayer.php';
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
	/** @var IMancalaDatabase */
	private $db;


	public function __construct( int $id,string $name,
		?int $gameId,?string $passwordHash,IMancalaDatabase $db,array $players = [] )
	{
		$this->id = $id;
		$this->name = $name;
		$this->gameId = $gameId;
		$this->passwordHash = $passwordHash;
		$this->db = $db;
		$this->players = $players;
	}

	public function AddPlayer( int $userId ) : void
	{
		$player = ($this->GetPlayerCount() == 0) ?
			new RoomPlayer( $userId,true ) :
			new RoomPlayer( $userId,false );
		$this->players[] = $player;
		
		$this->db->AddMembership( $player,$this->id );
	}

	public function RemovePlayer( int $userId ) : void
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
			$this->players[0]->MakeOwner();
		}

		$this->db->RemoveMembership( $userId,$this->id );
	}

	public function EngageGame() : int
	{
		assert( !$this->IsEngaged(),'tried to engage when game in progress' );
		assert( $this->GetPlayerCount() >= 2,'not enough players to engage game' );
		assert( $this->GetPlayer( 0 )->IsReady() && $this->GetPlayer( 1 )->IsReady(),'tried to engage when both players not ready' );

		$this->gameId = $this->db->CreateNewGame( 
			$this->GetPlayer( 0 )->GetUserId(),
			$this->GetPlayer( 1 )->GetUserId(),
			new Side( rand( 0,1 ) )
		);

		$this->db->UpdateRoom( $this );

		return $this->gameId;
	}

	public function ClearGame() : void
	{
		assert( $this->IsEngaged(),'tried to clear game when game not in progress' );		
		$this->gameId = null;
		$this->db->UpdateRoom( $this );
	}

	public function GetPlayer( int $index ) : IReadonlyRoomPlayer
	{
		return $this->players[$index];
	}

	/** @return IReadonlyRoomPlayer[] */
	public function GetPlayers() : array
	{
		return $this->players;
	}

	public function ReadyPlayerIndex( int $index ) : void
	{
		$this->players[$index]->MakeReady();
		$this->db->UpdateMembership( $this->players[$index],$this->GetId() );
	}

	public function UnreadyPlayerIndex( int $index ) : void
	{
		$this->players[$index]->ClearReady();
		$this->db->UpdateMembership( $this->players[$index],$this->GetId() );
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