<?php
header('Content-Type: application/json');
//require_once 'classes/customers.php';
require_once 'classes/applicationLogin.php';
require_once 'classes/applicationProduct.php';
require_once 'classes/applicationRegister.php';

try {
    // get the URL and extract the string after /api.php/
    $start = strpos($_SERVER['PHP_SELF'], '/api.php/');
    if ($start === FALSE) {
        throw new Exception("No resource");
    }

    $url = substr($_SERVER['PHP_SELF'], $start + 9);
    if ($url == '') {
        throw new Exception("No resource");
    }

    // extract the command + parameters
    $resourceAndParamaters = explode('/', $url);
    $params = [];

    $resource = $resourceAndParamaters[0];
    for ($i = 1; $i < count($resourceAndParamaters); $i++) {
        $params[] = $resourceAndParamaters[$i];
    }

    // extract the HTTP command
    $requestType = $_SERVER['REQUEST_METHOD'];
    if ($requestType != 'GET') {
        $data = file_get_contents('php://input');            
        $data = json_decode($data);
    } else {
        $data = '';
    }


    // analyse the command
    switch ($resource) {
        case 'customers':
            $o = new Customer();
            $retval = $o->execute($params, $data);
            break;
        case 'products':
            $o = new ApplicationProduct();
            $retval = $o->execute($params, $data);
            break;
        case 'login':
            $o = new ApplicationLogin();
            $retval = $o->execute($params, $data);            
            break;
        case 'register':
            $o =  new ApplicationRegister();
            $retval = $o->execute($params, $data);            
            break;
        default:
            throw new Exception("Unknown resource: $resource");
    }

    $retval->error = 200;
    $retval->errorDescription = '';
    echo json_encode($retval);
} catch (Exception $e) {
    $errorObject = new stdClass();
    $errorObject->error = 500;
    $errorObject->errorDescription = $e->getMessage();
    echo json_encode($errorObject);
}
