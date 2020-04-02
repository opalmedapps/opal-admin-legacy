<?php
include_once('user.inc');

$userObject = new User();  // Object
$userDetails = array(
    'id'				=> $_POST['id'],
    'language'	=> $_POST['language']
);

// Call function
$response = $userObject->updateLanguage($userDetails);
header('Content-Type: application/json');
echo json_encode($response); // Return response
