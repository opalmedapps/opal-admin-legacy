<?php
include_once("../config.php");

$auditId = strip_tags($_POST['ID']);

$alert = new Audit(); // Object
$response = $alert->getAuditDetails($auditId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response