<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get list logs on a particular notification */
include_once('notification.inc');

$serials = json_decode($_POST['serials']);
$notification = new Notification; // Object
$notificationLogs = $notification->getNotificationListLogs($serials);

echo json_encode($notificationLogs);
