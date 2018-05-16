CREATE TRIGGER `educationalmaterial_update_trigger` AFTER UPDATE ON `EducationalMaterial`
 FOR EACH ROW BEGIN
INSERT INTO `EducationalMaterialMH`(`EducationalMaterialSerNum`, `CronLogSerNum`, `EducationalMaterialControlSerNum`, `PatientSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.EducationalMaterialSerNum, NEW.CronLogSerNum, NEW.EducationalMaterialControlSerNum, NEW.PatientSerNum, NOW(), NEW.ReadStatus, 'UPDATE');
END