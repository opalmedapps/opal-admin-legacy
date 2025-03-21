<?php

// SPDX-FileCopyrightText: Copyright (C) 2024 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once "database.inc";

require_once('CustomPushNotification.php');


class DelayedLabNotification
{
    /**
     * Create in app notification records for delayed lab results available between NOW() and NOW() - 2 hours.
     *
     * The notification is marked as read if at least one lab result is marked as read
     * (assumes that patient went to the Chart->Lab Results page, so the notification should be marked read).
     *
     * Also, send push notifications for the released delayed lab results available between NOW() and NOW() - 2 hours.
     *
     * User will not receive a push notification if they have already read all the delayed labs.
     */
    public static function createAndSendNotifications() {
        global $pdo;

        $notifControl = self::fetchLabResultsNotificationControl();

        $notifControlSerNum = $notifControl["NotificationControlSerNum"];
        $notifControlNameEN = $notifControl["Name_EN"];
        $notifControlNameFR = $notifControl["Name_FR"];

        // Fetch the number of (delayed) lab results that are available now and the union of ReadBy of all of them.
        $delayedLabsQuery = "
            SELECT
                PTR.PatientSerNum AS PatientSerNum,
                COUNT(PTR.PatientSerNum) AS NumLabResults,
                CONCAT(
                    '[',
                    IFNULL(GROUP_CONCAT(IF(PTR.ReadBy='[]', NULL, SUBSTRING(PTR.ReadBy, 2, LENGTH(PTR.ReadBy) - 2))), ''),
                    ']'
                ) AS ReadBy,
                P.Language AS Language
            FROM PatientTestResult PTR
            LEFT JOIN Patient P ON P.PatientSerNum = PTR.PatientSerNum
            -- fetch the delayed labs that are available between NOW() and NOW() - 2 HOURS
            -- the time range should be set in accordance with the cronjob (please see docker/crontab)
            WHERE PTR.AvailableAt >= NOW() - INTERVAL 2 HOUR AND PTR.AvailableAt <= NOW()
            AND PTR.TestExpressionSerNum IN (
                SELECT
                    TE.TestExpressionSerNum
                FROM TestExpression TE
                INNER JOIN TestControl TC ON TC.TestControlSerNum = TE.TestControlSerNum
                WHERE TC.InterpretationRecommended = 1
            )
            GROUP BY PTR.PatientSerNum;
        ";

        try {
            $statement = $pdo->prepare($delayedLabsQuery);
            $statement->execute();
            $delayedLabs = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo json_encode(["success" => 0, "failure" => 1, "error" => $e]) . PHP_EOL;
            exit();
        }

        if ($delayedLabs) {
            self::createInAppNotifications(
                $delayedLabs,
                $notifControlSerNum,
                $notifControlNameEN,
                $notifControlNameFR,
            );

            self::sendPushNotifications(
                $delayedLabs,
                $notifControlNameEN,
                $notifControlNameFR,
            );
        }
    }

    /**
     * Create in app notification records for delayed lab results available between NOW() and NOW() - 2 hours.
     *
     * The notification is marked as read if at least one lab result is marked as read
     * (assumes that patient went to the Chart->Lab Results page, so the notification should be marked read).
     *
     * @param array{[PatientSerNum: int, NumLabResults: int, ReadBy: string, Language: string]} $delayedLabs
     *        delayed lab results grouped by PatientSerNum
     * @param int $notifControlSerNum - serial number of the lab results notification control
     * @param string $notifControlNameEN - lab results notification control title in English
     * @param string $notifControlNameFR - lab results notification control title in French
     */
    protected static function createInAppNotifications(
        $delayedLabs,
        $notifControlSerNum,
        $notifControlNameEN,
        $notifControlNameFR,
    )
    {
        global $pdo;

        // Create notification records.
        // The notification is marked as read if at least one lab result is marked as read.
        // This is needed due to UI business rule:
        // if user opens up the main "Lab Results" page ALL lab results' in app notifications should be marked as read.
        foreach ($delayedLabs as &$lab) {
            // Remove duplicates in ReadBy
            $readBy = json_decode($lab['ReadBy']);
            $readBy = array_unique($readBy);
            $lab["ReadBy"] = json_encode($readBy);
            // Delete NumLabResults and Language fields
            unset($lab['NumLabResults']);
            unset($lab['Language']);

            // Add fields required for creating a notification record
            $lab["NotificationControlSerNum"] = $notifControlSerNum;
            $lab["RefTableRowSerNum"] = -1;
            $date = new \DateTime('now');
            $lab["DateAdded"] = $date->format('Y-m-d H:i:s');
            $lab["ReadStatus"] = 0;
            $lab["RefTableRowTitle_EN"] = $notifControlNameEN;
            $lab["RefTableRowTitle_FR"] = $notifControlNameFR;
        }

        // Extract from the $delayedLabs associative array the keys and values into separate arrays.
        // The arrays are used in queries below where the keys are the columns and the values are new entries.
        // Note, the columns should contain:
        //         "PatientSerNum",
        //         "NotificationControlSerNum",
        //         "DateAdded",
        //         "ReadStatus",
        //         "ReadBy",
        //         "RefTableRowTitle_EN",
        //         "RefTableRowTitle_FR"
        $columns = implode(", ", array_keys($delayedLabs[0]));
        $values = [];

        foreach ($delayedLabs as $labResult) {
            $values[] = "('" . implode("', '", $labResult) . "')";
        }

        $values = implode(',', $values);

        $query = "
            INSERT INTO Notification ($columns) VALUES $values;
        ";

        try {
            $statement = $pdo->prepare($query);
            $statement->execute();
        } catch (PDOException $e) {
            echo json_encode(["success" => 0, "failure" => 1, "error" => $e]) . PHP_EOL;
            exit();
        }
    }

