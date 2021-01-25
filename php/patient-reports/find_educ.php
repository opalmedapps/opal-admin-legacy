<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$response = $patReport->findEducationalMaterialOptions($_POST);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>