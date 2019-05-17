<?php

include_once('questionnaire.inc');

$questionnaireArray = array(
    'name_EN'					=> $_POST['name_EN'],
    'name_FR'					=> $_POST['name_FR'],
    'private'					=> $_POST['private'],
    'publish'					=> $_POST['publish'],
    'last_updated_by'	=> $_POST['last_updated_by'],
    'created_by'			=> $_POST['created_by'],
    'tags'						=> $_POST['tags'],
    'questiongroups'	=> $_POST['groups'],
    'filters'					=> $_POST['filters'],
    'user'						=> $_POST['user']
);

$questionnaire = new Questionnaire($userId);
$questionnairesList = $questionnaire->getQuestionnaires();
$questionnaireObj->insertQuestionnaire($questionnaireArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);

?>
