<?php 

	/* To update an edu material for any changes */
    include_once('educational-material.inc');

    $eduMat = new EduMaterial; // Object

    // Construct array from FORM params
    $eduMatArray = array(
	    'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'url_EN'            => $_POST['url_EN'],
        'url_FR'            => $_POST['url_FR'],
        'share_url_EN'      => $_POST['share_url_EN'],
        'share_url_FR'      => $_POST['share_url_FR'],
        'type_EN'           => $_POST['type_EN'],
        'type_FR'           => $_POST['type_FR'],
        'phase_serial'      => $_POST['phase_serial'],
        'filters'           => $_POST['filters'],
        'tocs' 		        => $_POST['tocs'],
		'serial'            => $_POST['serial'],
		'user'				=> $_POST['user']
    );

    // Call function
    $response = $eduMat->updateEducationalMaterial($eduMatArray);
    print json_encode($response); // Return response

?>


