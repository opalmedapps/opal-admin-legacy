<?php

include_once('study.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);

$customCode = new Study($OAUserId); // Object
$results = $customCode->getStudies();

header('Content-Type: application/javascript');
echo json_encode($results);