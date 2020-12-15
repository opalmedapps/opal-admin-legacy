<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$qName	= $_POST['qstName'];

$response = $patReport->getQuestionnaireReport($qName);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>