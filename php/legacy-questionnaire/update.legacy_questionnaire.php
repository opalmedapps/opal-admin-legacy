<?php 

	/* To update a legacy questionnaire for any changes */
	include_once('legacy-questionnaire.inc');

	// Construct array from FORM params
	$legacyQuestionnaireDetails	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'filters'           => $_POST['filters'],
        'serial' 			=> $_POST['serial'],
        'occurrence'		=> $_POST['occurrence']
	);

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
    $response = $legacyQuestionnaire->updateLegacyQuestionnaire($legacyQuestionnaireDetails);
    print json_encode($response); // Return response

?>
