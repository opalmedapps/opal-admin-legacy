<?php
    include_once "database.inc";
    require_once('PushNotifications.php');


   class apptReminderPushNotification{

       /**
       * ==============================================================================
       *            API CALL FUNCTIONS
       * ==============================================================================
       **/

      /**
        *    sendPatientNotification($patientSerNum, $language, $messages):
        *    Requires: - PatientSerNum, language, and message. The message is in an array.
        *    Returns:  Object containing keys of success, failure,
        *             responseDevices, which is an array containing, (success, failure,
        *             registrationId, deviceId) for each device, and Message array containing
        *             (title, description),  NotificationSerNum, and error if any.
        **/
        public static function sendPatientNotification($patientSerNum, $language, $messages){
            //Obtain patient device identifiers
            $patientDevices = self::getDevicesForPatient($patientSerNum);

            //If no identifiers return there are no identifiers
            if(count($patientDevices)==0){
                return array("success"=>0, "failure"=>1,"responseDevices"=>"No patient devices available for that patient");
                exit();
            }

            if ($language == "EN"){
                $wsmtitle = $messages['title_EN'];
                $wsmdesc = $messages["message_text_EN"];
            }else{
                $wsmtitle = $messages['title_FR'];
                $wsmdesc = $messages["message_text_FR"];
            }

            // Need this format for PushNotifications functions
            $messageBody = array(
                "mtitle"=> $wsmtitle,
                "mdesc"=> $wsmdesc,
                "encode"=> 'Yes'
            );

            //Send message to patient devices and record in database
            $resultsArray = array();
            foreach($patientDevices as $device){
                //Determine device type
                if($device["DeviceType"]==0){
                    $response = PushNotifications::iOS($messageBody, $device["RegistrationId"]);
                }else if($device["DeviceType"]==1){
                    $response = PushNotifications::android($messageBody, $device["RegistrationId"]);
                }

                //Log result of push notification on database.
                self::logCustomerPushNotification($device["PatientDeviceIdentifierSerNum"], $patientSerNum, $wsmtitle, $wsmdesc, $response);

                //Build response
                $response["DeviceType"] = $device["DeviceType"];
                $response["RegistrationId"] = $device["RegistrationId"];
                $resultsArray[] = $response;
            }

            return array("success"=>1,"failure"=>0,"responseDevices"=>$resultsArray,"message"=>$messages);
       }

        /**
         * Gets a list of all appointments of patient on the next day
         * @return array of appointments
         */
        public static function getNextDayAppointments(){

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
                            AND ap.State = 'Active' 
                            AND ap.Status <> 'Deleted' 
                            AND ap.PatientSerNum = P.PatientSerNum) AS AP,
                    
                        (SELECT A.AliasName_FR, A.AliasName_EN, AE.AliasExpressionSerNum
                        FROM Alias A, AliasExpression AE
                        WHERE A.AliasSerNum = AE.AliasSerNum) AS ALIAS
                    
                    where AP.AliasExpressionSerNum = ALIAS.AliasExpressionSerNum
                ;    
            ";

            try{
                $s = $pdo->prepare($sql);
                $s->execute();
                $result = $s->fetchAll();
            }catch(PDOException $e){
                return array("success"=>0,"failure"=>1,"error"=>$e);
                exit();
            }

            if(count($result)==0){
                return array("success"=>0,"failure"=>1,"error"=>"No matching appointments in Database");
                exit();
            }
            return $result;
        }
 
       /**
       * ==============================================================================
       *                    HELPER FUNCTIONS
       * ==============================================================================
       **/

        /**
        *    (logCustomerPushNotification($deviceSerNum, $patientSerNum, $title, $msg, $response)
        *    Consumes a PatientDeviceIdentifierSerNum, $deviceSerNum, $title, $msg,
        *    and response, $response, where send status is a 1 or 0 for whether is was successfully sent or not.
        *    Inserts a row into the PushNotification table or updates SendLog flag.
        *    RegistrationId.
        *    Returns: Returns the send status
        **/
       private static function logCustomerPushNotification($deviceSerNum, $patientSerNum, $title, $msg, $response)
       {
           global $pdo;
           $sendStatus  = $response['success'];
           $sendLog     = $response['error'];
           if ($sendStatus == 0) {$sendStatus = 'F';}
           else {
               $sendStatus = 'T';
               $sendLog = "Successfully sent push notification!";
           }
            $sql = " INSERT INTO `customPushNotificationLog`(
                    PatientSerNum, PatientDeviceIdentifierSerNum, SendStatus, NotificationTitle, NotificationMSG, DateAdded)
                    SELECT :PatientSerNum, :PatientDeviceIdentifierSerNum, :SendStatus, :NotificationTitle, :NotificationMSG, NOW()";
                
            $s = $pdo->prepare($sql);
            $s->bindValue(':PatientSerNum', $patientSerNum);
            $s->bindValue(':PatientDeviceIdentifierSerNum', $deviceSerNum);
            $s->bindValue(':SendStatus', $sendStatus);
            $s->bindValue(':NotificationTitle', $title);
            $s->bindValue(':NotificationMSG', $msg);
            $s->execute();

           return $sendStatus;
       }

       /**
        *    (getDevicesForPatient($patientId)
        *    Consumes a PatientId, $patientId
        *    Returns: Returns array with devices that match that particular PatiendId.
        **/
        private static function getDevicesForPatient($patientSerNum)
        {
            global $pdo;
            //Retrieving device registration id for notification and device
            try{
                $sql = "SELECT 
                            PDI.PatientDeviceIdentifierSerNum, 
                            PDI.RegistrationId, 
                            PDI.DeviceType 
                        FROM PatientDeviceIdentifier PDI, Users U, Patient P
                        WHERE P.PatientSerNum = $patientSerNum
                            AND PDI.Username = U.Username
                            AND P.PatientSerNum = U.UserTypeSerNum
                            AND PDI.DeviceType in (0, 1)
                            AND length(PDI.RegistrationId) > 0
                        ";
                $result = $pdo->query($sql);
            }catch(PDOException $e)
            {
                echo $e;
                exit();
            }
            return $result ->fetchAll();
        }

   }

?>
