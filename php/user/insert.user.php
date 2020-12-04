<?php
include_once("../config.php");

$userObj = new User();
$userObj->insertUser($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);