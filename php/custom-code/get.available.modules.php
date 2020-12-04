<?php
include_once('custom.code.inc');

$customCode = new CustomCode();
$result = $customCode->getAvailableModules();

header('Content-Type: application/javascript');
echo json_encode($result);