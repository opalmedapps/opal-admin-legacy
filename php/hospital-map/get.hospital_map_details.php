<?php
header('Content-Type: application/javascript');
/* To get details on a particular hospital map */
include_once('hospital-map.inc');

$serial = strip_tags($_POST['serial']);
$hosMap = new HospitalMap; // Object
$hosMapDetails = $hosMap->getHospitalMapDetails($serial);

echo json_encode($hosMapDetails);