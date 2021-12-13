<?php

include_once("../config.php");

$ormsList = new Study();
$result = $ormsList->getStudiesPatientConsented($_POST);

header('Content-Type: application/javascript');
echo json_encode($result);