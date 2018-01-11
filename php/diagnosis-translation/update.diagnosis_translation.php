<?php 

	/* To update a diagnosis translation for any changes */
	include_once('diagnosis-translation.inc');
	
	$Diagnosis = new Diagnosis; // Object

	// Construct array from FORM params
	$diagnosisTranslationDetails = array(
		'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => $_POST['description_EN'],
		'description_FR'    => $_POST['description_FR'],
		'edumat'            => $_POST['eduMat'],
        'serial'            => $_POST['serial'],
        'diagnoses'         => $_POST['diagnoses']
	);

	 // Call function
	 $response = $Diagnosis->updateDiagosisTranslation($diagnosisTranslationDetails);
	 print json_encode($response); // Return response

 ?>