<?php

	/* To update hospital map */
    include_once('hospital-map.inc');

    // Construct array from FORM params
    $hosMapArray = array(
        'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
        'url_EN'            => $_POST['url_EN'],
        'url_FR'            => $_POST['url_FR'],
        'qrid'              => $_POST['qrid'],
        'serial'            => $_POST['serial'],
        'user'              => $_POST['user']
    );

    $hosMap = new HospitalMap;

    // Call function
    $hosMap->updateHospitalMap($hosMapArray);

?>

