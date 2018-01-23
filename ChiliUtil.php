<?php

// when shit hits the fan
function failout( string $msg,any $payload = null ) : void
{
	$ret = ['payload'=>$payload,'status'=>['isFail'=>true,'message'=>$msg]];
	header( "Content-type: application/json; charset=utf-8" );
	echo json_encode( $ret );
	exit;
}

// return data to the browser (js)
function submit_json( any $payload ) : void
{	
	$ret = array( 'status'=>['isFail'=>false],'payload'=>$payload );
	header( "Content-type: application/json; charset=utf-8" );
	echo json_encode( $ret );
	exit;
}

// test if an int is within a give range, inclusive
function in_range( int $val,int $min,int $max ) : bool
{
    return ($val >= $min) && ($val <= $max);
}

?>