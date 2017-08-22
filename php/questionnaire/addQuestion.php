<?php
	include_once('questionnaire.inc');

	$text_EN = $_POST['text_EN'];
	$text_FR = $_POST['text_FR'];
	$answertype_serNum = $_POST['answertype_serNum'];
	$questiongroup_serNum = $_POST['questiongroup_serNum'];
	$last_updated_by = $_POST['last_updated_by'];
	$created_by = $_POST['created_by'];

	//question array
	$questionArray = array(
		'text_EN'				=> $text_EN,
		'text_FR'				=> $text_FR,
		'answertype_serNum'		=> $answertype_serNum,
		'questiongroup_serNum'	=> $questiongroup_serNum,
		'last_updated_by'		=> $last_updated_by,
		'created_by'			=> $created_by
	);

	$questionObj = new Question;

	$questionObj->addQuestion($questionArray);
?>