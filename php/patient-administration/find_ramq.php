<?php

include_once("../config.php");
$pat = new PatientAdministration(); // Object
$response = $pat->findPatientByRAMQ($_POST);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>