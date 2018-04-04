<?php 

	/* To update a diagnosis translation for any changes */
	include_once('diagnosis-translation.inc');
	
	$Diagnosis = new Diagnosis; // Object

	// Construct array from FORM params
	$diagnosisTranslationDetails = array(
		'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
		'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
		'edumatser'         => $_POST['eduMatSer'],
        'serial'            => $_POST['serial'],
        'diagnoses'         => $_POST['diagnoses'],
        'user'				=> $_POST['user'],
        'details_updated'	=> $_POST['details_updated'],
        'codes_updated'		=> $_POST['codes_updated']
	);

	 // Call function
	 $response = $Diagnosis->updateDiagnosisTranslation($diagnosisTranslationDetails);
	 print json_encode($response); // Return response

 ?>