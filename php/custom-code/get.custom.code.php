<?php

include_once('custom.code.inc');

$customCode = new CustomCode(); // Object
$results = $customCode->getCustomCodes();

header('Content-Type: application/javascript');
echo json_encode($results);