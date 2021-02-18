<?php
include_once("../config.php");

$questionnaire = new Questionnaire();
$results = $questionnaire->GetPurposesRespondents();

header('Content-Type: application/javascript');
echo json_encode($results);