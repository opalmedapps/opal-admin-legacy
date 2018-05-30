CREATE TRIGGER `users_delete_trigger` AFTER DELETE ON `Users`
 FOR EACH ROW BEGIN
INSERT INTO `UsersMH` (`UserSerNum`, `UserRevSerNum`,`SessionId`, `UserType`, `UserTypeSerNum`, `Username`, `Password`,`LastUpdated`, `ModificationAction`) 
VALUES (OLD.UserSerNum, NULL,OLD.SessionId,OLD.UserType, OLD.UserTypeSerNum, OLD.Username,OLD.Password, NOW(), 'DELETE');
END