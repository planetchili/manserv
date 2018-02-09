<?php
require_once __DIR__.'/User.php';

class RoomPlayer
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

	public function GetUser( MancalaDatabase $db ) : User
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
}
?>