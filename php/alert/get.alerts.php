<?php
include_once("../config.php");

$alert = new Alert(); // Object
$results = $alert->getAlerts();

header('Content-Type: application/javascript');
echo json_encode($results);