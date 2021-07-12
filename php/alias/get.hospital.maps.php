<?php
include_once("../config.php");

$anAlias = new Alias; // Object
$existingHosMapList = $anAlias->getHospitalMaps();

header('Content-Type: application/javascript');
echo json_encode($existingHosMapList);