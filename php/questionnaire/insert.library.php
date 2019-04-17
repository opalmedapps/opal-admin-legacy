<?php
/* To insert a newly-created library */
include_once('questionnaire.inc');

// Construct array from FORM params
$libraryArray = array(
    'name_EN' => strip_tags($_POST['name_EN']),
    'name_FR' => strip_tags($_POST['name_FR']),
    'private' => strip_tags($_POST['private'])
);

$userId = strip_tags($_POST['userid']);
$libraryObj = new Library($userId); // Object

// Call function
$libraryObj->insertLibrary($libraryArray);
die();
header('Content-Type: application/javascript');
$response['value'] = true;
$response['message'] = 200;
echo json_encode($response);
?>
