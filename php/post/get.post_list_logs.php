<?php
include_once("../config.php");

$serials = json_decode(strip_tags($_POST['serials']));
$type = ( strip_tags($_POST['type']) === 'undefined' ) ? null : strip_tags($_POST['type']);

$post = new Post(); // Object
$postLogs = $post->getPostListLogs($serials, $type);

header('Content-Type: application/javascript');
echo json_encode($postLogs);