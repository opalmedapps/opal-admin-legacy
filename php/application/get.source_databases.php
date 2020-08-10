<?php
header('Content-Type: application/javascript');
/* To get source databases */

include_once('application.inc');

$appObject = new Application; // Object
$sourceDatabases = $appObject->getSourceDatabases();
echo json_encode($sourceDatabases);
