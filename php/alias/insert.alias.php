<?php

	/* To insert a newly created alias */
	include_once('alias.inc');

	// Construct array from FORM params
	$aliasArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
		'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
		'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
 		'serial' 	        => $_POST['serial'],
        'type' 		        => $_POST['type'],
        'color'             => $_POST['color'],
        'edumat'            => $_POST['eduMat'],
        'source_db'         => $_POST['source_db'],
		'terms' 	        => $_POST['terms'],
		'user'				=> $_POST['user'],
		'checkin_details'	=> $_POST['checkin_details'],
		'hospitalMap'		=> $_POST['hospitalMap']
	);

	$aliasObject = new Alias; // Object

	// Call function
	print $aliasObject->insertAlias($aliasArray);
	
?>