    /**
     * Send notifications in loop per patient (e.g., self-caregiver patient plus related caregivers).
     *
     * Ensure that push notifications are not sent to the users who have already fully read the delayed lab results.
     *
     * @param string $notifControlNameEN - lab results notification control title in English
     * @param string $notifControlNameFR - lab results notification control title in French
     */
    protected static function sendPushNotifications(
        $delayedLabs,
        $notifControlNameEN,
        $notifControlNameFR,
    ) {
        foreach ($delayedLabs as $lab) {
            // Create an array of users who have already read the delayed lab results per patient.
            // Note that array contains the username duplicates that indicate how many delayed labs user has read.
            // E.g., ["QXmz5ANVN3Qp9ktMlqm2tJ2YYBz2", "SipDLZCcOyTYj7O3C8HnWLalb4G3", "QXmz5ANVN3Qp9ktMlqm2tJ2YYBz2"]
            $usersReadsPerPatient = json_decode($lab['ReadBy']);
            
            // Create a dictionary that contains read counts per user for a specific patient
            $readCountsPerUser = array_count_values($usersReadsPerPatient);

            // Create an array for storing usernames that should be ignored
            // (e.g., caregivers who have fully read the delayed labs)
            $ignoredUsernames = array_filter(
                $readCountsPerUser,
                fn($readCounts) => $readCounts === $lab["NumLabResults"],
            );

            $messages = array(
                "title_en" => $notifControlNameEN,
                "message_text_en" => "",
                "title_fr" => $notifControlNameFR,
                "message_text_fr" => "",
            );
            // Call API to send push notification
            $response = CustomPushNotification::sendNotificationByPatientSerNum(
                $lab["PatientSerNum"],
                $messages,
                $ignoredUsernames,
            );
            echo json_encode($response) . PHP_EOL;
        }
    }

    /**
     * Get an associative array for lab results notification control that contains type name and description
     * @return array notification control for lab results
     */
    protected static function fetchLabResultsNotificationControl() {
        global $pdo;

        // Fetch notification control SerNum and description
        $notificationControlQuery = "
            SELECT
                ntc.NotificationControlSerNum as NotificationControlSerNum,
                ntc.Name_EN AS Name_EN,
                ntc.Name_FR AS Name_FR,
                ntc.Description_EN AS Description_EN,
                ntc.Description_FR AS Description_FR
            FROM NotificationControl ntc
            WHERE ntc.NotificationType = 'NewLabResult';
        ";

        try {
            $statement = $pdo->prepare($notificationControlQuery);
            $statement->execute();
            $notifControl = $statement->fetchAll();
        } catch (PDOException $e) {
            echo json_encode(["success" => 0, "failure" => 1, "error" => $e]) . PHP_EOL;
            exit();
        }

        if (count($notifControl) !== 1)
        {
            $result = [
                "success" => 0,
                "failure" => 1,
                "error" => "An error occurred while fetching 'NewLabResult' NotificationType from NotificationControl.",
            ];
            echo json_encode($result) . PHP_EOL;
            exit();
        }

        return $notifControl[0];
    }
}
