<?php
include_once("../config.php");

$user = new User();
$roles = $user->getRoles();

header('Content-Type: application/javascript');
echo json_encode($roles);