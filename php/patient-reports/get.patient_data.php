<?php

include_once('patient-reports.inc');

$patReport = new PatientReports; // Object
$pNum	= $_POST['psnum'];
$fList = array(
    "diagnosis" => $_POST['diagnosis'],
    "appointments" => $_POST['appointments'],
    "questionnaires" => $_POST['questionnaires'],
    "education" => $_POST['education'],
    "testresults" => $_POST['testresults'],
    "notes" => $_POST['notes'],
    "treatplan" => $_POST['treatplan'],
    "clinicalnotes" => $_POST['clinicalnotes'],
    "treatingteam" => $_POST['treatingteam'],
    "general" => $_POST['general']
);
$response = $patReport->getPatientReport($pNum, $fList);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>