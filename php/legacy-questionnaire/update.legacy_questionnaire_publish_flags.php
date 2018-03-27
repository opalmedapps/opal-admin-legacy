<?php 

	/* To update legacy questionnaire when the "Publish" checkbox has been changed */
	include_once('legacy-questionnaire.inc');

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Retrieve FORM params
	$legacyQuestionnairePublishFlags	= $_POST['flagList'];
	$user 								= $_POST['user'];
	
	// Call function
    $response = $legacyQuestionnaire->updatelegacyQuestionnairePublishFlags($legacyQuestionnairePublishFlags, $user);
    print json_encode($response); // Return response

?>


