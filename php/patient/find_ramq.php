<?php

include_once("../config.php");
$patReport = new Patient(); // Object
$response = $patReport->findPatientByRAMQ($_POST);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>