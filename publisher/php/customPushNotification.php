<?php
    include_once "database.inc";
    require_once('PushNotifications.php');


   class customPushNotification{

       /**
       * ==============================================================================
       *            API CALL FUNCTIONS
       * ==============================================================================
       **/

      /**
        *    sendPatientNotification($patientId, $message):
        *    Consumes a PatientId and the message.
        *    Requires: - PatientId, Site, and message. The message is in an array.
        *    Returns:  Object containing keys of success, failure,
        *             responseDevices, which is an array containing, (success, failure,
        *             registrationId, deviceId) for each device, and Message array containing
        *             (title,description),  NotificationSerNum, and error if any.
        **/
       public static function sendPatientNotification($patientId, $site, $message)
       {
           global $pdo;

            // Step 1) Check if patient exist in the system
            // If patient does not exist, exit with an error
            // otherwise get the patientId, Language
            
            $sql = "SELECT P.PatientSerNum, P.Language
                    FROM Patient P, Patient_Hospital_Identifier PHI
                    WHERE P.PatientSerNum = PHI.PatientSerNum
                        and PHI.MRN = :patientId
                        and PHI.Hospital_Identifier_Type_Code = :siteId
                    ";

            try{
                $s = $pdo->prepare($sql);
                $s->bindValue(':patientId', $patientId);
                $s->bindValue(':siteId', $site);
                $s->execute();
                $result = $s->fetchAll();
            }catch(PDOException $e)
            {
                return array("success"=>0,"failure"=>1,"error"=>$e);
                exit();
            }

            if(count($result)==0)
			{
				return array("success"=>0,"failure"=>1,"error"=>"No matching PatientSerNum or AppointmentSerNum in Database");
				exit();
			}  

			// Ste 2 Get devices and language
			//Sets parameters for later usage
			$language = $result[0]["Language"];
			$patientSerNum = $result[0]["PatientSerNum"];

			if ($language == "EN")
			{
				$wsmtitle = $message['title_EN'];
				$wsmdesc = $message["message_EN"];
			}else{
				$wsmtitle = $message['title_FR'];
				$wsmdesc = $message["message_FR"];
			}

			$messageBody = array(
				"mtitle"=> $wsmtitle,
				"mdesc"=> $wsmdesc,
				"encode"=> "No" // Set the encoding to NO because the French characters works
			);

			//Obtain patient device identifiers
			$patientDevices = self::getDevicesForPatient($patientSerNum);

			//If no identifiers return there are no identifiers
			if(count($patientDevices)==0)
			{
				return array("success"=>0, "failure"=>1,"responseDevices"=>"No patient devices available for that patient");
				exit();
			}

			//Send message to patient devices and record in database
			$resultsArray = array();
			foreach($patientDevices as $device)
			{

				// Step 3) Send message
                //Determine device type
                if($device["DeviceType"]==0)
                {
                    $response = PushNotifications::iOS($messageBody, $device["RegistrationId"]);
                }else if($device["DeviceType"]==1)
                {
                    $response = PushNotifications::android($messageBody, $device["RegistrationId"]);
                }
                print "logCustomerPushNotification";
                //Log result of push notification on database.
				self::logCustomerPushNotification($device["PatientDeviceIdentifierSerNum"], $patientSerNum, $wsmtitle, $wsmdesc, $response);
                
                //Build response
				$response["DeviceType"] = $device["DeviceType"];
				$response["RegistrationId"] = $device["RegistrationId"];
				$resultsArray[] = $response;

                // Step 4) Log message to logAppointmentReminder(mrn, phoneNumber, messageSent, creationDate, creationTime)
            }

            return array("success"=>1,"failure"=>0,"responseDevices"=>$resultsArray,"message"=>$message);
            // return array("success"=>1,"failure"=>0);
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
        *    Inserts a into the PushNotification table or updates SendLog flag.
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
                $sql = "Select 
                            PDI.PatientDeviceIdentifierSerNum, 
                            PDI.RegistrationId, 
                            PDI.DeviceType 
                        FROM PatientDeviceIdentifier PDI
                        WHERE PDI.PatientSerNum = $patientSerNum
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
