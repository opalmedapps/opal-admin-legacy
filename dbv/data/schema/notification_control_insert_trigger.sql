CREATE TRIGGER `notification_control_insert_trigger` AFTER INSERT ON `NotificationControl`
 FOR EACH ROW BEGIN
   INSERT INTO `NotificationControlMH`(`NotificationControlSerNum`, `Name_EN`, `Name_FR`, `Description_EN`, `Description_FR`, `NotificationTypeSerNum`, `DateAdded`, `LastUpdatedBy`, `SessionId`, `ModificationAction`) VALUES (NEW.NotificationControlSerNum, NEW.Name_EN, NEW.Name_FR, NEW.Description_EN, NEW.Description_FR, NEW.NotificationTypeSerNum, NEW.DateAdded, NEW.LastUpdatedBy, NEW.SessionId, 'INSERT');
END