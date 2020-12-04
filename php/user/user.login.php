<?php

include_once("../config.php");
$usr = new User(true); // Object
$result = $usr->userLogin($_POST);

header('Content-Type: application/javascript');
echo json_encode($result); // Return response