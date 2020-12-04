<?php
include_once("../config.php");

$diagnosis = new Diagnosis();
$results = $diagnosis->getEducationalMaterials();

header('Content-Type: application/javascript');
echo json_encode($results);