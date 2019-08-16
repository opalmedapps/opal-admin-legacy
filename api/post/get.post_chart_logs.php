<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$type = ( strip_tags($_POST['type']) === 'undefined' ) ? null : strip_tags($_POST['type']);
$post = new Post; // Object
$postLogs = $post->getPostChartLogs($serial, $type);

echo json_encode($postLogs);