<?php
include_once('questionnaire.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
$questionnaire = new Questionnaire($OAUserId);
$questionnaireList = $questionnaire->getFinalizedQuestionnaires();

header('Content-Type: application/javascript');
echo json_encode($questionnaireList);