<?php
    require_once('apptReminderPushNotification.php');

    // Step 1: Gets a list of all appointments of patient on the next day
    $result = apptReminderPushNotification::getNextDayAppointments();
    if(array_key_exists("failure", $result) && $result["failure"] == 1)
    {
        print json_encode($result) . PHP_EOL;
        exit();
    }
    // Step 2: Prepare the Push Notification message
    foreach($result as $row){
        $patientSerNum = $row["PatientSerNum"];
        $language = $row["Language"];
        $hospital_EN = "MUHC";
        $hospital_FR = "CUSM";
        $title_EN = "Opal Appointment Reminder";
        $title_FR = "Rappel de rendez-vous d'Opal";
        $appointment_date = $row["Date"];
        $appointment_time = $row["Time"];
        $appointment_alias_EN = $row["AliasName_EN"];
        $appointment_alias_FR = $row["AliasName_FR"];
    
        if ($language == "EN"){
            $message_text_EN = "Opal reminder for an appointment at the " 
                            . $hospital_EN . ": " . $appointment_alias_EN  
                            . " on " . $appointment_date . " at " . $appointment_time;
        }
        else{
            $message_text_FR = "Opal rappel pour un rendez-vous au " 
                            . $hospital_FR . ": " . $appointment_alias_FR  
                            . " le " . $appointment_date . " à " . $appointment_time;
        }
    
        $messages = array(
            "title_EN"=> $title_EN,
            "message_text_EN"=> $message_text_EN,
            "title_FR"=> $title_FR,
            "message_text_FR"=> $message_text_FR
        );

        // Call API to send push notification
        $response = apptReminderPushNotification::sendPatientNotification($patientSerNum, $language, $messages);
        print json_encode($response) . PHP_EOL;
    }

?>