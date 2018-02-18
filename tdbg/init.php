<!DOCTYPE HTML>
<html>
    <head><title>Database Initialization</title></head>
    <body><h3>Initialization successful!</h3></body>

<?php
require_once '../MancalaDatabase.php';
require_once '../MancalaFactory.php';

session_start();
session_destroy();

$db = new MancalaDatabase( new ChiliSql( 'testschema','testuser','password' ) );
$factory = new MancalaFactory( $db );
$db->ClearSchema();
$db->SetupSchema();

$user0 = $factory->MakeUser( 'chili','pubes@me.com','chilipass' );
$user1 = $factory->MakeUser( 'mom','dimsum@me.com','mompass' );
?>
</html>

