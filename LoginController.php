<?php

try
{
	require_once 'Session.php';
	require_once 'ChiliUtil.php';
	require_once 'MancalaDatabase.php';
	require_once 'SqlConnect.php';

	$db = new MancalaDatabase( SqlConnect() );
	$s = new Session( $db );

	// need a cmd at least
	assert( isset( $_POST['cmd'] ),'cmd not set in req to testsc' );

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
		// respond with user id
		$resp = $s->GetUser()->ToArray();
		break;
	default:
		throw new ChiliException( 'bad command in testsc' );
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