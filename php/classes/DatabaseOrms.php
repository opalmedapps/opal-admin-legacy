<?php

/**
 * ORMS SQL config file that contains config access, table names and SQL queries used in ORMS
 * User: Dominic Bourdua
 * Date: 26/02/2019
 * Time: 11:04 AM
 */

class DatabaseOrms extends DatabaseAccess {


    function getAppointmentForAlias() {
        $result = $this->_fetchAll(ORMS_SQL_GET_APPOINTMENT_FOR_ALIAS, array());
        $toInsert = array();

        foreach($result as $item) {
            $tempArr = array(
                "externalId"=>-1,
                "type"=>2,
                "code"=>$item["code"],
                "expression"=>$item["expression"],
                "source"=>2,
            );
            array_push($toInsert, $tempArr);
        }
        return $toInsert;
    }

    function getAppointmentForSms()
    {
        $result = $this->_fetchAll(ORMS_SQL_GET_APPOINTMENT_FOR_SMS, array());
        $appointmentList = array();
        foreach ($result as $item) {
            $tempArr = array(
                "appcode" => $item["appcode"],
                "rescode" => $item["rescode"],
                "resname" => $item["resname"],
                "state" => $item["state"],
                "spec" => $item["spec"],
                "ressernum" => $item["ressernum"],
                "code" => $item["codeid"],
                "apptype" => $item["type"],
                "source" => 2,
            );
            array_push($appointmentList, $tempArr);
        }
        return $appointmentList;
    }

    function getEventsForAppointment($type,$speciality)
    {
        $result = $this->_fetchAll(ORMS_SQL_GET_EVENTS_FOR_APPOINTMENT, array(
            array("parameter"=>":typ","variable"=>$type,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":spec","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
        ));
        $events = array();
        foreach ($result as $item) {
            $tempArr = array(
                "event" => $item["event"],
                "source" => 2,
            );
            array_push($events, $tempArr);
        }
        return $events;
    }

    function getMessageForAppointment($speciality,$type,$event,$language)
    {
//        if($language == 'EN'){
//            $language = 'English';
//        }
//        elseif($language == 'FR'){
//            $language = 'French';
//        }
        $result = $this->_fetchAll(ORMS_SQL_GET_MESSAGE_FOR_APPOINTMENT, array(
            array("parameter"=>":spec","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":typ","variable"=>$type,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":event","variable"=>$event,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":lang","variable"=>$language,"data_type"=>PDO::PARAM_STR),
        ));
        $messages = array();

        foreach ($result as $item) {
            $messages = array(
                "message" => $item["smsmessage"],
            );
        }
        return $messages;
    }

    function updateActivationState($state, $appointmentCodeId, $resourceSerNum) {
        $toInsert = array(
            array("parameter"=>":state","variable"=>$state,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":res","variable"=>$resourceSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":id","variable"=>$appointmentCodeId,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(ORMS_SQL_UPDATE_APPOINTMENT_ACTIVE_STATE, $toInsert);
    }

    function updateSmsMessage($smsMessage,$speciality, $type, $event, $language) {
        $toInsert = array(
            array("parameter"=>":message","variable"=>$smsMessage,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":spec","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":type","variable"=>$type,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":event","variable"=>$event,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":lang","variable"=>$language,"data_type"=>PDO::PARAM_STR),
        );
        return $this->_execute(ORMS_SQL_UPDATE_MESSAGE_FOR_APPOINTMENT, $toInsert);
    }


    function getSpecialityForMessage(){
        $result = $this->_fetchAll(ORMS_SQL_GET_SPECIALITY_FOR_MESSAGE, array());
        $specialityList = array();
        foreach ($result as $item) {
            $tempArr = array(
                "speciality" => $item["Speciality"],
                "source" => 2,
            );
            array_push($specialityList, $tempArr);
        }
        return $specialityList;
    }

    function getTypeForMessage($speciality){
        $result = $this->_fetchAll(ORMS_SQL_GET_TYPE_FOR_MESSAGE, array(
            array("parameter"=>":spec","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
        ));
        $typeList = array();
        foreach ($result as $item) {
            $tempArr = array(
                "type" => $item["Type"],
                "source" => 2,
            );
            array_push($typeList, $tempArr);
        }
        return $typeList;
    }

}