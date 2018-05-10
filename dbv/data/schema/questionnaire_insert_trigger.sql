CREATE DEFINER=`ackeem`@`%` TRIGGER `questionnaire_insert_trigger` AFTER INSERT ON `Questionnaire_patient`
 FOR EACH ROW BEGIN
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.patient_serNum,ntc.NotificationControlSerNum,NEW.serNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'Questionnaire';
END