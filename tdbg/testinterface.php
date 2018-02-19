<?php
require_once '../ChiliGuzz.php';
require_once 'DebugRender.php';


session_start();

$title = 'Mancala Tester';
$output = '';

function GetPlayerName( array $gameData,Side $side ) : string
{
	return $gameData['players'][$side->GetIndex()]['name'];
}

if( isset( $_POST['cmd'] ) && $_POST['cmd'] == 'logout' )
{
	unset( $_SESSION['userData'] );
	unset( $_SESSION['roomData'] );
	unset( $_SESSION['gameData'] );
	unset( $_SESSION['jar'] );
	unset( $_SESSION['skipUpdate'] );
	unset( $_SESSION['initialized'] );
}

if( isset( $_SESSION['userData'] ) )
{
	// player is sessioned in a game already
	if( isset( $_SESSION['gameData'] ) )
	{		
		$winStateNames = [
			'in progress',
			GetPlayerName( $_SESSION['gameData'],Side::Top() ).' wins',
			GetPlayerName( $_SESSION['gameData'],Side::Bottom() ).' wins',
			'tie'
		];

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

			$_SESSION['gameData'] = array_merge( $_SESSION['gameData'],$resp['payload']['state'] );
			$_SESSION['gameData']['history'] = array_merge(
				$_SESSION['gameData']['history'],
				$resp['payload']['history']
			);
		}
		else if( !$_SESSION['skipUpdate'] )
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
				$_SESSION['gameData']['history'] = array_merge(
					$_SESSION['gameData']['history'],
					$resp['payload']['history']
				);
			}
		}
		
		$_SESSION['skipUpdate'] = false;

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

		$output .= '<br/><table class="history"><caption>Move History</caption>'
			.'<thead><th>Turn #</th><th>Player</th><th>Pot</th></thead><tbody>';
		foreach( $_SESSION['gameData']['history'] as $move )
		{
			$output .= '<tr><td>'.($move['turn'] + 1)
				.'</td><td>'.GetPlayerName( 
					$_SESSION['gameData'],
					(new Pot( (int)$move['pot'] ))->GetSide()
				)
				.'</td><td>'.$move['pot'].'</td></tr>';
		}
		$output .= '</tbody></table>';

		if( $_SESSION['gameData']['ourSide'] != $_SESSION['gameData']['activeSide'] &&
			$_SESSION['gameData']['winState'] == 1 )
		{
			header( 'Refresh: 0' );
		}
	}
	// player is sessioned in a room already
	else if( isset( $_SESSION['roomData'] ) )
	{
		// check for ready command / leave room command
		// otherwise update
		if( isset( $_POST['cmd'] ) )
		{
			if( $_POST['cmd'] == 'ready' )
			{
				$resp = GuzzPost( 'RoomController',
					[
						'cmd'=>'ready',
						'roomId'=>$_SESSION['roomData']['id']
					],
					$_SESSION['jar']
				);			
				if( $resp['status']['isFail'] )
				{
					$output .= '<p>'.$resp['status']['message'].'</p>';
				}
				else
				{
					header( 'Location: '.$_SERVER['PHP_SELF'] );
					die;					
				}
			}
			else if( $_POST['cmd'] == 'unready' )
			{
				$resp = GuzzPost( 'RoomController',
					[
						'cmd'=>'unready',
						'roomId'=>$_SESSION['roomData']['id']
					],
					$_SESSION['jar']
				);			
				if( $resp['status']['isFail'] )
				{
					$output .= '<p>'.$resp['status']['message'].'</p>';
				}
				else
				{
					header( 'Location: '.$_SERVER['PHP_SELF'] );
					die;					
				}
			}
			else if( $_POST['cmd'] == 'leave' )
			{
				$resp = GuzzPost( 'RoomController',
					[
						'cmd'=>'leave',
						'roomId'=>$_SESSION['roomData']['id']
					],
					$_SESSION['jar']
				);			
				if( $resp['status']['isFail'] )
				{
					$output .= '<p>'.$resp['status']['message'].'</p>';
				}
				else
				{
					unset( $_SESSION['roomData'] );
					header( 'Location: '.$_SERVER['PHP_SELF'] );
					die;					
				}
			}
			else
			{
				$output .= '<p>Bad cmd: '.$_POST['cmd'].'</p>';
			}
		}
		else
		{
			$resp = GuzzPost( 'RoomController',
				[
					'cmd'=>'update',
					'roomId'=>$_SESSION['roomData']['id']
				],
				$_SESSION['jar']
			);			
			if( $resp['status']['isFail'] )
			{
				$output .= '<p>'.$resp['status']['message'].'</p>';
			}
			else
			{
				$_SESSION['roomData'] = $resp['payload'];
				if( $_SESSION['roomData']['gameId'] != null )
				{
					$resp = GuzzPost( 'GameController',
						[
							'cmd'=>'query',
							'gameId'=>$_SESSION['roomData']['gameId']
						],
						$_SESSION['jar']
					);			
					if( $resp['status']['isFail'] )
					{
						$output .= '<p>'.$resp['status']['message'].'</p>';
					}
					else
					{
						$_SESSION['gameData'] = $resp['payload'];
						$_SESSION['gameData']['id'] = (int)$_SESSION['roomData'];
						$_SESSION['skipUpdate'] = true;
						header( 'Location: '.$_SERVER['PHP_SELF'] );
						die;
					}
				}

				$output .= '<h2>'.$_SESSION['roomData']['name'].'</h2>';
				foreach( $_SESSION['roomData']['players'] as $player )
				{
					$output .= '<p>'.$player['name'].($player['isOwner']?'&#x1f528':'').($player['isReady']?'&#x2714;':'').'</p>';
				}
				$output .= '
				<form method="POST">
					<input type="hidden" name="cmd" value="ready">
					<input type="submit" value="Ready">
				</form><br/>';
				$output .= '
				<form method="POST">
					<input type="hidden" name="cmd" value="unready">
					<input type="submit" value="Unready">
				</form><br/>';
				$output .= '
				<form method="POST">
					<input type="hidden" name="cmd" value="leave">
					<input type="submit" value="Leave">
				</form><br/>';
			}
		}
	}
	else
	{
		if( !isset( $_SESSION['initialized'] ) )
		{
			$resp = GuzzPost( 'RoomController',
				[
					'cmd'=>'check'
				],
				$_SESSION['jar']
			);			
			if( $resp['status']['isFail'] )
			{
				$output .= '<p>'.$resp['status']['message'].'</p>';
			}
			else if( $resp['payload'] != [] )
			{
				$_SESSION['roomData'] = $resp['payload'];
				if( $_SESSION['roomData']['gameId'] != null )
				{
					$resp = GuzzPost( 'GameController',['cmd'=>'query','gameId'=>$_SESSION['roomData']['gameId']],$_SESSION['jar'] );
					if( $resp['status']['isFail'] )
					{
						throw new ChiliException( $resp['status']['message'] );
					}
					$_SESSION['gameData'] = $resp['payload'];
					$_SESSION['gameData']['id'] = (int)$_SESSION['roomData']['gameId'];
					$_SESSION['skipUpdate'] = true;
				}
			}

			$_SESSION['initialized'] = true;
			header( 'Location: '.$_SERVER['PHP_SELF'] );
			die;
		}
		else // logged in and initialized, no room
		{
			if( isset( $_POST['cmd'] ) )
			{
				if( $_POST['cmd'] == 'join' )
				{
					$resp = GuzzPost( 'RoomController',
						[
							'cmd'=>'join',
							'roomId'=>$_POST['roomId'],
							'password'=>$_POST['password']
						],
						$_SESSION['jar']
					);			
					if( $resp['status']['isFail'] )
					{
						$output .= '<p>'.$resp['status']['message'].'</p>';
					}
					$_SESSION['roomData'] = $resp['payload'];
	
					header( 'Location: '.$_SERVER['PHP_SELF'] );
					die;

				}
				else if( $_POST['cmd'] == 'create' )
				{
					$resp = GuzzPost( 'RoomController',
						[
							'cmd'=>'create',
							'name'=>$_POST['name'],
							'password'=>$_POST['password']
						],
						$_SESSION['jar']
					);			
					if( $resp['status']['isFail'] )
					{
						$output .= '<p>'.$resp['status']['message'].'</p>';
					}
					else
					{
						$_SESSION['roomData'] = $resp['payload'];	
						header( 'Location: '.$_SERVER['PHP_SELF'] );
						die;
					}
				}
				else
				{
					$output .= '<p>Bad cmd: '.$_POST['cmd'].'</p>';
				}
			}
			else
			{
				// otherwise just list
				// output create form
				$output .= '
				<form method="POST">
					<h3>Create Room</h3>
					<input type="hidden" name="cmd" value="create">
					<input type="text" name="name">
					<input type="text" name="password">
					<input type="submit" value="Create Room">
				</form><br/>';

				// output refresh form
				$output .= '
				<form method="POST">
					<input type="submit" value="Refresh">
				</form><br/>';

				// output room list/join forms
				$resp = GuzzPost( 'RoomController',
					[
						'cmd'=>'list'
					],
					$_SESSION['jar']
				);			
				if( $resp['status']['isFail'] )
				{
					$output .= '<p>'.$resp['status']['message'].'</p>';
				}

				$output .= '<h2>Rooms</h2>';
				$rooms = $resp['payload'];
				foreach( $rooms as $room )
				{
					// TODO: add player roles
					$output .= '
					<form method="POST">
						<h3>'.$room['name'].($room['locked'] ? '&#x1f512;' : '').'</h3>';
					foreach( $room['players'] as $player_name )
					{
						$output .= '<p>'.$player_name.'</p>';
					}
					$output .=	
					   '<input type="hidden" name="cmd" value="join">
						<input type="hidden" name="roomId" value="'.$room['id'].'">
						<input type="text" name="password">
						<input type="submit" value="Join">
					</form><br/>';
				}				
			}
		}
	}
}
else
{
	if( isset( $_POST['cmd'] ) && $_POST['cmd'] == 'login' && isset( $_POST['username'] ) && isset( $_POST['password'] ) )
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
			<input type="hidden" name="cmd" value="login">
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
			p { margin: 0px;}
			p.stuff { margin: 2px; padding 3px;}
			input[type=submit].pushy { font-size:16px;font-weight:bold;}

			table { border-collapse: collapse; margin: 5px;}

			table.board td { width: 40px; height:40px; vertical-align: middle; text-align: center;font-size:20px;font-weight:bold;}
			table.board td, th { border: 2px solid black;}
			
			table.history td { text-align: center;font-size:14px;}
			table.history td, th { border: 1px solid black;padding: 3px;}
		</style>		
	</head>
	<body>
		<?=$output ?>
	</body>
</html>