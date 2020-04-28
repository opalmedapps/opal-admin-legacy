<?php

include_once('study.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);

$study = new Study($OAUserId); // Object
$results = $study->getStudies();

header('Content-Type: application/javascript');
echo json_encode($results);