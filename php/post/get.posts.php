<?php
include_once("../config.php");

$post = new Post(); // Object
$existingPostList = $post->getPosts();

header('Content-Type: application/javascript');
echo json_encode($existingPostList);