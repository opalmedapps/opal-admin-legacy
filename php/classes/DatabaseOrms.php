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

        return $result;
    }

    function getEventsForAppointment($type,$speciality)
    {
        $result = $this->_fetchAll(ORMS_SQL_GET_EVENTS_FOR_APPOINTMENT, array(
            array("parameter"=>":Type","variable"=>$type,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Speciality","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
        ));

        return $result;
    }

    function getMessageForAppointment($speciality,$type,$event,$language)
    {
        $result = $this->_fetchAll(ORMS_SQL_GET_MESSAGE_FOR_APPOINTMENT, array(
            array("parameter"=>":Speciality","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Type","variable"=>$type,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Event","variable"=>$event,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Language","variable"=>$language,"data_type"=>PDO::PARAM_STR),
        ));
        return $result;
    }

    function updateActivationState($state,$appointmentCodeId, $resourceSerNum) {
        $toInsert = array(
            array("parameter"=>":Active","variable"=>$state,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ClinicResourcesSerNum","variable"=>$resourceSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":AppointmentCodeId","variable"=>$appointmentCodeId,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(ORMS_SQL_UPDATE_APPOINTMENT_ACTIVE_STATE, $toInsert);
    }

    function updateAppointmentType($type, $appointmentCodeId, $resourceSerNum) {
        $toInsert = array(
            array("parameter"=>":Type","variable"=>$type,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":ClinicResourcesSerNum","variable"=>$resourceSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":AppointmentCodeId","variable"=>$appointmentCodeId,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(ORMS_SQL_UPDATE_APPOINTMENT_TYPE, $toInsert);
    }

    function setAppointmentTypeNull($appointmentCodeId, $resourceSerNum) {
        $toInsert = array(
            array("parameter"=>":ClinicResourcesSerNum","variable"=>$resourceSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":AppointmentCodeId","variable"=>$appointmentCodeId,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(ORMS_SQL_SET_APPOINTMENT_TYPE_TO_NULL, $toInsert);
    }

    function updateSmsMessage($smsMessage,$speciality, $type, $event, $language) {
        $toInsert = array(
            array("parameter"=>":Message","variable"=>$smsMessage,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Speciality","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Type","variable"=>$type,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Event","variable"=>$event,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Language","variable"=>$language,"data_type"=>PDO::PARAM_STR),
        );
        return $this->_execute(ORMS_SQL_UPDATE_MESSAGE_FOR_APPOINTMENT, $toInsert);
    }


    function getSpecialityForMessage(){
        $result = $this->_fetchAll(ORMS_SQL_GET_SPECIALITY_FOR_MESSAGE, array());
        return $result;
    }

    function getTypeForMessage($speciality){
        $result = $this->_fetchAll(ORMS_SQL_GET_TYPE_FOR_MESSAGE, array(
            array("parameter"=>":Speciality","variable"=>$speciality,"data_type"=>PDO::PARAM_STR),
        ));
        return $result;
    }

    function getAllTypeForMessage(){
        $result = $this->_fetchAll(ORMS_SQL_GET_ALL_TYPE__FOR_MESSAGE, array());
        return $result;
    }
}