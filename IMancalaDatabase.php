<?php
require_once __DIR__.'/Side.php';
require_once __DIR__.'/RoomPlayer.php';
require_once __DIR__.'/Board.php';
require_once __DIR__.'/User.php';

interface IMancalaDatabase
{
    public function SetupSchema() : void;

    public function ClearSchema() : void;

    public function LoadGameInfo( int $gameId ) : GameInfo;

    public function UpdateGame( GameInfo $gameInfo ) : void;

    public function LoadBoard( int $gameId ) : Board;

    public function UpdateBoard( Board $board,int $gameId ) : void;

    public function CreateNewGame( int $player0Id,int $player1Id,Side $startSide ) : int;

    public function ClearBoard( int $gameId ) : void;

    public function AddHistoryMove( int $gameId,int $turn,Pot $move ) : void;

    public function CreateNewUser( string $name,string $email,string $passwordHash ) : int;

    public function LoadUserById( int $userId ) : User;

    public function LoadUserByName( string $name ) : User;

    public function GetActiveGamesByUserId( int $userId ) : array;

    public function LoadNewMoves( int $gameId,int $fromTurn ) : array;

    public function AddMembership( RoomPlayer $player,int $roomId ) : void;

    public function CreateNewRoom( string $name,?string $password ) : int;
}
?>