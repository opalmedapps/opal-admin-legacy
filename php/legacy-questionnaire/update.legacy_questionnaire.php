<?php 

	/* To update a legacy questionnaire for any changes */
	include_once('legacy-questionnaire.inc');

	// Construct array from FORM params
	$legacyQuestionnaireDetails	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
		'intro_EN'          => str_replace(array('"', "'"), '\"', $_POST['intro_EN']),
        'intro_FR'          => str_replace(array('"', "'"), '\"', $_POST['intro_FR']),
        'filters'           => $_POST['filters'],
        'serial' 			=> $_POST['serial'],
        'occurrence'		=> $_POST['occurrence'],
        'user'				=> $_POST['user']
	);

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
    $response = $legacyQuestionnaire->updateLegacyQuestionnaire($legacyQuestionnaireDetails);
    print json_encode($response); // Return response

?>
