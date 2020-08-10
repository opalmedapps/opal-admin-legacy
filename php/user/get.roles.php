<?php

include_once('user.inc');

$user = new User();
$roles = $user->getRoles();

header('Content-Type: application/javascript');
echo json_encode($roles);