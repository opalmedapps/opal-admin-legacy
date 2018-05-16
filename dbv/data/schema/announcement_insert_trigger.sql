CREATE TRIGGER `announcement_insert_trigger` AFTER INSERT ON `Announcement`
 FOR EACH ROW BEGIN
INSERT INTO `AnnouncementMH`(`AnnouncementSerNum`,`CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.AnnouncementSerNum,NEW.CronLogSerNum, NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.CronLogSerNum, NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.AnnouncementSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'Announcement';
END