<?php

	/* To insert a newly created diagnosis translation */
	include_once('diagnosis-translation.inc');
	
	// Construct array from FORM params
	$diagnosisTranslationDetails = array(
		'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
		'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
		'edumat'            => $_POST['eduMat'],
        'diagnoses'         => $_POST['diagnoses'],
        'user'				=> $_POST['user']
	);

	$Diagnosis = new Diagnosis; // Object

	// Call function 
	print $Diagnosis->insertDiagnosisTranslation($diagnosisTranslationDetails);

?>