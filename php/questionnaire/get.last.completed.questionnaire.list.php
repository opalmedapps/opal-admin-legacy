<?php
include_once("../config.php");

$questionnaire = new Questionnaire();

$results = $questionnaire->getLastCompletedQuestionnaireForPatients($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);
