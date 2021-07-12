<?php
header('Content-Type: application/javascript');
/* To get a list of existing educational material types */
include_once('educational-material.inc');

$eduMat = new EduMaterial; // Object
$types = $eduMat->getEducationalMaterialTypes();

echo json_encode($types);