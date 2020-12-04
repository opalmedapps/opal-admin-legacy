<?php
/* To get a list of existing hospital maps */
include_once('alias.inc');

$anAlias = new Alias; // Object
$existingHosMapList = $anAlias->getHospitalMaps();

header('Content-Type: application/javascript');
echo json_encode($existingHosMapList);