<?php

	/* To insert a newly created legacy questionnaire */
	include_once('legacy-questionnaire.inc');

	// Construct array from FORM params
	$legacyQuestionnaireDetails	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
		'intro_EN'          => str_replace(array('"', "'"), '\"', $_POST['intro_EN']),
        'intro_FR'          => str_replace(array('"', "'"), '\"', $_POST['intro_FR']),
		'expression' 	    => $_POST['legacy_questionnaire'],
		'filters'			=> $_POST['filters'],
        'occurrence'		=> $_POST['occurrence']
	);

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
	$legacyQuestionnaire->insertLegacyQuestionnaire($legacyQuestionnaireDetails);
	
?>
