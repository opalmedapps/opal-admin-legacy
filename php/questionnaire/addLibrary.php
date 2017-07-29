<?php
	
	include_once('questionnaire.inc');
	
	$name_EN = $_POST['name_EN'];
	$name_FR = $_POST['name_FR'];
	$private = $_POST['private'];
	$created_by = $_POST['created_by'];
	$last_updated_by = $_POST['last_updated_by'];

	$libraryArray = array(
		'name_EN'			=> $name_EN,
		'name_FR'			=> $name_FR,
		'private'			=> $private,
		'created_by'		=> $created_by,
		'last_updated_by'	=> $last_updated_by
	);

	$libraryObj = new Library;

	$libraryObj->addLibrary($libraryArray);
?>