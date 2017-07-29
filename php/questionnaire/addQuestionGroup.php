<?php

	include_once("questionnaire.inc");

	$name_EN = $_POST['name_EN'];
	$name_FR = $_POST['name_FR'];
	$category_EN = $_POST['category_EN'];
	$category_FR = $_POST['category_FR'];
	$private = $_POST['private'];
	$last_updated_by = $_POST['last_updated_by'];
	$created_by = $_POST['created_by'];
	$library_serNum = $_POST['library_serNum'];

	//Questiongroup array
	$groupArray = array(
		'name_EN'			=> $name_EN,
		'name_FR'			=> $name_FR,
		'category_EN'		=> $category_EN,
		'category_FR'		=> $category_FR,
		'private'			=> $private,
		'created_by'		=> $created_by,
		'last_updated_by'	=> $last_updated_by,
		'library_serNum'	=> $library_serNum
	);

	$groupObj = new Group;

	$groupObj->addQuestionGroup($groupArray);

?>