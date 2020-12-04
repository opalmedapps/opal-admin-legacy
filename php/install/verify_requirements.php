<?php
/* To verify installation requirements */
include_once('install.inc');

$callback 	= $_GET['callback'];
$installObj = new Install; // Object

// Call function
$response = $installObj->verifyRequirements($abspath);

header('Content-Type: application/javascript');
echo json_encode($response);