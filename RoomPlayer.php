<?php
require_once __DIR__.'/User.php';
require_once __DIR__.'/IReadonlyRoomPlayer.php';
require_once __DIR__.'/IMancalaDatabase.php';

class RoomPlayer implements IReadonlyRoomPlayer
{
	/** @var int */
	protected $userId;
	/** @var bool */
	protected $isOwner;
	/** @var bool */
	protected $isReady;

	public function __construct( int $userId,bool $isOwner,bool $isReady = false )
	{
		$this->userId = $userId;
		$this->isOwner = $isOwner;
		$this->isReady = $isReady;
	}

	public function GetUser( IMancalaDatabase $db ) : User
	{
		return $db->LoadUserById( $this->userId );
	}

	public function GetUserId() : int
	{
		return $this->userId;
	}

	public function IsOwner() : bool
	{
		return $this->isOwner;
	}

	public function IsReady() : bool
	{
		return $this->isReady;
	}

	public function MakeReady() : void
	{
		$this->isReady = true;
	}

	public function ClearReady() : void
	{
		$this->isReady = false;
	}

	public function MakeOwner() : void
	{
		$this->isOwner = true;
	}

	public function ToAssociative( IMancalaDatabase $db ) : array
	{
		$user = $this->GetUser( $db );
		return [
			'name'=>$user->GetName(),
			'id'=>$user->GetId(),
			'isOwner'=>$this->isOwner,
			'isReady'=>$this->isReady
		];
	}
}
?>