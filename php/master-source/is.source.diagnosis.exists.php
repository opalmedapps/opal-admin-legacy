<?php

include_once("../config.php");

$sourceDiag = new MasterSourceDiagnosis(); // Object
$results = $sourceDiag->doesDiagnosisExists($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);