CREATE TRIGGER `notification_control_delete_trigger` AFTER DELETE ON `NotificationControl`
 FOR EACH ROW BEGIN
   INSERT INTO `NotificationControlMH`(`NotificationControlSerNum`, `Name_EN`, `Name_FR`, `Description_EN`, `Description_FR`, `NotificationTypeSerNum`, `DateAdded`, `LastUpdatedBy`, `SessionId`, `ModificationAction`) VALUES (OLD.NotificationControlSerNum, OLD.Name_EN, OLD.Name_FR, OLD.Description_EN, OLD.Description_FR, OLD.NotificationTypeSerNum, OLD.DateAdded, OLD.LastUpdatedBy, OLD.SessionId, 'DELETE');
END