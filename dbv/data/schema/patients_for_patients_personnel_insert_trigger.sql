CREATE DEFINER=`root`@`localhost` TRIGGER `patients_for_patients_personnel_insert_trigger` AFTER INSERT ON `PatientsForPatientsPersonnel`
 FOR EACH ROW BEGIN
INSERT INTO `PatientsForPatientsPersonnelMH` (`PatientsForPatientsPersonnelSerNum`, `FirstName`, `LastName`, `Email`, `Bio_EN`, `Bio_FR`, `ProfileImage`, `ModificationAction`) VALUES (NEW.PatientsForPatientsPersonnelSerNum,NEW.FirstName, NEW.LastName, NEW.Email,NEW.Bio_EN,NEW.Bio_FR,NEW.ProfileImage, 'INSERT ');
END