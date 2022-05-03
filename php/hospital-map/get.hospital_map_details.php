<?php
include_once("../config.php");

$hosMap = new HospitalMap(); // Object
$hosMapDetails = $hosMap->getHospitalMapDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($hosMapDetails);