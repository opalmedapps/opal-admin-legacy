<?php
header('Content-Type: application/javascript');
/* To get a list of existing educational materials */
include_once('educational-material.inc');

$eduMat = new EduMaterial; // Object
$existingEduMatList = $eduMat->getEducationalMaterials();

echo json_encode($existingEduMatList);