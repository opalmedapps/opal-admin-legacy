<?php 

	/* To update an alias for any changes */
	include_once('alias.inc');

	$aliasObject = new Alias; // Object 

	// Retrieve FORM params
	$aliasName_EN	    = $_POST['name_EN'];
	$aliasName_FR	    = $_POST['name_FR'];
	$aliasDesc_FR	    = $_POST['description_FR'];
	$aliasDesc_EN	    = $_POST['description_EN'];
	$aliasSer 	        = $_POST['serial'];
	$aliasTerms	        = $_POST['terms'];
    $aliasColorTag      = $_POST['color'];
    $aliasEduMat        = $_POST['eduMat'];
    $aliasSourceDB      = $_POST['source_db'];

	$aliasArray	= array(
		'name_EN' 	        => $aliasName_EN,
		'name_FR' 	        => $aliasName_FR,
		'description_EN'    => $aliasDesc_EN,
		'description_FR'    => $aliasDesc_FR,
 		'serial' 	        => $aliasSer,
        'terms' 	        => $aliasTerms,
        'source_db'         => $aliasSourceDB,
        'color'             => $aliasColorTag,
        'edumat'            => $aliasEduMat
	);

	// Call function
    $response = $aliasObject->updateAlias($aliasArray);
    print json_encode($response); // Return response

?>
