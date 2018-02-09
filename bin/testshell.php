<?php

if( isset( $argv[1] ) )
{
	switch( substr( $argv[1],0,1 ) )
	{
	case "g":
		passthru( 'vendor\bin\phpunit.bat --configuration'
			.' test\phpunit.xml --group '.$argv[1] );
		break;
	default:
		passthru( 'vendor\bin\phpunit.bat --configuration'
			.' test\phpunit.xml --filter '.$argv[1] );
		break;
	}
}
else
{
	passthru( 'vendor\bin\phpunit.bat --configuration test\phpunit.xml' );
}
?>