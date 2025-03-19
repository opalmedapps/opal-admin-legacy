<?php
include_once("../config.php");
$id = strip_tags($_POST["ID"]);

$user = new User();
$user->deleteUser($id);

// when user is marked as deleted successfully in the legacy opaladmin, call new backend api to deactivate user
$user->deleteUserNewBackend($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);