<?php

	include_once("questionnaire.inc");

	$name_EN = $_POST['name_EN'];
	$name_FR = $_POST['name_FR'];
	$level = $_POST['level'];
	$last_updated_by = $_POST['last_updated_by'];
	$created_by = $_POST['created_by'];

	//Questiongroup array
	$tagArray = array(
		'name_EN'			=> $name_EN,
		'name_FR'			=> $name_FR,
		'level'				=> $level,
		'last_updated_by'	=> $last_updated_by,
		'created_by'		=> $created_by
	);

	$tag = new Tag;

	$tag->addTag($tagArray);

?>