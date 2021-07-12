<?php

include_once("../config.php");

$questionnaire = new Questionnaire();
$results = $questionnaire->getPurposes();

header('Content-Type: application/javascript');
echo json_encode($results);