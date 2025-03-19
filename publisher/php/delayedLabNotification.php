<?php
include_once "database.inc";

require_once('customPushNotification.php');


class DelayedLabNotification
{
    /**
     * Create in app notification records for delayed lab results available between NOW() and NOW() - 2 hours.
     * 
     * The notification is marked as read if at least one lab result is marked as read
     * (assumes that patient went to the Chart->Lab Results page, so the notification should be marked read).
     */
    public static function createInAppNotifications()
    {
        global $pdo;

        $notifControl = self::fetchLabResultsNotificationControl();

        $notifControlSerNum = $notifControl["NotificationControlSerNum"];
        $notifControlNameEN = $notifControl["Name_EN"];
        $notifControlNameFR = $notifControl["Name_FR"];
        $notifControlDescEN = $notifControl["Description_EN"];
        $notifControlDescFR = $notifControl["Description_FR"];

        // Fetch patient for whom in app notifications should be created
        $labsQuery = "
            SELECT
                res.PatientSerNum AS PatientSerNum,
                :notifControlSerNum AS NotificationControlSerNum,
                -1 AS RefTableRowSerNum,
                NOW() AS DateAdded,
                0 AS ReadStatus,
                res.ReadBy AS ReadBy,
                :notifControlNameEN AS RefTableRowTitle_EN,
                :notifControlNameFR AS RefTableRowTitle_FR
            FROM
            (
                -- Group read lab results by PatientSerNum
                SELECT
                    ptr.PatientSerNum AS PatientSerNum,
                    ptr.ReadBy
                FROM PatientTestResult ptr
                -- fetch the delayed labs that are available between NOW() and NOW() - 2 HOURS
                -- the time range should be set in accordance with the cronjob (please see docker/crontab)
                WHERE ptr.AvailableAt >= NOW() - INTERVAL 2 HOUR AND ptr.AvailableAt <= NOW()
                -- fetch only the delayed labs (the regular ones should be ignored)
                AND ptr.TestExpressionSerNum IN (
                    SELECT
                        TE.TestExpressionSerNum
                    FROM TestExpression TE
                    LEFT JOIN TestControl TC ON TC.TestControlSerNum = TE.TestControlSerNum
                    WHERE TC.InterpretationRecommended = 1
                )
                AND ptr.ReadBy NOT LIKE '[]'
                GROUP BY ptr.PatientSerNum
                UNION
                -- Group unread lab results by PatientSerNum
                SELECT
                    ptr.PatientSerNum AS PatientSerNum,
                    ptr.ReadBy AS ReadBy
                FROM PatientTestResult ptr
                -- fetch the delayed labs that are available between NOW() and NOW() - 2 HOURS
                -- the time range should be set in accordance with the cronjob (please see docker/crontab)
                WHERE ptr.AvailableAt >= NOW() - INTERVAL 2 HOUR AND ptr.AvailableAt <= NOW()
                -- fetch only the delayed labs (the regular ones should be ignored)
                AND ptr.TestExpressionSerNum IN (
                    SELECT
                        TE.TestExpressionSerNum
                    FROM TestExpression TE
                    LEFT JOIN TestControl TC ON TC.TestControlSerNum = TE.TestControlSerNum
                    WHERE TC.InterpretationRecommended = 1
                )
                AND ptr.ReadBy LIKE '[]'
                GROUP BY ptr.PatientSerNum
            ) res
            GROUP BY res.PatientSerNum;
        ";

        try {
            $statement = $pdo->prepare($labsQuery);
            $statement->execute([
                ":notifControlSerNum"   => $notifControlSerNum,
                ":notifControlNameEN"   => $notifControlNameEN,
                ":notifControlNameFR"   => $notifControlNameFR,
            ]);
            $delayedLabs = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo json_encode(["success" => 0, "failure" => 1, "error" => $e]) . PHP_EOL;
            exit();
        }

        // Create notification records.
        // The notification is marked as read if at least one lab result is marked as read.
        // This is needed due to UI business rule:
        // if user opens up the main "Lab Results" page ALL lab results' in app notifications should be marked as read.
        if ($delayedLabs) {
            // Extract from the $delayedLabs associative array the keys and values into separate arrays.
            // The arrays are used in queries below where the keys are the columns and the values are new entries.
            $columns = implode(", ", array_keys($delayedLabs[0]));
            $values = [];
            foreach ($delayedLabs as $lab) {
                $values[] = "('" . implode("', '", $lab) . "')";
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
    }

    /**
     * Send push notifications for the released delayed lab results available between NOW() and NOW() - 2 hours.
     * 
     * User will not receive a push notification if they have already read all the delayed labs.
     */
    public static function sendPushNotifications() {
        global $pdo;

        $notifControl = self::fetchLabResultsNotificationControl();
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
                ) AS ReadBy
            FROM PatientTestResult PTR
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

        // Send notifications in loop per patient (e.g., self-caregiver patient plus related caregivers);
        // Ensure that push notifications are not sent to the users who have already fully read the delayed lab results.
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
                "title_EN" => $notifControlNameEN,
                "message_text_EN" => "",
                "title_FR" => $notifControlNameFR,
                "message_text_FR" => "",
            );
            // Call API to send push notification
            $response = customPushNotification::sendNotificationByPatientSerNum($patientSerNum, $language, $messages);
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
