<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$serial = strip_tags($_POST['serial']);
$post = new Post; // Object
$postDetails = $post->getPostDetails($serial);

echo json_encode($postDetails);