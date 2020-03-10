<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$OAUSerID = strip_tags($_POST["OAUserId"]);
$serial = strip_tags($_POST["serial"]);
$type = strip_tags($_POST["type"]);

$post = new Post($OAUSerID);
$postLogs = $post->getPostChartLogs($serial, $type);

echo json_encode($postLogs);