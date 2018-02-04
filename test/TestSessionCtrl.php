<?php

try
{
	require_once '../Session.php';
	require_once '../ChiliUtil.php';
	require_once '../MancalaDatabase.php';

	$db = new MancalaDatabase( new ChiliSql( 'testschema','testuser','password' ) );
	$session = new Session( $db );

	// need a cmd at least
	assert( isset( $_POST['cmd'] ),'cmd not set in req to testsc' );

	switch( $_POST['cmd'] )
	{
	case 'login':		
		$session->Login( $_POST['userName'],$_POST['password'] );

		// respond with user id
		$resp = [
			'userId' => $session->GetUserId()
		];
		break;
	case 'logout':
		$session->Logout();
		$resp = [];
		break;
	case 'getuserid':
		// respond with user id
		$resp = [
			'userId' => $session->GetUserId()
		];
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