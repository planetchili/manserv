<!DOCTYPE HTML>
<html>
    <head><title>Database Initialization</title></head>
    <body><h3>Initialization successful!</h3></body>
    
<?php
require_once '../MancalaDatabase.php';

$conn = new ChiliSql( 'testschema','testuser','password' );
$db = new MancalaDatabase( $conn );
$db->ClearSchema();
$db->SetupSchema();
?>
</html>

