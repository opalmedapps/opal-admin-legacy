<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class OpalMailer extends HelpSetup
{
    private $host;
    private $username;
    private $password;
    private $port;
    
    /**
     * Constructor of the class
     */
    function __construct() {
        try{
            $this->host = "smtp.example.com";
            $this->username = "example@muhc.mcgill.ca";
            $this->password = "AuthorizationCode";
            $this->port = "587";
        } catch (Throwable $err){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY, "An error occur during email creation: " . $err->getMessage());
        }
    }

    /**
     * Update the email address of a given patient in firebase.
     * @param $subject - email subject
     * @param $message - email message
     * @return array - void
     */
    public function sendViaSMTP($subject, $message, $recipientes, $sender){

        //Create an instance; passing `true` (e.g., PHPMailer(true)) enables exceptions
        $mail = new PHPMailer();

        // Settings
        $mail->IsSMTP();
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;

        $mail->Host       = $this->host;
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;      // enables SMTP debug information (for testing)
        $mail->SMTPAuth   = true;                 // enable SMTP authentication
        $mail->Username   = $this->username;
        $mail->Password   = $this->password;
        $mail->Port       = $this->port;       // set the SMTP port

        // Content
        $mail->setFrom(
            $sender,
            'ORMS',
        );
        foreach($recipientes as $email)
        {
            $mail->addAddress($email);
        }

        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();

    }
}