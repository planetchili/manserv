<?php

try
{
	require_once 'ChiliUtil.php';
	require_once 'GameInfo.php';
	require_once 'Board.php';
	require_once 'Game.php';
	require_once 'MancalaDatabase.php';
	require_once 'SqlConnect.php';
	require_once 'Session.php';

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
	function SetupGame( MancalaDatabase $db,Session $s ) : GameSidePair
	{
		assert( isset( $_POST['gameId'] ),'gameId not set in req to gc' );
		$game = new Game( $db,(int)$_POST['gameId'] );
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

	if( !$s->IsLoggedIn() )
	{
		throw new ChiliException( 'Not logged in, cannot take game action' );
	}

	switch( $_POST['cmd'] )
	{
	case 'move':
		// load game and verify user is part of game, etc.
		$pair = SetupGame( $db,$s );
		$game = $pair->game;
		$side = $pair->side;

		// verify move pot is set
		assert( isset( $_POST['pot'] ),'pot not set in req to gc' );

		// verify user has active turn
		assert( $game->GetActiveSide() == $side,'not user turn for move in gc' );

		// verify game is still in progress
		assert( $game->GetWinState() == WinState::InProgress,"cannot move: game over in gc" );

		// execute the move
		$game->DoMove( new Pot( (int)$_POST['pot'] ) );
		
		// respond with current game state (changes only)
		$resp = [
			'board' => $game->DumpBoard(),
			'winState' => $game->GetWinState(),
			'activeSide' => $game->GetActiveSide()->GetIndex(),
			'turn' => $game->GetTurn()
		];
		break;
	case 'query':
		// load game and verify user is part of game, etc.
		$pair = SetupGame( $db,$s );
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

		// respond with full game info
		$resp = [
			'board' => $game->DumpBoard(),
			'winState' => $game->GetWinState(),
			'activeSide' => $game->GetActiveSide()->GetIndex(),
			'turn' => $game->GetTurn(),
			'players' =>
			[
				['name'=>$player0->GetName(),'id'=>$player0->GetId()],
				['name'=>$player1->GetName(),'id'=>$player1->GetId()]
			]
		];
		break;
	case 'update':
		// load game and verify user is part of game, etc.
		$pair = SetupGame( $db,$s );
		$game = $pair->game;
		$side = $pair->side;

		assert( isset( $_POST['turn'] ),'turn not set in update req to gc' );
		assert( $_POST['turn'] <= $game->GetTurn(),'bad turn; client ahead of server' );
		$moves = $db->LoadNewMoves( $game->GetGameId(),(int)$_POST['turn'] );
		if( count( $moves ) > 0 )
		{
			$resp = [
				'upToDate' => false,
				'moves' => $moves,
				'state' => [
					'board' => $game->DumpBoard(),
					'winState' => $game->GetWinState(),
					'activeSide' => $game->GetActiveSide()->GetIndex(),
					'turn' => $game->GetTurn()
				]
			];
		}
		else
		{
			$resp = ['upToDate' => true];
		}
		break;
	case 'getactive':
		throw new ChiliException( 'getactive notimple' );
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