<?php 

	/* To update a legacy questionnaire for any changes */
	include_once('legacy-questionnaire.inc');

	// Construct array from FORM params
	$legacyQuestionnaireDetails	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
		'intro_EN'          => filter_var($_POST['intro_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'intro_FR'          => filter_var($_POST['intro_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
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
