<?php

include_once("../config.php");

$userObject = new User();
$userObject->updateUser($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);