<?php

	/* To insert a newly created educational material */
	include_once('educational-material.inc');

	// Construct array
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
        'filters'           => $_POST['filters'],
 		'tocs' 		        => $_POST['tocs']
	);

	$eduMat = new EduMaterial; // Object

	// Call function
	print $eduMat->insertEducationalMaterial($eduMatArray);
	
?>
