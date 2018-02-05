<?php
require_once __DIR__.'/ChiliSql.php';

function SqlConnect() : ChiliSql
{
    return new ChiliSql( 'testschema','testuser','password' );
}
?>