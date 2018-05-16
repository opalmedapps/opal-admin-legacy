CREATE TRIGGER `email_delete_trigger` AFTER DELETE ON `EmailLog`
 FOR EACH ROW BEGIN
INSERT INTO `EmailLogMH`(`EmailLogSerNum`, `CronLogSerNum`, `PatientSerNum`, `EmailControlSerNum`, `Status`, `DateAdded`, `ModificationAction`) VALUES (OLD.EmailLogSerNum, OLD.CronLogSerNum, OLD.PatientSerNum, OLD.EmailControlSerNum, OLD.Status, NOW(), 'DELETE');
END