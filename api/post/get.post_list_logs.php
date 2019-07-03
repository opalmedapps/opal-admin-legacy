<?php

header('Content-Type: application/javascript');
include_once('post.inc');
$serials = json_decode($_POST['serials']);
$type = ( $_POST['type'] === 'undefined' ) ? null : $_POST['type'];
$post = new Post; // Object
$postLogs = $post->getPostListLogs($serials, $type);

echo json_encode($postLogs);