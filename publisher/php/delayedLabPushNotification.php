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

        $query = "
            SELECT
            res.PatientSerNum AS PatientSerNum,
            (
                SELECT
                    ntc.NotificationControlSerNum
                FROM NotificationControl ntc
                WHERE ntc.NotificationType = 'NewLabResult'
            ) AS NotificationControlSerNum,
            -1 AS RefTableRowSerNum,
            NOW() AS DateAdded,
            res.ReadBy AS ReadBy,
            'New Lab Result' AS RefTableRowTitle_EN,
            'Nouveau résultat de laboratoire' AS RefTableRowTitle_FR
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
                WHERE DATE(ptr.AvailableAt) = CURDATE()
                AND ptr.ReadBy NOT LIKE '[]'
                GROUP BY ptr.PatientSerNum
                UNION
                -- Group unread lab results by PatientSerNum
                SELECT
                    ptr.PatientSerNum AS PatientSerNum,
                    ptr.ReadBy AS ReadBy
                FROM PatientTestResult ptr
                WHERE DATE(ptr.AvailableAt) = CURDATE()
                AND ptr.ReadBy LIKE '[]'
                GROUP BY ptr.PatientSerNum
            ) res
            GROUP BY res.PatientSerNum;
        ";

        try {
            $statement = $pdo->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll();
        } catch (PDOException $e) {
            echo json_encode(["success" => 0, "failure" => 1, "error" => $e]) . PHP_EOL;
            exit();
        }

        return $result;
    }

    public static function createNotificationsForDelayedLabs($delayedLabs)
    {
        global $pdo;

        $delayedLabsArray = array_values($delayedLabs);
        $columns = implode(", ",array_keys($delayedLabs));
        $values = implode("', '", $delayedLabsArray);

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
