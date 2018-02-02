<?php
require_once './vendor/autoload.php';

function GuzzPost( string $page,array $params = [] ) : array
{
    $client = new GuzzleHttp\Client([
        // Base URI is used with relative requests
        'base_uri' => 'http://localhost/manserv/',
        // You can set any number of default request options.
        'timeout'  => 300.0
    ]);

    $response = $client->request( 'POST',$page,
        ['form_params' => $params]
    );

    $body = (string)$response->getBody();
    $json = json_decode( $body,true ) ?? ['status'=> [
        'isFail'=>true,
        'message'=>'Guzzle response error - invalid JSON - Output: '.$body
    ]];
    return $json;
}
?>