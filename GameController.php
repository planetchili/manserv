<?php

try
{
	require_once __DIR__.'/ChiliUtil.php';
	require_once __DIR__.'/GameInfo.php';
	require_once __DIR__.'/Board.php';
	require_once __DIR__.'/Game.php';
	require_once __DIR__.'/MancalaDatabase.php';
	require_once __DIR__.'/SqlConnect.php';
	require_once __DIR__.'/Session.php';
	require_once __DIR__.'/MancalaFactory.php';

	class GameSidePair
	{
		/** @var Side */
		public $side;
		/** @var Game */
		public $game;

		public function __construct( Game $game,Side $side )
		{
			$this->side = $side;
			$this->game = $game;
		}
	}

	// function to establish verify game data/entities
	function SetupGame( MancalaFactory $factory,Session $s ) : GameSidePair
	{
		assert( isset( $_POST['gameId'] ),'gameId not set in req to gc' );
		$game = $factory->LoadGame( (int)$_POST['gameId'] );
		// verify user is in game
		$side = $game->GetSideFromId( (int)$s->GetUserId() );
		assert( $side != null,'user is not part of game in gc' );
		// return gameinfopair
		return new GameSidePair( $game,$side );
	}

	// TODO: need to differentiate between authentication lack and other errors
	// verify that required post params are set
	assert( isset( $_POST['cmd'] ),'cmd not set in req to gc' );

	// connect to database
	$db = new MancalaDatabase( SqlConnect() );
	$s = new Session( $db );
	$factory = new MancalaFactory( $db );

	if( !$s->IsLoggedIn() )
	{
		throw new ChiliException( 'Not logged in, cannot take game action' );
	}

	switch( $_POST['cmd'] )
	{
	case 'move':
		// load game and verify user is part of game, etc.
		$pair = SetupGame( $factory,$s );
		$game = $pair->game;
		$side = $pair->side;

		// verify move pot is set
		assert( isset( $_POST['pot'] ),'pot not set in req to gc' );

		// verify user has active turn
		assert( $game->GetActiveSide() == $side,'not user turn for move in gc' );

		$opponentPresent = true;
		// verify game is still in progress (check if over, then need presence check)
		if( $game->GetWinState() != WinState::InProgress )
		{
			$roomId = $db->GetActiveGamesByUserId( $s->GetUserId() )[0];
			$room = $factory->LoadRoom( $roomId );
			$op = $room->GetOtherPlayer( $s->GetUserId() );
			if( !$op->IsReady() )
			{
				$opponentPresent = false;
			}
		}
		else
		{
			// execute the move
			$game->DoMove( new Pot( (int)$_POST['pot'] ) );			
		}
		
		// respond with current game state (changes only)
		$resp = [
			'history' => [['turn' => ($game->GetTurn() - 1),'pot' => (int)$_POST['pot']]],
			'state' => [
				'board' => $game->DumpBoard(),
				'winState' => $game->GetWinState(),
				'activeSide' => $game->GetActiveSide()->GetIndex(),
				'turn' => $game->GetTurn(),
				'opponentPresent' => $opponentPresent
			]
		];
		break;
	case 'query':
		// load game and verify user is part of game, etc.
		$pair = SetupGame( $factory,$s );
		$game = $pair->game;
		$side = $pair->side;

		// get player names
		$player0 = $db->LoadUserById( $game->GetPlayerId( Side::Top() ) );
		$player1 = $db->LoadUserById( $game->GetPlayerId( Side::Bottom() ) );
		
		// make sure player is in game
		assert( $player0->GetId() === (int)$s->GetUserId() ||
				$player1->GetId() === (int)$s->GetUserId(),
				'user does not belong to this game'
		);

		$opponentPresent = true;
		if( $game->GetWinState() != WinState::InProgress )
		{
			$room = $factory->LoadRoom( $_POST['roomId'] );
			$op = $room->GetOtherPlayer( $s->GetUserId() );
			if( !$op->IsReady() )
			{
				$opponentPresent = false;
			}
		}

		// respond with full game info
		$resp = [
			'board' => $game->DumpBoard(),
			'winState' => $game->GetWinState(),
			'activeSide' => $game->GetActiveSide()->GetIndex(),
			'turn' => $game->GetTurn(),
			'ourSide' => $side->GetIndex(),
			'history' => $db->LoadNewMoves( $game->GetGameId(),0 ),
			'players' =>
			[
				['name'=>$player0->GetName(),'id'=>$player0->GetId()],
				['name'=>$player1->GetName(),'id'=>$player1->GetId()]
			],
			'opponentPresent' => $opponentPresent
		];
		break;
	case 'update':
		// load game and verify user is part of game, etc.
		$pair = SetupGame( $factory,$s );
		$game = $pair->game;
		$side = $pair->side;
		
		$opponentPresent = true;
		if( $game->GetWinState() != WinState::InProgress )
		{
			$room = $factory->LoadRoom( $_POST['roomId'] );
			if( $room->GetPlayerCount() === 2 )
			{
				$op = $room->GetOtherPlayer( $s->GetUserId() );
				$opponentPresent = $op->IsReady();
			}
			else
			{
				$opponentPresent = false;
			}
		}

		assert( isset( $_POST['turn'] ),'turn not set in update req to gc' );
		assert( $_POST['turn'] <= $game->GetTurn(),'bad turn; client ahead of server' );
		$history = $db->LoadNewMoves( $game->GetGameId(),(int)$_POST['turn'] );
		if( count( $history ) > 0 || $_POST['winState'] != $game->GetWinState() )
		{
			$resp = [
				'upToDate' => false,
				'history' => $history,
				'state' => [
					'board' => $game->DumpBoard(),
					'winState' => $game->GetWinState(),
					'activeSide' => $game->GetActiveSide()->GetIndex(),
					'turn' => $game->GetTurn(),
					'opponentPresent' => $opponentPresent
				]
			];
		}
		else
		{
			$resp = ['upToDate' => true];
		}
		break;
	case 'getactive':
		$resp = $db->GetActiveGamesByUserId( $s->GetUserId() );
		break;
	default:
		throw new ChiliException( 'bad command in gc' );
	}

	// return result
	submit_json( $resp );
}
catch( Exception $e )
{
	failout( strip_tags( $e ) );
}
catch( Error $e )
{
	failout( strip_tags( $e ) );
}
?>