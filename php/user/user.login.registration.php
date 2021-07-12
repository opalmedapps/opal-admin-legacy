<?php
include_once("../config.php");

$usr = new User(true); // Object
$result = $usr->userLoginRegistration($_POST);

header('Content-Type: application/javascript');
http_response_code($result);
