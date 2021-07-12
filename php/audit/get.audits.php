<?php
include_once("../config.php");

$alert = new Audit(); // Object
$results = $alert->getAudits();

header('Content-Type: application/javascript');
echo json_encode($results);