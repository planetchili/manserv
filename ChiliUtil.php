<?php

// when shit hits the fan
function failout( string $msg,int $code = 0 ) : void
{
	$ret = ['status'=>['isFail'=>true,'message'=>$msg,'code'=>$code]];
	header( "Content-type: application/json; charset=utf-8" );
	echo json_encode( $ret );
	exit;
}

// return data to the browser (js)
function submit_json( $payload ) : void
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

// surround stuff in {} for toString purposes
function brace( string $s ) : string
{
	return '{'.$s.'}';
}

class ChiliException extends Exception
{

}

function check( bool $pred,string $errorString = "ChiliExcpetion: failed runtime check" ) : void
{
	if( !$pred )
	{
		throw new ChiliException( $errorString );
	}
}
?>