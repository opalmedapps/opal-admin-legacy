<?php
include_once("../config.php");
$id = strip_tags($_POST["ID"]);

$user = new User();
$user->deleteUser($id);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);