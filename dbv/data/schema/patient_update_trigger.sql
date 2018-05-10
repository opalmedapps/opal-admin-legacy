CREATE DEFINER=`ackeem`@`%` TRIGGER `patient_update_trigger` AFTER UPDATE ON `Patient`
 FOR EACH ROW BEGIN
INSERT INTO `PatientMH`(`PatientSerNum`, `PatientRevSerNum`, `SessionId`,`PatientAriaSer`, `PatientId`, `PatientId2`, `FirstName`, `LastName`, `Alias`, `Sex`, `DateOfBirth`, `TelNum`, `EnableSMS`, `Email`, `Language`, `SSN`, `AccessLevel`,`RegistrationDate`, `LastUpdated`, `ModificationAction`) VALUES (NEW.PatientSerNum,NULL,NEW.SessionId,NEW.PatientAriaSer,NEW.PatientId, NEW.PatientId2, NEW.FirstName,NEW.LastName,NEW.Alias, NEW.Sex, NEW.DateOfBirth, NEW.TelNum,NEW.EnableSMS,NEW.Email,NEW.Language,NEW.SSN, NEW.AccessLevel,NEW.RegistrationDate,NOW(), 'UPDATE');
END