<?php
header('Content-Type: application/javascript');
/* To get logs on a particular legacy questionnaire for highcharts */
include_once('legacy-questionnaire.inc');

$serial = (strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$legacyQuestionnaire = new LegacyQuestionnaire; // Object
$legacyQuestionnaireLogs = $legacyQuestionnaire->getLegacyQuestionnaireChartLogs($serial);

echo json_encode($legacyQuestionnaireLogs);