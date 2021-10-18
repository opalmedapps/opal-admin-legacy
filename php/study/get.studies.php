<?php

include_once('study.inc');

$study = new Study(); // Object
$results = $study->getStudies();

header('Content-Type: application/javascript');
echo json_encode($results);