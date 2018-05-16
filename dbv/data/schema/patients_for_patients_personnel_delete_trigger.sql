CREATE TRIGGER `patients_for_patients_personnel_delete_trigger` AFTER DELETE ON `PatientsForPatientsPersonnel`
 FOR EACH ROW BEGIN
INSERT INTO `PatientsForPatientsPersonnelMH` (`PatientsForPatientsPersonnelSerNum`, `FirstName`, `LastName`, `Email`, `Bio_EN`, `Bio_FR`, `ProfileImage`, `ModificationAction`) VALUES (OLD.PatientsForPatientsPersonnelSerNum,OLD.FirstName, OLD.LastName, OLD.Email,OLD.Bio_EN,OLD.Bio_FR,OLD.ProfileImage, 'DELETE');
END