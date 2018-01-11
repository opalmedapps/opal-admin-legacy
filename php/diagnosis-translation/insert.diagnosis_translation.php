<?php

	/* To insert a newly created diagnosis translation */
	include_once('diagnosis-translation.inc');
	
	// Construct array from FORM params
	$diagnosisTranslationDetails = array(
		'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => $_POST['description_EN'],
		'description_FR'    => $_POST['description_FR'],
		'edumat'            => $_POST['eduMat'],
        'diagnoses'         => $_POST['diagnoses']
	);

	$Diagnosis = new Diagnosis; // Object

	// Call function 
	print $Diagnosis->insertDiagnosisTranslation($diagnosisTranslationDetails);

?>