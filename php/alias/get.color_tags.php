<?php
/* To get a list of existing color tags */
include_once('alias.inc');

$type = strip_tags($_POST['type']);
$alias = new Alias; // Object
$colorTags = $alias->getColorTags($type);

header('Content-Type: application/javascript');
echo json_encode($colorTags);