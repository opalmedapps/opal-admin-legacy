CREATE TRIGGER `diagnosis_translation_delete_trigger` AFTER DELETE ON `DiagnosisTranslation`
 FOR EACH ROW BEGIN
   INSERT INTO `DiagnosisTranslationMH`( `DiagnosisTranslationSerNum`, `EducationalMaterialControlSerNum`, `Name_EN`, `Name_FR`, `Description_EN`, `Description_FR`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (OLD.DiagnosisTranslationSerNum, OLD.EducationalMaterialControlSerNum, OLD.Name_EN, OLD.Name_FR, OLD.Description_EN, OLD.Description_FR, NULL, NULL, 'DELETE', NOW());
END