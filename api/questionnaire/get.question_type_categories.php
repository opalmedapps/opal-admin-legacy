<?php
	header('Content-Type: application/javascript');
  /* To get a list of distinct answer type categories */
  include_once('questionnaire.inc');

  // Retrieve form param
  $callback = $_GET['callback'];

  $answerType = new QuestionType(); // Object

  // Call function
  $answerTypeCategoryList = $answerType->getQuestionTypeCategories();

  // Callback to http request
  print $callback.'('.json_encode($answerTypeCategoryList).')';
?>
