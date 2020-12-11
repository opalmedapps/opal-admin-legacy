<?php
include_once("../config.php");

$sourceDiag = new MasterSourceDiagnosis(); // Object
$results = $sourceDiag->getSourceDiagnoses();

header('Content-Type: application/javascript');
echo json_encode($results);