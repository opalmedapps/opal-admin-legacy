<?php
header('Content-Type: application/javascript');
/* To get a list of phase in treatments */
include_once('educational-material.inc');

$eduMat = new EduMaterial; // Object
$phases = $eduMat->getPhasesInTreatment();

echo json_encode($phases);