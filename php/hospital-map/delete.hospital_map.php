<?php
include_once("../config.php");

$hosMap = new HospitalMap(); // Object
$hosMap->deleteHospitalMap($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);