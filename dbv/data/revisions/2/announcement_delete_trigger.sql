CREATE TRIGGER `announcement_delete_trigger` AFTER DELETE ON `Announcement`
 FOR EACH ROW BEGIN
INSERT INTO `AnnouncementMH`(`AnnouncementSerNum`, `CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (OLD.AnnouncementSerNum, OLD.CronLogSerNum, OLD.PatientSerNum, OLD.PostControlSerNum, NOW(), OLD.ReadStatus, 'DELETE');
END