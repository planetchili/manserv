<?php

try
{
	require_once __DIR__.'/Session.php';
	require_once __DIR__.'/ChiliUtil.php';
	require_once __DIR__.'/MancalaDatabase.php';
	require_once __DIR__.'/SqlConnect.php';

	$db = new MancalaDatabase( SqlConnect() );
	$s = new Session( $db );

	// need a cmd at least
	assert( isset( $_POST['cmd'] ),'cmd not set in req to loginctrl' );

	switch( $_POST['cmd'] )
	{
	case 'login':		
		$s->Login( $_POST['userName'],$_POST['password'] );
		// respond with user id
		$resp = $s->GetUser()->ToArray();
		break;
	case 'logout':
		$s->Logout();
		$resp = [];
		break;
	case 'getuser':
		// respond with user info
		$resp = $s->GetUser()->ToArray();
		break;
	default:
		throw new ChiliException( 'bad command in loginctrl' );
	}

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