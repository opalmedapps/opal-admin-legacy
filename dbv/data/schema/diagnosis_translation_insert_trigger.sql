CREATE TRIGGER `diagnosis_translation_insert_trigger` AFTER INSERT ON `DiagnosisTranslation`
 FOR EACH ROW BEGIN
   INSERT INTO `DiagnosisTranslationMH`( `DiagnosisTranslationSerNum`, `EducationalMaterialControlSerNum`, `Name_EN`, `Name_FR`, `Description_EN`, `Description_FR`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (NEW.DiagnosisTranslationSerNum, NEW.EducationalMaterialControlSerNum, NEW.Name_EN, NEW.Name_FR, NEW.Description_EN, NEW.Description_FR, NEW.LastUpdatedBy, NEW.SessionId, 'INSERT', NOW());
END