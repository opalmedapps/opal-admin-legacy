<?php
header('Content-Type: application/javascript');
/* To get details on a particular educational material */
include_once('educational-material.inc');

$serial = strip_tags($_POST['serial']);
$eduMat = new EduMaterial; // Object
$eduMatDetails = $eduMat->getEducationalMaterialDetails($serial);

echo json_encode($eduMatDetails);