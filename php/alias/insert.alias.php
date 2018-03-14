<?php

	/* To insert a newly created alias */
	include_once('alias.inc');

	// Construct array from FORM params
	$aliasArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
		'description_EN'    => $_POST['description_EN'],
		'description_FR'    => $_POST['description_FR'],
 		'serial' 	        => $_POST['serial'],
        'type' 		        => $_POST['type'],
        'color'             => $_POST['color'],
        'edumat'            => $_POST['eduMat'],
        'source_db'         => $_POST['source_db'],
		'terms' 	        => $_POST['terms'],
		'user'				=> $_POST['user']
	);

	$aliasObject = new Alias; // Object

	// Call function
	$aliasObject->insertAlias($aliasArray);
	
?>
