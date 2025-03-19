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

        if (count($notifControl) > 0)
        {
            $notifControlSerNum = $notifControl[0]["NotificationControlSerNum"];
            $notifControlNameEN = $notifControl[0]["Name_EN"];
            $notifControlNameFR = $notifControl[0]["Name_FR"];
            $notifControlDescEN = $notifControl[0]["Description_EN"];
            $notifControlDescFR = $notifControl[0]["Description_FR"];
        }
        else {
            $notifControlSerNum = 2;
            $notifControlNameEN = 'New Lab Result';
            $notifControlNameFR = 'Nouveau résultat de laboratoire';
            $notifControlDescEN = '$patientName: New lab test result';
            $notifControlDescFR = '$patientName: Nouveau résultat de test de laboratoire';
        }

        $labsQuery = "
            SELECT
            res.PatientSerNum AS PatientSerNum,
            :notifControlSerNum AS NotificationControlSerNum,
            -1 AS RefTableRowSerNum,
            NOW() AS DateAdded,
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
            $statement = $pdo->prepare($labsQuery);
            $statement->execute([
                ":notifControlSerNum"   => $notifControlSerNum,
                ":notifControlNameEN"   => $notifControlNameEN,
                ":notifControlNameFR"   => $notifControlNameFR,
            ]);
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
