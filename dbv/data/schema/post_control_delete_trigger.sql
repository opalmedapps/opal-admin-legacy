CREATE TRIGGER `post_control_delete_trigger` AFTER DELETE ON `PostControl`
 FOR EACH ROW BEGIN
   INSERT INTO `PostControlMH`(`PostControlSerNum`, `PostType`, `PublishFlag`, `PostName_FR`, `PostName_EN`, `Body_FR`, `Body_EN`, `PublishDate`, `Disabled`, `DateAdded`, `LastPublished`, `LastUpdatedBy`, `SessionId`, `ModificationAction`) VALUES (OLD.PostControlSerNum, OLD.PostType, OLD.PublishFlag, OLD.PostName_FR, OLD.PostName_EN, OLD.Body_FR, OLD.Body_EN, OLD.PublishDate, OLD.Disabled, NOW(), OLD.LastPublished, OLD.LastUpdatedBy, OLD.SessionId, 'DELETE');
END