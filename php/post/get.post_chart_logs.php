<?php
include_once("../config.php");

$serial = strip_tags($_POST["serial"]);
$type = strip_tags($_POST["type"]);

$post = new Post();
$postLogs = $post->getPostChartLogs($serial, $type);

header('Content-Type: application/javascript');
echo json_encode($postLogs);