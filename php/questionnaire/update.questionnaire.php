<?php
$OAUserId = strip_tags($_POST['OAUserId']);
print_R($_POST);die();

$questionnaire = new Questionnaire($OAUserId);
$questionnaireArray = $questionnaire->validateAndSanitize($_POST);
if(!$questionnaireArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire format");

print_R($questionnaireArray);die();

$questionnaire->updateQuestionnaire($questionnaireArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);
?>
