<!DOCTYPE HTML>
<html>
    <head><title>Database Initialization</title></head>
    <body><h3>Initialization successful!</h3></body>

<?php
require_once '../MancalaDatabase.php';

$db = new MancalaDatabase( new ChiliSql( 'testschema','testuser','password' ) );
$db->ClearSchema();
$db->SetupSchema();

$db->AddUser( new User( -1,'chili','pubes@me.com','pubes' ) );
$db->AddUser( new User( -1,'mom','dimsum@me.com','dimsum' ) );

$gid = $db->CreateNewGame(
    $db->LoadUserByName( 'chili' )->GetId(),
    $db->LoadUserByName( 'mom' )->GetId(),
    Side::Top()
);

$db->UpdateBoard( Board::MakeFresh(),$gid );
?>
</html>

