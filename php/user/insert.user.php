<?php

include_once("../config.php");

$userObj = new User();
$userObj->insertUser($_POST);
// call new backend api function to insert the new users and their groups
$userObj->insertUserNewBackend($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);