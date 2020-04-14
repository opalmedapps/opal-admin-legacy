<?php

include_once('patient.inc');
$OAUserId = $_POST['OAUserId'];
;
$patientObject = new Patient($OAUserId); // Object
$patientTransfers	= $_POST["patientTransfers"]['transferList'];
$patientList = array();

foreach($patientTransfers as $patient) {
    array_push($patientList, array('serial' => $patient['serial'], 'transfer' => $patient['transfer'], 'patientId' => $patient['patientId']));
}

$response = $patientObject->updatePatientTransferFlags($patientList);
header('Content-Type: application/javascript');
print json_encode($response); // Return response
