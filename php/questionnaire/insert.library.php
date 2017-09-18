<?php
	/* To insert a newly-created library */
	include_once('questionnaire.inc');

	// Construct array from FORM params
	$libraryArray = array(
		'name_EN'			=> $_POST['name_EN'],
		'name_FR'			=> $_POST['name_FR'],
		'private'			=> $_POST['private'],
		'created_by'		=> $_POST['created_by'],
		'last_updated_by'	=> $_POST['last_updated_by']
	);

	$libraryObj = new Library; // Object

	// Call function 
	$libraryObj->insertLibrary($libraryArray);
?>