<?php
include_once "database.inc";


class ApptReminderPushNotification
{
    /**
     * Gets a list of all appointments of patient on the next day
     * @return array of appointments
     */
    public static function getNextDayAppointments()
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
