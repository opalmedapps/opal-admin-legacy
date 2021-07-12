<?php
header('Content-Type: application/javascript');
/* To get the application build */

include_once('application.inc');

$appObject = new Application; // Object
$build = $appObject->getApplicationBuild();

echo json_encode($build);
