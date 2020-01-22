<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$OAUSerID = strip_tags($_POST["OAUserId"]);
$post = new Post($OAUSerID);
$postLogs = $post->getPostChartLogs($_POST);

echo json_encode($postLogs);