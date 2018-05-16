CREATE TRIGGER `patients_for_patients_insert_trigger` AFTER INSERT ON `PatientsForPatients`
 FOR EACH ROW BEGIN
INSERT INTO `PatientsForPatientsMH`(`PatientsForPatientsSerNum`, `CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.PatientsForPatientsSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.CronLogSerNum, NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.PatientsForPatientsSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'PatientsForPatients';
END