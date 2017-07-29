<?php

include_once('questionnaire.inc');

$callback = $_GET['callback'];

$tag = new Tag();

$tagList = $tag->getTags();

// Callback to http request
print $callback.'('.json_encode($tagList).')';
?>