<?php

/*
 * Class to handle all the future methods for the CronJobs operations
 * */
class CronJob extends OpalProject {

    protected $ariaDB;

    public function __construct($OAUserId = false, $sessionId = false) {
        if(!$sessionId)
            $sessionId = HelpSetup::makeSessionId();
        parent::__construct($OAUserId);

        if(ARIA_DB_ENABLED)
            $this->ariaDB = new DatabaseAria(
                ARIA_DB_HOST,
                "",
                ARIA_DB_PORT,
                ARIA_DB_USERNAME,
                ARIA_DB_PASSWORD,
                ARIA_DB_DSN
            );
        else
            $this->ariaDB = new DatabaseDisconnected();

        $this->ariaDB->setUsername($this->opalDB->getUsername());
        $this->ariaDB->setOAUserId($this->opalDB->getOAUserId());
        $this->ariaDB->setUserRole($this->opalDB->getUserRole());
    }

    public function incrementLogFile($functionName) {
        $lineFound = false;

        $myFile = fopen(FRONTEND_ABS_PATH . "publisher". DIRECTORY_SEPARATOR . "logs". DIRECTORY_SEPARATOR . "php-cron.log", "r+") or die("Unable to open file!");
        while(!feof($myFile)) {
            $aLine = explode(" ", fgets($myFile));
            if($aLine[0] == $functionName) {
                $lineFound = true;
                break;
            }
            print_r($aLine);
            print "\r\n";
        }

        if(!$lineFound) {
            fwrite($myFile, $functionName . " 1\r\n");
        }
        else
            echo "found!\r\n";

        fclose($myFile);

    }

    /*
     * This function updates the aliases lists by connecting to ARIA
     * */
    public function updateAliasesList() {


        $this->incrementLogFile(__FUNCTION__);




        echo __FUNCTION__ ."\r\n";




        //Get the last time the source where updated
        $settings = $this->opalDB->getSettings(SETTING_CRONJOB);
        $settings = json_decode($settings["setting"], true);
        $result = HelpSetup::verifyDate($settings["lastUpdated"], false, "Y-m-d H:i:s");

        print_r($this->opalDB->getUserRole());
        print_r($this->ariaDB->getUserRole());

        if(!$result)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot run the cron job for the alias import. Invalid date format in the setting table.");


//       $settings["lastUpdated"]


    }


}