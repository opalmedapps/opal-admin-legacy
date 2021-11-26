<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$uid = $_POST["userId"];
$newPassword = $_POST["new_password"];

$myFirebase = new Firebase();
//'b0tEHXqDqwN9s7qKQdX1SqdTIQm1', "testpassword1234!"

print json_encode($myFirebase->changePassword($uid, $newPassword));