<?php
include_once "database.inc";


class DelayedLabPushNotification
{
    /**
     * Fetch lab results grouped by patient for creating notification records and sending push notifications
     * Mark notification as read if at least one lab result is marked as read
     * 
     * @return array of delayed lab results grouped by patients
     */
    public static function fetchDelayedLabResults()
    {
        global $pdo;

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

        $notifControlSerNum = $notifControl[0]["NotificationControlSerNum"];
        $notifControlNameEN = $notifControl[0]["Name_EN"];
        $notifControlNameFR = $notifControl[0]["Name_FR"];
        $notifControlDescEN = $notifControl[0]["Description_EN"];
        $notifControlDescFR = $notifControl[0]["Description_FR"];

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
                    CONCAT(
                        '[',
                        GROUP_CONCAT(TRIM(TRAILING ']' FROM TRIM(leading '[' from ptr.ReadBy))),
                        ']'
                    ) AS ReadBy
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
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo json_encode(["success" => 0, "failure" => 1, "error" => $e]) . PHP_EOL;
            exit();
        }

        return $result;
    }

    public static function createNotificationsForDelayedLabs($delayedLabs)
    {
        global $pdo;

        if (!$delayedLabs) {
            $errMsg = "An error occurred while creating notifications for delayed lab results.";
            $errMsg .= "\nThe array of delayed labs is empty.\n";
            echo json_encode(["success" => 0, "failure" => 1, "error" => $errMsg]) . PHP_EOL;
            exit();
        }

        // Extract from the $delayedLabs associative array the keys and values into separate arrays.
        // The arrays are used in queries below where the keys are the columns and the values are new entries.
        $delayedLabsArray = array_values($delayedLabs);
        $columns = implode(", ",array_keys($delayedLabs[0]));
        $values = implode("', '", $delayedLabsArray[0]);

        $query = "
            INSERT INTO Notification($columns) VALUES ('$values');
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
