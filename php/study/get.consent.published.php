<?php
include_once("../config.php");

$consentId = strip_tags($_POST['consentId']);

$study = new Study(); // Object
$results = $study->getConsentPublished($consentId);

header('Content-Type: application/javascript');
echo json_encode($results);
?>