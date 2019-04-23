<?php
require_once (__DIR__ .'/Controller.php');

session_start();
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));
}
$token = $_SESSION['token'];


$functionName = filter_input(INPUT_GET, 'functionName', FILTER_SANITIZE_STRING);


if(empty($functionName)) {
    $functionName = filter_input(INPUT_POST, 'functionName', FILTER_SANITIZE_STRING);
}
$request = filter_input_array(INPUT_GET);

if(empty($request)) {
    $request = filter_input_array(INPUT_POST);
}

header ( 'Content-Type: application/json; charset=UTF-8' );
try{
    if ( empty ($functionName ) || !method_exists('Controller', $functionName) ) {
            throw new Exception("method '$functionName' does not exist in class in class 'Controller'");
    }
    unset($request['functionName']);
    $response = Controller::$functionName((object)$request);
    
    print( json_encode ($response) );
} catch (AppException $e) {
    die(json_encode ( array (
        'error' => $e->getMessage()
    )));
} catch (Exception $e) {
    $servsrProtocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL' );
    
    $serverProtocol = $servsrProtocol ? $servsrProtocol : "HTTP/1.0";
    header ( $serverProtocol . ' 500 Internal Server Error', true, 500 );
    die ( json_encode ( array (
                    'message' => $e->getMessage(),
                    'code' => 500
    ) ) );
}