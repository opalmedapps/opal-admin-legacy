<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$serials = json_decode(strip_tags($_POST['serials']));
$type = ( strip_tags($_POST['type']) === 'undefined' ) ? null : strip_tags($_POST['type']);
$post = new Post($OAUserId); // Object
$postLogs = $post->getPostListLogs($serials, $type);

echo json_encode($postLogs);