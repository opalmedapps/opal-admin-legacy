<?php

header('Content-Type: application/javascript');
include_once('patient.inc');
include_once('../user/user.inc');
include_once('../audit-logs/audit-logs.inc');

$ssn = strip_tags($_POST['ssn']);
$id = strip_tags($_POST['id']);
$OAUserId = strip_tags($_POST['userId']);

$patientObj = new Patient($OAUserId); // Object
$patientResponse = $patientObj->findPatient($ssn, $id);

//INSERT INTO AUDIT LOGS TABLE

echo json_encode($patientResponse);
