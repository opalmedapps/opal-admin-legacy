<?php
include_once("../config.php");

$diagnosis = new Diagnosis();
$diagnosisTranslationDetails = array(
	'name_EN'					=> $_POST['name_EN'],
	'name_FR'					=> $_POST['name_FR'],
	'description_EN'	=> filter_var($_POST['description_EN'], FILTER_SANITIZE_ADD_SLASHES),
	'description_FR'	=> filter_var($_POST['description_FR'], FILTER_SANITIZE_ADD_SLASHES),
	'edumatser'				=> $_POST['eduMatSer'],
	'serial'					=> $_POST['serial'],
	'diagnoses'				=> $_POST['diagnoses'],
	'user'						=> $_POST['user'],
	'details_updated'	=> $_POST['details_updated'],
	'codes_updated'		=> $_POST['codes_updated']
);

$response = $diagnosis->updateDiagnosisTranslation($diagnosisTranslationDetails);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);