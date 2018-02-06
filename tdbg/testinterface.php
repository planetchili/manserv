<?php
require_once '../ChiliGuzz.php';
require_once 'DebugRender.php';

session_start();

$title = 'Mancala Tester';
$output = '';

$winStateNames = ['in progress','top wins','bottom wins','tie'];

if( isset( $_POST['cmd'] ) && $_POST['cmd'] == 'logout' )
{
	unset( $_SESSION['userData'] );
	unset( $_SESSION['gameData'] );
	unset( $_SESSION['jar'] );
	unset( $_SESSION['skipupdate'] );
}

if( isset( $_SESSION['userData'] ) )
{
	if( isset( $_SESSION['gameData'] ) )
	{
		if( isset( $_POST['pot'] ) )
		{
			$resp = GuzzPost( 'GameController',
				[
					'cmd'=>'move',
					'gameId'=>$_SESSION['gameData']['id'],
					'pot'=>(int)$_POST['pot']
				],
				$_SESSION['jar']
			);
			if( $resp['status']['isFail'] )
			{
				throw new ChiliException( $resp['status']['message'] );
			}

			$_SESSION['gameData'] = array_merge( $_SESSION['gameData'],$resp['payload'] );
		}
		else if( !$_SESSION['skipupdate'] )
		{
			$resp = GuzzPost( 'GameController',
				[
					'cmd'=>'update',
					'gameId'=>$_SESSION['gameData']['id'],
					'turn'=>$_SESSION['gameData']['turn']
				],
				$_SESSION['jar']
			);
			if( $resp['status']['isFail'] )
			{
				throw new ChiliException( $resp['status']['message'] );
			}

			if( !$resp['payload']['upToDate'] )
			{
				$_SESSION['gameData'] = array_merge( $_SESSION['gameData'],$resp['payload']['state'] );
			}
		}

		$_SESSION['skipupdate'] = false;

		$output .= '<p class="stuff" style="background-color: PaleTurquoise">You are: <strong>'
			.$_SESSION['userData']['name'].'.</strong></p>';
		$output .= '<p class="stuff" style="background-color: Salmon">'
			.'Turn <strong>'.$_SESSION['gameData']['turn'].'</strong>. It is: <strong>'
			.$_SESSION['gameData']['players'][$_SESSION['gameData']['activeSide']]['name']
			.'\'s</strong> turn.</p>';		
		$output .= '<p class="stuff" style="background-color: CornflowerBlue">Game state: <strong>'
			.$winStateNames[$_SESSION['gameData']['winState'] - 1]
			.'</strong>.</p>';

		$output .= '<br/><p>'.$_SESSION['gameData']['players'][0]['name'].'\'s side</p>';
		// display board
		$output .= DebugRender( 
			new Board( $_SESSION['gameData']['board'] ),
			new Side( $_SESSION['gameData']['ourSide'] ),
			new Side( $_SESSION['gameData']['activeSide'] )
		);
		$output .= '<p>'.$_SESSION['gameData']['players'][1]['name'].'\'s side</p><br/>';

		// logout button
		$output .= '
		<form method="POST">
			<input type="hidden" name="cmd" value="logout">
			<input type="submit" value="Logout">
		</form>';

		// refresh button
		$output .= '<br/>
		<form method="POST">
			<input type="submit" value="Refresh">
		</form>';
	}
	else
	{
		$resp = GuzzPost( 'GameController',['cmd'=>'getactive'],$_SESSION['jar'] );
		if( $resp['status']['isFail'] )
		{
			$output .= '<p>'.$resp['status']['message'].'</p>';
		}
		else
		{
			$activeGameIds = $resp['payload'];
			if( count( $activeGameIds ) == 0 )
			{
				$output .= '<h2>No active games for this user!</h2>';
			}
			else
			{
				$resp = GuzzPost( 'GameController',['cmd'=>'query','gameId'=>$activeGameIds[0]],$_SESSION['jar'] );
				if( $resp['status']['isFail'] )
				{
					throw new ChiliException( $resp['status']['message'] );
				}
				$_SESSION['gameData'] = $resp['payload'];
				$_SESSION['gameData']['id'] = $activeGameIds[0];
				$_SESSION['skipupdate'] = true;

				header( 'Location: '.$_SERVER['PHP_SELF'] );
				die;
			}				
		}
	}
}
else
{
	if( isset( $_POST['username'] ) && isset( $_POST['password'] ) )
	{
		$_SESSION['jar'] = GuzzMakeJar();
		$response = GuzzPost( 'LoginController',[
			'cmd'=>'login',
			'userName'=>$_POST['username'],
			'password'=>$_POST['password']],
			$_SESSION['jar']
		);

		if( !$response['status']['isFail'] )
		{
			$_SESSION['userData'] = $response['payload'];
		}

		header( 'Location: '.$_SERVER['PHP_SELF'] );
		die;
	}
	else
	{
		$output .= '
		<h2>Login to play Mancala!</h2>
		<form method="POST">
			<input type="text" name="username">
			<input type="text" name="password">
			<input type="submit" value="Login">
		</form>';
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?=$title ?></title>
		<style>
			p.stuff {margin: 2px; padding 3px;}
			p {margin: 0px;}
			table {border-collapse: collapse;}
			td { width: 40px; height:40px; vertical-align: middle; text-align: center;font-size:20px;font-weight:bold;}
			input[type=submit].pushy {font-size:16px;font-weight:bold;}
			table, td { border: 2px solid black;}
			table { margin: 5px; }
		</style>		
	</head>
	<body>
		<?=$output ?>
	</body>
</html>