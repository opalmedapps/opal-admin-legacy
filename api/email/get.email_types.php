<?php
header('Content-Type: application/javascript');
/* To get a list email types */

include_once('email.inc');

$emailObj = new Email; // Object
$types = $emailObj->getEmailTypes();

echo json_encode($types);