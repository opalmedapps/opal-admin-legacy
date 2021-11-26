<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$uid = $_POST["userId"];
$newEmail = $_POST["new_email"];

$myFirebase = new Firebase();
//'b0tEHXqDqwN9s7qKQdX1SqdTIQm1', "zeyu.dou@mail.mcgill.ca"

print json_encode($myFirebase->changeEmail($uid, $newEmail));