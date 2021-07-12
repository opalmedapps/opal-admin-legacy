<?php
header('Content-Type: application/javascript');
/* To get list logs on a particular educational material */
include_once('educational-material.inc');

$serials = json_decode($_POST['serials']);
$eduMat = new EduMaterial; // Object
$educationalMaterialLogs = $eduMat->getEducationalMaterialListLogs($serials);

echo json_encode($educationalMaterialLogs);