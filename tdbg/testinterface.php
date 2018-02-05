<?php

require_once '../Session.php';
require_once '../ChiliSql.php';
require_once '../MancalaDatabase.php';
require_once '../ChiliGuzz.php';

function Linker( int $side,int $offset ) : string
{
	$pot = Pot::FromSideOffset( $side,$offset );
	throw new ChiliException( 'linker not impl' );
}

$output = '';

$conn = new ChiliSql( 'testschema','testuser','password' );
$db = new MancalaDatabase( $conn );
$s = new Session( $db );

if( $s->IsLoggedIn() )
{
	$games = $db->GetActiveGamesByUserId( $s->GetUserId() );
	if( count( $games ) == 0 )
	{
		$output .= '<h2>No active games for this user!</h2>';
	}
	else
	{
		$game = $games[0];
		
	}
}
else
{
	// display login form	
}

?>