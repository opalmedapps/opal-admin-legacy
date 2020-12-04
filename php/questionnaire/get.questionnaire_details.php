<?php
include_once('questionnaire.inc');

$questionnaireId = strip_tags($_POST['questionnaireId']);

$questionnaire = new Questionnaire();
$questionnaireDetails = $questionnaire->getQuestionnaireDetails($questionnaireId);
unset($questionnaireDetails["category"]);
unset($questionnaireDetails["createdBy"]);
unset($questionnaireDetails["creationDate"]);
unset($questionnaireDetails["lastUpdated"]);
unset($questionnaireDetails["updatedBy"]);
unset($questionnaireDetails["parentId"]);
unset($questionnaireDetails["optionalFeedback"]);
unset($questionnaireDetails["version"]);

header('Content-Type: application/javascript');
echo json_encode($questionnaireDetails);
?>
