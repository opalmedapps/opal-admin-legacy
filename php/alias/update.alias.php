<?php 

	/* To update an alias for any changes */
	include_once('alias.inc');

	$aliasObject = new Alias; // Object 

	// Construct array from FORM params
	$aliasArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
		'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
		'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
 		'serial' 	        => $_POST['serial'],
        'terms' 	        => $_POST['terms'],
        'source_db'         => $_POST['source_db'],
        'color'             => $_POST['color'],
        'edumatser'            => $_POST['eduMatSer'],
        'user'				=> $_POST['user']
	);

	// Call function
    $response = $aliasObject->updateAlias($aliasArray);
    print json_encode($response); // Return response

?>
