<?php
include_once("../config.php");

$userObject = new User();  // Object
$response = $userObject->updateLanguage($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);