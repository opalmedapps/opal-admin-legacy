<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$serials = json_decode(strip_tags($_POST['serials']));
$type = ( strip_tags($_POST['type']) === 'undefined' ) ? null : strip_tags($_POST['type']);

$post = new Post(); // Object
$postLogs = $post->getPostListLogs($serials, $type);

echo json_encode($postLogs);