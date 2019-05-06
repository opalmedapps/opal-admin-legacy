<?php
/* To insert a newly-created library */
include_once('questionnaire.inc');

// Construct array from FORM params
$libraryArray = array(
    'name_EN' => strip_tags($_POST['name_EN']),
    'name_FR' => strip_tags($_POST['name_FR']),
    'private' => strip_tags($_POST['private'])
);

$userId = strip_tags($_POST['userId']);
$libraryObj = new Library($userId); // Object

// Call function
$libraryObj->insertLibrary($libraryArray);
header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);
?>
