<?php

include_once("../config.php");

$userObject = new User();
$userObject->updateUser($_POST);
// call new backend api function to update the users and their groups
$userObject->updateUserNewBackend($_POST);

// call new backend api function to update user manager group according to the role assigned to them
$userObject->checkUpdateUserPrivilege($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);