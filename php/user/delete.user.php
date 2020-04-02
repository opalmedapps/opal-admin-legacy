<?php

include_once('user.inc');

$id = strip_tags($_POST["ID"]);
$OAUserId = strip_tags($_POST["OAUserId"]);

$user = new User($OAUserId);
$response = $user->deleteUser($id);

header('Content-Type: application/javascript');
print json_encode($response); // Return response