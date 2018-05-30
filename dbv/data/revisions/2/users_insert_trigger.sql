CREATE TRIGGER `users_insert_trigger` AFTER INSERT ON `Users`
 FOR EACH ROW BEGIN
INSERT INTO `UsersMH` (`UserSerNum`, `UserRevSerNum`,`SessionId`, `UserType`, `UserTypeSerNum`, `Username`, `Password`,`LastUpdated`, `ModificationAction`) 
VALUES (NEW.UserSerNum, NULL,NEW.SessionId,NEW.UserType,  NEW.UserTypeSerNum, NEW.Username,NEW.Password, NOW(), 'INSERT');
END