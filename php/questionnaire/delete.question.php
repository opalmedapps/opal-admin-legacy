<?php
	/* To delete a question */
	include_once('questionnaire.inc');

	// Retrieve FORM param
	$serNum = $_POST['serNum'];

	$questionObj = new Question; // Object

	// Call function
	$response = $questionObj->deleteQuestion($serNum);
	
    print json_encode($response); // Return response
?>