<?php

// SPDX-FileCopyrightText: Copyright (C) 2024 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

// Find delayed lab results and trigger sending off push notifications.
// Only send one notification irregardless of the total number of lab results

require_once('DelayedLabNotification.php');


// Create in app notifications for the delayed lab results that are available between NOW() and NOW() - 2 hours
// Send push notifications for the released delayed lab results that are available between NOW() and NOW() - 2 hours
DelayedLabNotification::createAndSendNotifications();
