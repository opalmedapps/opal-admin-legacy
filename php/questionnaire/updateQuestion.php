<?php
	include_once('questionnaire.inc');

	$serNum = $_POST['serNum'];
	$text_EN = $_POST['text_EN'];
	$text_FR = $_POST['text_FR'];
	$answertype_serNum = $_POST['answertype_serNum'];
	$questiongroup_serNum = $_POST['questiongroup_serNum'];
	$last_updated_by = $_POST['last_updated_by'];
	

	//question array
	$questionArray = array(
		'serNum'				=> $serNum,
		'text_EN'				=> $text_EN,
		'text_FR'				=> $text_FR,
		'answertype_serNum'		=> $answertype_serNum,
		'questiongroup_serNum'	=> $questiongroup_serNum,
		'last_updated_by'		=> $last_updated_by
	);

	$questionObj = new Question;

	$response = $questionObj->updateQuestion($questionArray);
	print json_encode($response);
?>