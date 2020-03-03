<?php
include_once('custom-code.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$customCode = new CustomCode($OAUserId);
$result = $customCode->getAvailableModules();

header('Content-Type: application/javascript');
echo json_encode($result);