<?php
include_once "database.inc";


class NextDayAppointmentNotification
{
    /**
     * TODO: Figure out if in-app notification should be created along with the push notification.
     *
     * Send push notifications reminders for the patients who have an appointment the following day.
     */
    public static function createAndSendNotifications() {
        // TODO: Figure out if we need to create Notification records

        // Step 1: Gets a list of all appointments of patient on the next day
        $result = self::getNextDayAppointments();
        if (array_key_exists("failure", $result) && $result["failure"] == 1) {
            echo json_encode($result) . PHP_EOL;
            exit();
        }
        // Step 2: Prepare the Push Notification message
        foreach ($result as $row) {
            $patientSerNum = $row["PatientSerNum"];
            $language = $row["Language"];
            $hospitalEN = "MUHC";
            $hospitalFR = "CUSM";
            $titleEN = "Opal Appointment Reminder";
            $titleFR = "Rappel de rendez-vous d'Opal";
            $appointmentDate = $row["Date"];
            $appointmentTime = $row["Time"];
            $appointmentAliasEN = $row["AliasName_EN"];
            $appointmentAliasFR = $row["AliasName_FR"];

            if ($language == "EN") {
                $messageTextEN = "Opal reminder for an appointment at the "
                    . $hospitalEN . ": " . $appointmentAliasEN
                    . " on " . $appointmentDate . " at " . $appointmentTime;
            } else {
                $messageTextFR = "Opal rappel pour un rendez-vous au "
                    . $hospitalFR . ": " . $appointmentAliasFR
                    . " le " . $appointmentDate . " à " . $appointmentTime;
            }

            $messages = array(
                "title_EN" => $titleEN,
                "message_text_EN" => $messageTextEN,
                "title_FR" => $titleFR,
                "message_text_FR" => $messageTextFR
            );

            // Call API to send push notification
            $response = CustomPushNotification::sendNotificationByPatientSerNum(
                $patientSerNum,
                $language,
                $messages,
            );
            echo json_encode($response) . PHP_EOL;
        }
    }

    /**
     * Gets a list of all appointments of patient on the next day
     * @return array of appointments
     */
    protected static function getNextDayAppointments()
    {
        global $pdo;

        // Get the list of appointments for the next day
        $sql = "select AP.PatientSerNum,
                        AP.`Language`,
                        AP.`Date`,
                        AP.`Time`,
                        ALIAS.AliasName_FR,
                        ALIAS.AliasName_EN
                    from (SELECT
                            ap.PatientSerNum,
                            P.`Language`,
                            ap.AliasExpressionSerNum,
                            DATE_FORMAT(ap.ScheduledStartTime, '%Y-%m-%d') `Date`,
                            DATE_FORMAT(ap.ScheduledStartTime, '%H:%i') `Time`,
                            ap.SourceDatabaseSerNum
                        FROM
                            Appointment ap, Patient P
                        WHERE 
                            DATE(ap.ScheduledStartTime) = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))
                            AND TIME(ap.ScheduledStartTime) >= '02:00'
                            AND ap.State = 'Active' 
                            AND ap.Status <> 'Deleted' 
                            AND ap.PatientSerNum = P.PatientSerNum) AS AP,
                    
                        (SELECT A.AliasName_FR, A.AliasName_EN, AE.AliasExpressionSerNum
                        FROM Alias A, AliasExpression AE
                        WHERE A.AliasSerNum = AE.AliasSerNum) AS ALIAS
                    
                    where AP.AliasExpressionSerNum = ALIAS.AliasExpressionSerNum
                    Order by AP.PatientSerNum, AP.`Time`
                ;    
            ";

        try {
            $s = $pdo->prepare($sql);
            $s->execute();
            $result = $s->fetchAll();
        } catch (PDOException $e) {
            return array("success" => 0, "failure" => 1, "error" => $e);
            exit();
        }

        if (count($result) == 0) {
            return array("success" => 0, "failure" => 1, "error" => "No matching appointments in Database");
            exit();
        }
        return $result;
    }
}
