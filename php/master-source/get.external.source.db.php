<?php

include_once("../config.php");

$sourceDiag = new MasterSourceDiagnosis(); // Object
$results = $sourceDiag->getExternalSourceDatabase();

header('Content-Type: application/javascript');
echo json_encode($results);

