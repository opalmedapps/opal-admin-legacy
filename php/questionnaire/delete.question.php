<?php
header('Content-Type: application/javascript');
/* To delete a question */
include_once('questionnaire.inc');

// Retrieve FORM param
$serNum = strip_tags($_POST['ID']);
$userId = strip_tags($_POST['userId']);
$questionObj = new Question; // Object

// Call function
$response = $questionObj->deleteQuestion($serNum, $userId);

print json_encode($response); // Return response
?>
