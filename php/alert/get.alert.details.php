<?php
include_once("../config.php");

$alertId = strip_tags($_POST['alertId']);

$alert = new Alert(); // Object
$response = $alert->getAlertDetails($alertId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response