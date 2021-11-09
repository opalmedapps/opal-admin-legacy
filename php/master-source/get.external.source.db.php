<?php

include_once("../config.php");

$sourceDiag = new MasterSourceModule(); // Object
$results = $sourceDiag->getExternalSourceDatabase();

header('Content-Type: application/javascript');
echo json_encode($results);

