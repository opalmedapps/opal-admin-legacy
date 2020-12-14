<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$patientRAMQ	= $_POST['pramq'];
$response = $patReport->findPatientByRAMQ($patientRAMQ);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>