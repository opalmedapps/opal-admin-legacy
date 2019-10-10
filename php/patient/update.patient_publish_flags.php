<?php

include_once('patient.inc');

$patientObject = new Patient; // Object
$patientTransfers	= $_POST['transferList'];
$patientList = array();

foreach($patientTransfers as $patient) {
    array_push($patientList, array('serial' => $patient['serial'], 'transfer' => $patient['transfer']));
}

$response = $patientObject->updatePatientTransferFlags($patientList);
header('Content-Type: application/javascript');
print json_encode($response); // Return response