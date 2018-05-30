CREATE TRIGGER `post_control_update_trigger` AFTER UPDATE ON `PostControl`
 FOR EACH ROW BEGIN
if NEW.LastPublished <=> OLD.LastPublished THEN
   INSERT INTO `PostControlMH`(`PostControlSerNum`, `PostType`, `PublishFlag`, `PostName_FR`, `PostName_EN`, `Body_FR`, `Body_EN`, `PublishDate`, `Disabled`, `DateAdded`, `LastPublished`, `LastUpdatedBy`, `SessionId`, `ModificationAction`) VALUES (NEW.PostControlSerNum, NEW.PostType, NEW.PublishFlag, NEW.PostName_FR, NEW.PostName_EN, NEW.Body_FR, NEW.Body_EN, NEW.PublishDate, NEW.Disabled, NOW(), NEW.LastPublished, NEW.LastUpdatedBy, NEW.SessionId, 'UPDATE');
END IF;
END