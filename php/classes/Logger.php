<?php

class Logger extends OpalProject
{
    public $username = null;
    public $userId = null;
    public $activityType = null;
    public $alertType = null;
    public $patientModifiedSerNum = null;


    /**
     * Insert Into Audit Logs table
     *
     * @param string $message
     * @param string $username
     * @param string $userId
     * @param string $activityType
     * @param string $time
     * @param string $patientModifiedSerNum
     * @return void
     */
    public function insertAuditLogs($message, $username = null, $userId, $activityType = null, $dateTime = null, $date = null, $currentTime = null, $patientModifiedSerNum = null)
    {
        array_push($toInsert, array(
            'userId' => $userId,
            'username' => $username,
            'activityType' => $activityType,
            'messageType' => $message,
            'dateTime' => $dateTime,
            'date' => $date,
            'time' => $currentTime,
            'patientModifiedSerNum' => $patientModifiedSerNum
        ));
        $response = array(
            'value' => 0,
            'message' => ''
        );

        try {

            $this->opalDB->insertIntoAuditLogsTable($toInsert);
            $response['value'] = 1; // success
            return $response;

        } catch (PDOException $e) {
            return $e->getMessage();
        }

    }
}
