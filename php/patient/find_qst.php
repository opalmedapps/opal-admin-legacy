<?php

include_once("../config.php");
$patReport = new Patient(); // Object

$response = $patReport->findQuestionnaireOptions();
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>