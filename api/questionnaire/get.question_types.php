<?php
	header('Content-Type: application/javascript');
  /* To get a list of answer types */
  include_once('questionnaire.inc');

  // Retrieve FORM params
  $callback = $_GET['callback'];
  $userid = strip_tags($_GET['userid']);

  $questionType = new QuestionType($userid); // Object

  // Call function
  $questionTypeList = $questionType->getQuestionTypes();

  // Callback to http request
  print $callback.'('.json_encode($questionTypeList).')';
?>
