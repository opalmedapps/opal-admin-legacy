<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$matType	= $_POST['matType']; //input

$response = $patReport->findEducationalMaterialOptions($matType);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>