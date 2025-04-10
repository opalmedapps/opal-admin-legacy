<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
include_once('notification.inc');

$notification = new Notification; // Object
$existingNotificationList = $notification->getNotifications();

echo json_encode($existingNotificationList);
