<?php
header('Content-Type: application/javascript');

include_once('notification.inc');

$notification = new Notification; // Object
$types = $notification->getNotificationTypes();

echo json_encode($types);