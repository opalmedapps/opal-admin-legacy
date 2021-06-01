<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$messageUpdates	= strip_tags($_POST['UpdateInformation']);

$sms = new Sms();
$response = $sms->updateSmsMessage($messageUpdates);

echo json_encode($response);
