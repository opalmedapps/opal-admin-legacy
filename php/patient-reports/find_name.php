<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$patientName	= $_POST['pname'];
$response = $patReport->findPatientByName($patientName);
header('Content-Type: application/javascript');
print json_encode($response); // Return response