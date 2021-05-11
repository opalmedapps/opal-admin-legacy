<?php
// DEFINE Waiting Room Management SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MySQL setup.
define( "WRM_DB_ENABLED", (intval($config['databaseConfig']['wrm']['enabled']) == 0?false:true));
define( "WRM_DB_HOST", $config['databaseConfig']['wrm']['host'] );
define( "WRM_DB_PORT", $config['databaseConfig']['wrm']['port'] );
define( "WRM_DB_NAME", $config['databaseConfig']['wrm']['name'] );
define( "WRM_DB_NAME_FED", $config['databaseConfig']['wrm']['nameFED'] );
define( "WRM_DB_DSN", "mysql:host=" . WRM_DB_HOST . ";port=" . WRM_DB_PORT . ";dbname=" . WRM_DB_NAME . ";charset=utf8" );
define( "WRM_DB_USERNAME", $config['databaseConfig']['wrm']['username'] );
define( "WRM_DB_PASSWORD", $config['databaseConfig']['wrm']['password'] );

define("ORMS_MEDIVISIT_APPOINTMENT_LIST_TABLE", "MediVisitAppointmentList");
define("ORMS_SMS_APPOINTMENT_LIST_TABLE", "SmsAppointment");
define("ORMS_SMS_MESSAGE_LIST_TABLE", "SmsMessage");
define("ORMS_CLINIC_RESOURCE_LIST_TABLE", "ClinicResources");
define("ORMS_APPOINTMENT_CODE_LIST_TABLE", "AppointmentCode");


define("ORMS_SQL_GET_APPOINTMENT_FOR_ALIAS","
    SELECT DISTINCT mval.AppointmentCode AS code, mval.ResourceDescription AS expression 
    FROM ".ORMS_MEDIVISIT_APPOINTMENT_LIST_TABLE." mval
    WHERE mval.AppointSys in ('Medivisit','Impromptu','ImpromptuOrtho','InstantAddOn')
    ORDER BY mval.AppointmentCode, mval.ResourceDescription
");

define("ORMS_SQL_GET_APPOINTMENT_FOR_SMS","
    SELECT appc.AppointmentCode AS appcode, clir.ResourceCode AS rescode, smsa.Active AS state,
           smsa.Speciality AS spec, smsa.Type AS type, smsa.ClinicResourcesSerNum AS ressernum, 
           smsa.AppointmentCodeId AS codeid, clir.ResourceName AS resname
    FROM ".ORMS_SMS_APPOINTMENT_LIST_TABLE." smsa 
    INNER JOIN ".ORMS_APPOINTMENT_CODE_LIST_TABLE." appc 
    ON appc.AppointmentCodeId = smsa.AppointmentCodeId
    INNER JOIN ".ORMS_CLINIC_RESOURCE_LIST_TABLE." clir
    ON clir.ClinicResourcesSerNum = smsa.ClinicResourcesSerNum
");

define("ORMS_SQL_GET_EVENTS_FOR_APPOINTMENT","
    SELECT DISTINCT message.Event AS event
    FROM ".ORMS_SMS_MESSAGE_LIST_TABLE." message
    WHERE message.Type = :typ AND (message.Speciality = 'Any' OR message.Speciality = :spec)
    ORDER BY Event
");

define("ORMS_SQL_GET_MESSAGE_FOR_APPOINTMENT","
    SELECT message.Message AS smsmessage
    FROM ".ORMS_SMS_MESSAGE_LIST_TABLE." message
    WHERE (message.Speciality = 'Any' OR message.Speciality = :spec) AND message.Type = :typ AND message.Event = :event 
    AND message.Language = :lang
");

define("ORMS_SQL_UPDATE_APPOINTMENT_ACTIVE_STATE","
    UPDATE ".ORMS_SMS_APPOINTMENT_LIST_TABLE." SET Active = :state
    WHERE ClinicResourcesSerNum = :res AND AppointmentCodeId = :id
");

define("ORMS_SQL_UPDATE_APPOINTMENT_TYPE","
    UPDATE ".ORMS_SMS_APPOINTMENT_LIST_TABLE." SET Type = :typ
    WHERE ClinicResourcesSerNum = :res AND AppointmentCodeId = :id
");

define("ORMS_SQL_UPDATE_MESSAGE_FOR_APPOINTMENT","
    UPDATE ".ORMS_SMS_MESSAGE_LIST_TABLE." sms SET Message = :message
    WHERE (sms.Speciality = 'Any' OR sms.Speciality = :spec) AND sms.Type = :type AND sms.Event = :event AND sms.Language = :lang
");

define("ORMS_SQL_GET_SPECIALITY_FOR_MESSAGE","
    SELECT DISTINCT Speciality FROM ".ORMS_SMS_APPOINTMENT_LIST_TABLE
);

define("ORMS_SQL_GET_TYPE_FOR_MESSAGE","
    SELECT DISTINCT message.Type FROM ".ORMS_SMS_MESSAGE_LIST_TABLE." message 
    WHERE (message.Speciality = 'Any' OR message.Speciality = :spec)
");

