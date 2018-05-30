CREATE TRIGGER `txteammessage_insert_trigger` AFTER INSERT ON `TxTeamMessage`
 FOR EACH ROW BEGIN
INSERT INTO `TxTeamMessageMH`(`TxTeamMessageSerNum`, `CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)  VALUES (NEW.TxTeamMessageSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.CronLogSerNum, NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.TxTeamMessageSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'TxTeamMessage';
END