<?php 

	/* To update a diagnosis translation for any changes */
	include_once('diagnosis-translation.inc');
	
	$Diagnosis = new Diagnosis; // Object

	// Construct array from FORM params
	$diagnosisTranslationDetails = array(
		'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => str_replace(array('"', "'"), '\"', $_POST['description_EN']),
		'description_FR'    => str_replace(array('"', "'"), '\"', $_POST['description_FR']),
		'edumatser'         => $_POST['eduMatSer'],
        'serial'            => $_POST['serial'],
        'diagnoses'         => $_POST['diagnoses'],
        'user'				=> $_POST['user']
	);

	 // Call function
	 $response = $Diagnosis->updateDiagnosisTranslation($diagnosisTranslationDetails);
	 print json_encode($response); // Return response

 ?>