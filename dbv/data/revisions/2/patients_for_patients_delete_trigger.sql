CREATE TRIGGER `patients_for_patients_delete_trigger` AFTER DELETE ON `PatientsForPatients`
 FOR EACH ROW BEGIN
INSERT INTO `PatientsForPatientsMH`(`PatientsForPatientsSerNum`, `CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (OLD.PatientsForPatientsSerNum, OLD.CronLogSerNum, OLD.PatientSerNum, OLD.PostControlSerNum, NOW(), OLD.ReadStatus, 'DELETE');
END