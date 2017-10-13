<?php 

	/* To update legacy questionnaire when the "Publish" checkbox has been changed */
	include_once('legacy-questionnaire.inc');

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Retrieve FORM params
	$legacyQuestionnairePublishFlags	= $_POST['flagList'];
	
	// Call function
    $response = $legacyQuestionnaire->updatelegacyQuestionnairePublishFlags($legacyQuestionnairePublishFlags);
    print json_encode($response); // Return response

?>


