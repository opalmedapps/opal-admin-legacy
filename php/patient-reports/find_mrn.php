<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$patientMRN	= $_POST['pmrn'];
$response = $patReport->findPatientByMRN($patientMRN);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>