<?php

try
{
    require_once 'ChiliUtil.php';
    require_once 'GameInfo.php';
    require_once 'Board.php';
    require_once 'Game.php';
    require_once 'MancalaDatabase.php';
    require_once 'SqlConnect.php';

    $test = 69;
    // TODO: need to authenticate OR session for such operations
    // verify that required post params are set
    assert( isset( $_POST['cmd'] ),'cmd not set in req to gc' );
    assert( isset( $_POST['gameId'] ),'gameId not set in req to gc' );
    assert( isset( $_POST['userId'] ),'userId not set in req to gc' );

    // connect to database and load game
    $db = new MancalaDatabase( SqlConnect() );
    $game = new Game( $db,(int)$_POST['gameId'] );

    switch( $_POST['cmd'] )
    {
    case 'move':
        // verify move pot is set
        assert( isset( $_POST['pot'] ),'pot not set in req to gc' );
        // verify it is user's turn (and user Id is valid ofc)
        $side = $game->GetSideFromId( $_POST['userId'] );
        assert( $side != null,'user is not part of game in gc' );
        assert( $game->GetActiveSide() == $side,'not user turn for move in gc' );

        // verify game is still in progress
        assert( $game->GetWinState() == WinState::InProgress,"cannot move: game over in gc" );
        
        // respond with current game state (changes only)
        $resp = [
            'board' => $game->DumpBoard(),
            'winState' => $game->GetWinState(),
            'activeSide' => $game->GetActiveSide()->GetIndex(),
            'turn' => $game->GetTurn()
        ];
        break;
    case 'query':
        // get player names
        $player0 = $db->LoadUserById( $game->GetPlayerId( Side::Top() ) );
        $player1 = $db->LoadUserById( $game->GetPlayerId( Side::Bottom() ) );

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
        throw new ChiliException( 'update unimplemented' );
    default:
        throw new ChiliException( 'bad command in gc' );
    }

    // return result
    submit_json( $resp );
}
catch( Exception $e )
{
    failout( $e->getMessage() );
}
catch( Error $e )
{
    failout( $e->getMessage() );
}
?>