<?php
/* To validate login */
include_once('user.inc');

$usr = new User(true); // Object
$result = $usr->userLogin($_POST);

header('Content-Type: application/javascript');
print json_encode($result); // Return response