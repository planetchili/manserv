<?php

try
{
	require_once __DIR__.'/Session.php';
	require_once __DIR__.'/ChiliUtil.php';
	require_once __DIR__.'/MancalaDatabase.php';
	require_once __DIR__.'/MancalaFactory.php';
	require_once __DIR__.'/SqlConnect.php';
	require_once __DIR__.'/Room.php';

	$db = new MancalaDatabase( SqlConnect() );
	$f = new MancalaFactory( $db );
	$s = new Session( $db );

	// need a cmd at least
	assert( isset( $_POST['cmd'] ),'cmd not set in req to rmctrl' );

	if( !$s->IsLoggedIn() )
	{
		throw new ChiliException( 'Not logged in, cannot take room action' );
	}

	// TODO: implement delta room update (right now just reload all rooms periodically)
	switch( $_POST['cmd'] )
	{
	// returns newly-created room
	// TODO: should make sure not already in room before doing this
	case 'create':
		$password = ($_POST['password'] == '' ) ? null : $_POST['password'];
		$resp = $f->MakeRoom( $_POST['name'],$password )->ToAssociative();
		break;
	case 'join':
		$room = $f->LoadRoom( (int)$_POST['roomId'] );
		if( $room->IsLocked() && !$room->VerifyPassword( $_POST['password'] ) )
		{
			throw new ChiliException( 'Wrong room password!' );
		}
		// TODO: validate room exists / not full etc.
		$room->AddPlayer( $s->GetUserId() );
		$resp = $room->ToAssociative();
		break;
	case 'update':
		$resp = $f->LoadRoom( (int)$_POST['roomId'] )
			->ToAssociative();
		break;
	case 'leave':		
		// TODO: what if leave when game already starts?
		// maybe return room list when leave table??
		$room = $f->LoadRoom( (int)$_POST['roomId'] );
		$room->RemovePlayer( $s->GetUserId() );
		$resp = [];
		break;
	case 'list':
		$resp = $db->ListRooms();
		break;
	case 'ready':
		$room = $f->LoadRoom( (int)$_POST['roomId'] );
		$room->ReadyPlayer( $s->GetUserId() );
		// start game if ready?
		$resp = [];
		break;
	default:
		throw new ChiliException( 'bad command in rmctrl' );
	}

	// TODO: add longpoll status update command?

	// return result
	submit_json( $resp );
}
catch( Exception $e )
{
	failout( $e );
}
catch( Error $e )
{
	failout( $e );
}
?>