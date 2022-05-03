<?php
include_once("../config.php");

$hosMap = new HospitalMap(); // Object
$existingHosMapList = $hosMap->getHospitalMaps();

header('Content-Type: application/javascript');
echo json_encode($existingHosMapList);