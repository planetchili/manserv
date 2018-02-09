<?php
require_once __DIR__.'/Side.php';
require_once __DIR__.'/GameInfo.php';
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
		$passwordHash = ($password == null) ? null : password_verify( $password,PASSWORD_DEFAULT );
		$roomId = $this->db->CreateNewRoom( $name,$password );
		// TODO: reorder room ctor params and add default values
		return new Room( $roomId,$name,null,$passwordHash );
	}
}
?>