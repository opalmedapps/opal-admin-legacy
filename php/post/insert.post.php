<?php
include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

//$postObject = new Post();
//$postObject->insertPost($_POST);
//
//header('Content-Type: application/javascript');
//http_response_code(HTTP_STATUS_SUCCESS);

$backendApi = new NewOpalApiCall(
    '/api/users/caregivers/ThQKckoll2Y3tXcA1k7iCfGhmeu1/',
    'PUT',
    'en',
    ['email' => 'bbb@bbb.com'],
);

print $backendApi->execute();