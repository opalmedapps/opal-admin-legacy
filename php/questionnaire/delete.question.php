<?php
header('Content-Type: application/javascript');
/* To delete a question */
include_once('questionnaire.inc');

// Retrieve FORM param
$serNum = strip_tags($_POST['ID']);
$OAUserId = strip_tags($_POST['OAUserId']);

// Call function
$questionObj = new Question($OAUserId); // Object
$response = $questionObj->deleteQuestion($serNum);

print json_encode($response); // Return response