<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$matType	= $_POST['type'];
$matName = $_POST['name'];

$response = $patReport->getEducationalMaterialReport($matType, $matName);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>