<?php

header('Content-Type: application/javascript');
include_once("../../config.php");

$patientObj = new Patient();
$uid = $patientObj->getPatientUsername($_POST);
$myFirebase = new Firebase();
//'b0tEHXqDqwN9s7qKQdX1SqdTIQm1', "testpassword1234!"

$myFirebase->changeEmail($uid, $_POST);
$response = $patientObj->updatePatientEmail($_POST);

print json_encode($response);