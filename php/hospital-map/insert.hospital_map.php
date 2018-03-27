<?php

	/* To insert a newly created hospital map */
    include_once('hospital-map.inc');

    // Construct array from FORM params
    $hosMapArray = array(
        'name_EN'           => $_POST['name_EN'],
        'name_FR'           => $_POST['name_FR'],
        'description_EN'    => str_replace(array('"', "'"), '\"', $_POST['description_EN']),
        'description_FR'    => str_replace(array('"', "'"), '\"', $_POST['description_FR']),
        'url'               => $_POST['url'],
        'qrid'              => $_POST['qrid'],
        'user'              => $_POST['user']
    );

    $hosMap = new HospitalMap; // Object

    // Call function
    print $hosMap->insertHospitalMap($hosMapArray);
?>

