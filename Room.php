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
			$this->db->UpdateMembership( $this->players[0],$this->id );
		}

		$this->db->RemoveMembership( $userId,$this->id );
	}

	public function EngageGame() : int
	{
		assert( !$this->IsEngaged(),'tried to engage when game in progress' );
		assert( $this->GetPlayerCount() >= 2,'not enough players to engage game' );
		$players = $this->GetPlayers();
		assert( $players[0]->IsReady() && $players[1]->IsReady(),'tried to engage when both players not ready' );

		$this->gameId = $this->db->CreateNewGame( 
			$players[0]->GetUserId(),
			$players[1]->GetUserId(),
			new Side( rand( 0,1 ) )
		);

		$this->db->UpdateRoom( $this );

		return $this->gameId;
	}

	public function DisengageGame() : void
	{
		assert( $this->IsEngaged() );
		assert( $this->CountEngagedPlayers() == 0 );

		$this->gameId = null;
		$this->db->UpdateRoom( $this );
	}

	public function CountEngagedPlayers() : int
	{
		$nEngaged = 0;
		foreach( $this->GetPlayers() as $player )
		{
			if( $player->IsReady() )
			{
				$nEngaged++;
			}
		}
		return $nEngaged;
	}

	public function ClearGame() : void
	{
		assert( $this->IsEngaged(),'tried to clear game when game not in progress' );		
		$this->gameId = null;
		$this->db->UpdateRoom( $this );
	}

	public function GetPlayer( int $userId ) : IReadonlyRoomPlayer
	{
		$target = false;
		foreach( $this->players as $player )
		{
			if( $player->GetUserId() === $userId )
			{
				$target = $player;
				break;
			}
		}

		if( !$target )
		{
			throw new ChiliException( 'getplayer: userid does not exist in room' );
		}
		
		return $target;
	}

	public function GetOtherPlayer( int $userId ) : IReadonlyRoomPlayer
	{
		$target = false;
		foreach( $this->players as $player )
		{
			if( $player->GetUserId() !== $userId )
			{
				$target = $player;
				break;
			}
		}

		// TODO: make sure that $userId actually exists first?
		
		return $target;
	}

	/** @return IReadonlyRoomPlayer[] */
	public function GetPlayers() : array
	{
		return $this->players;
	}

	public function ReadyPlayer( int $userId ) : void
	{
		$player = $this->GetPlayer( $userId );
		$player->MakeReady();
		$this->db->UpdateMembership( $player,$this->GetId() );
	}

	public function UnreadyPlayer( int $userId ) : void
	{
		$player = $this->GetPlayer( $userId );
		$player->ClearReady();
		$this->db->UpdateMembership( $player,$this->GetId() );
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

	public function GetGameId() : ?int
	{
		return $this->gameId;
	}

	public function ToAssociative() : array
	{
		return [
			'id'=>$this->id,
			'name'=>$this->name,
			'gameId'=>$this->gameId,
			'players'=>array_map( 
				function( IReadonlyRoomPlayer $player ) 
					{ return $player->ToAssociative( $this->db );},
				$this->players
			)
		];
	}
}
?>