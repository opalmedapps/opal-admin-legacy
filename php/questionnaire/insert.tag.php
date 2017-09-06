<?php

	/* Inserts a newly-created tag */
	include_once("questionnaire.inc");

	// Construct array from FORM params
	$tagArray = array(
		'name_EN'			=> $_POST['name_EN'],
		'name_FR'			=> $_POST['name_FR'],
		'level'				=> $_POST['level'],
		'last_updated_by'	=> $_POST['last_updated_by'],
		'created_by'		=> $_POST['created_by']
	);

	$tag = new Tag; // Object

	// Call function
	$tag->insertTag($tagArray);

?>