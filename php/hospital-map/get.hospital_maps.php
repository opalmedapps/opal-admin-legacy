<?php
header('Content-Type: application/javascript');
/* To get a list of existing hospital maps */
include_once('hospital-map.inc');

$hosMap = new HospitalMap; // Object
$existingHosMapList = $hosMap->getHospitalMaps();

echo json_encode($existingHosMapList);