<?php
require_once __DIR__.'/User.php';
require_once __DIR__.'/IMancalaDatabase.php';

interface IReadonlyRoomPlayer
{
	public function GetUser( IMancalaDatabase $db ) : User;

	public function GetUserId() : int;

	public function IsOwner() : bool;

	public function IsReady() : bool;
}
?>