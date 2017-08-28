<?php
    /* Gets a list of existing question groups with libraries */ 
    include_once('questionnaire.inc');

	// Retrieve form params
    $callback = $_GET['callback'];
	$userid = $_GET['userid'];
	
    $questionGroup = new QuestionGroup(); // Object 

	// Call function
    $questionGroupList = $questionGroup->getQuestionGroupsWithLibraries($userid);

    // Callback to http request
    print $callback.'('.json_encode($questionGroupList).')';
?>