<?php

header('Content-Type: application/javascript');
include_once("../../config.php");
require __DIR__.'/../../../vendor/autoload.php';

$patientObj = new Patient();
$myFirebase = new Firebase();
//'b0tEHXqDqwN9s7qKQdX1SqdTIQm1', "testpassword1234!"

$myFirebase->changeEmail($_POST);
$patientObj->updatePatientEmail($_POST);
http_response_code(HTTP_STATUS_SUCCESS);