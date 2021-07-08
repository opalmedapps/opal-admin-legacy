<?php
include_once("../config.php");

$questionnaire = new Questionnaire();
$results = $questionnaire->getLastCompletedQuestionnaire($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);

