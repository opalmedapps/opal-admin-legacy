CREATE TRIGGER `educationalmaterial_insert_trigger` AFTER INSERT ON `EducationalMaterial`
 FOR EACH ROW BEGIN
INSERT INTO `EducationalMaterialMH`(`EducationalMaterialSerNum`, `CronLogSerNum`, `EducationalMaterialControlSerNum`, `PatientSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.EducationalMaterialSerNum, NEW.CronLogSerNum, NEW.EducationalMaterialControlSerNum, NEW.PatientSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.CronLogSerNum, NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.EducationalMaterialSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'EducationalMaterial';
END