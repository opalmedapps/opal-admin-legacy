<?php

	/* To insert a newly created educational material */
	include_once('educational-material.inc');

	// Construct array from FORM params
	$eduMatArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'url_EN'            => $_POST['url_EN'],
        'url_FR'            => $_POST['url_FR'],
        'share_url_EN'      => $_POST['share_url_EN'],
        'share_url_FR'      => $_POST['share_url_FR'],
        'type_EN'           => $_POST['type_EN'],
        'type_FR'           => $_POST['type_FR'],
        'phase_in_tx'       => $_POST['phase_in_tx'],
        'triggers'          => $_POST['triggers'],
		'tocs' 		        => $_POST['tocs'],
		'user'				=> $_POST['user']
		 
	);

	$eduMat = new EduMaterial; // Object

	// Call function
	$response = $eduMat->insertEducationalMaterial($eduMatArray);
	print json_encode($response); // Return response
	
?>
