<?php
header('Content-Type: application/javascript');
/* To get list logs on a particular legacy questionnaire */
include_once('legacy-questionnaire.inc');

$serials = json_decode($_POST['serials']);
$legacyQuestionnaire = new LegacyQuestionnaire; // Object
$legacyQuestionnaireLogs = $legacyQuestionnaire->getLegacyQuestionnaireListLogs($serials);

echo json_encode($legacyQuestionnaireLogs);