<?php
	header('Content-Type: application/javascript');
  /* To get a list of distinct answer type categories */
  include_once('questionnaire.inc');

  // Retrieve form param
  $callback = strip_tags($_GET['callback']);
  $userId = strip_tags($_GET['userId']);

  $answerType = new QuestionType($userId); // Object

  // Call function
  $answerTypeCategoryList = $answerType->getQuestionTypeList();

  // Callback to http request
  print $callback.'('.json_encode($answerTypeCategoryList).')';
?>
