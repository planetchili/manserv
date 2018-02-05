<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/ChiliUtil.php';

function GuzzMakeJar() : GuzzleHttp\Cookie\CookieJar
{
    return new GuzzleHttp\Cookie\CookieJar();
 }

function GuzzPost( string $page,array $params = [],?GuzzleHttp\Cookie\CookieJar $jar = null ) : array
{
    $cparams = [
        // Base URI is used with relative requests
        'base_uri' => 'http://localhost/manserv/',
        // You can set any number of default request options.
        'timeout'  => 300.0,
    ];

    if( count( $params ) > 0 )
    {
        $cparams['form_params'] = $params;
    }

    if( $jar != null )
    {
        $cparams['cookies'] = $jar;
    }

    $client = new GuzzleHttp\Client( $cparams );

    $response = $client->request( 'POST',$page,
        ['form_params' => $params]
    );

    $body = (string)$response->getBody();
    $json = json_decode( $body,true );

    if( $json == null )
    {
        throw new ChiliException( 'Guzzle response error - invalid JSON - Response from server: '.strip_tags( $body ) );
    }

    return $json;
}
?>