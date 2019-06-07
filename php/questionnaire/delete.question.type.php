<?php
header('Content-Type: application/javascript');
/* To delete a question */
include_once('questionnaire.inc');

// Retrieve FORM param
$questionTypeId = strip_tags($_POST['ID']);
$OAUserId = strip_tags($_POST['OAUserId']);

// Call function
$questionTypeObj = new TemplateQuestion($OAUserId); // Object

$response = $questionTypeObj->deleteTemplateQuestion($questionTypeId);

print json_encode($response); // Return response
?>
