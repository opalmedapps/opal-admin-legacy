<?php

include_once('user.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
$user = new User($OAUserId);
$roles = $user->getRoles();

header('Content-Type: application/javascript');
echo json_encode($roles);