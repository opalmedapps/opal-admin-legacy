<?php

include_once('user.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
if($OAUserId == "")
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Access denied.");

$userObject = new User($OAUserId); // Object
$users = $userObject->getUsers();

header('Content-Type: application/javascript');
echo json_encode($users);