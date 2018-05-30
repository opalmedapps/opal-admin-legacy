CREATE TRIGGER `doctor_delete_trigger` AFTER DELETE ON `Doctor`
 FOR EACH ROW BEGIN
 INSERT INTO DoctorMH (DoctorSerNum, DoctorRevSerNum, ResourceSerNum, SourceDatabaseSer, DoctorAriaSer, FirstName, LastName, Role, Workplace, Email, Phone, Address, ProfileImage, LastUpdated, ModificationAction) VALUES (OLD.DoctorSerNum, NULL, OLD.ResourceSerNum, OLD.SourceDatabaseSerNum, OLD.DoctorAriaSer, OLD.FirstName, OLD.LastName, OLD.Role, OLD.Workplace, OLD.Email, OLD.Phone, OLD.Address,OLD.ProfileImage,NOW(), 'DELETE');
END