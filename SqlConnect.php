<?php
require_once 'ChiliSql.php';

function SqlConnect() : ChiliSql
{
    return new ChiliSql( 'testschema','testuser','password' );
}
?>