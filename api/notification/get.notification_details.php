<?php
header('Content-Type: application/javascript');
/* To get details on a particular notification */
include_once('notification.inc');

$serial = strip_tags($_POST['serial']);
$notification = new Notification; // Object
$notificationDetails = $notification->getNotificationDetails($serial);

echo json_encode($notificationDetails);