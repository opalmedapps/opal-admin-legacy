<?php
header('Content-Type: application/javascript');
/* To get a list of existing educational material parents */
include_once('educational-material.inc');

$eduMat = new EduMaterial; // Object
$parents = $eduMat->getParentEducationalMaterials();

echo json_encode($parents);