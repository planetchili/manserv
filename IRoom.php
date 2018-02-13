<?php
require_once __DIR__.'/IReadonlyRoomPlayer.php';

interface IRoom
{
	public function GetPlayer( int $userId ) : IReadonlyRoomPlayer;

	public function AddPlayer( int $userId ) : void;

	public function RemovePlayer( int $userId ) : void;

	/** @return IReadonlyRoomPlayer[] */
	public function GetPlayers() : array;

	public function GetPlayerCount() : int;

	public function GetId() : int;

	public function GetName() : string;
	
	public function EngageGame() : int;

	public function IsEngaged() : bool;

	public function IsLocked() : bool;

	public function VerifyPassword( string $password ) : bool;

	public function GetGameId() : int;

	public function ReadyPlayer( int $userId ) : void;

	public function UnreadyPlayer( int $userId ) : void;

	public function ToAssociative() : array;
}
?>