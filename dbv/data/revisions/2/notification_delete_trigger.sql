CREATE TRIGGER `notification_delete_trigger` AFTER DELETE ON `Notification`
 FOR EACH ROW BEGIN
INSERT INTO `NotificationMH`(`NotificationSerNum`, `CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`) VALUES (OLD.NotificationSerNum, OLD.CronLogSerNum, OLD.PatientSerNum, OLD.NotificationControlSerNum, OLD.RefTableRowSerNum, OLD.ReadStatus, NOW(), 'DELETE');
END