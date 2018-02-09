<?php
require_once __DIR__.'/RoomPlayer.php';

interface IRoom
{
	public function GetPlayer( int $index ) : RoomPlayer;

	/** @return RoomPlayer[] */
	public function GetPlayers() : array;

	public function GetPlayerCount() : int;

	public function GetId() : int;

	public function GetName() : string;

	public function IsEngaged() : bool;

	public function IsLocked() : bool;

	public function VerifyPassword( string $password ) : bool;

	public function GetGameId() : int;
}
?>