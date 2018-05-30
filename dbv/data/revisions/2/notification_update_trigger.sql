CREATE TRIGGER `notification_update_trigger` AFTER UPDATE ON `Notification`
 FOR EACH ROW BEGIN
INSERT INTO `NotificationMH`(`NotificationSerNum`, `CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`) VALUES (NEW.NotificationSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.NotificationControlSerNum, NEW.RefTableRowSerNum, NEW.ReadStatus, NOW(), 'UPDATE');
END