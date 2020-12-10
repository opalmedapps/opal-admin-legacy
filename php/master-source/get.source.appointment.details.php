<?php
include_once("../config.php");

$sourceDiag = new MasterSourceAppointment(); // Object
$results = $sourceDiag->getSourceAppointmentDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);