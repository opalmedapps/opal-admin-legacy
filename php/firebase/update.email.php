<?php

header('Content-Type: application/javascript');
include_once("firebase.inc");

$myFirebase = new Firebase();
//'b0tEHXqDqwN9s7qKQdX1SqdTIQm1', "testpassword1234!"

$myFirebase->updateEmail($_POST);
http_response_code(HTTP_STATUS_SUCCESS);