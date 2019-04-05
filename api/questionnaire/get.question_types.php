<?php
	header('Content-Type: application/javascript');
  /* To get a list of answer types */
  include_once('questionnaire.inc');

  // Retrieve FORM params
  $callback = $_GET['callback'];
  $userid = $_GET['userid'];

  $answerType = new QuestionType(); // Object

  // Call function
  $answerTypeList = $answerType->getQuestionTypes($userid);

  // Callback to http request
  print $callback.'('.json_encode($answerTypeList).')';
?>
