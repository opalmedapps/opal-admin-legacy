CREATE TRIGGER `diagnosis_code_insert_trigger` AFTER INSERT ON `DiagnosisCode`
 FOR EACH ROW BEGIN
   INSERT INTO `DiagnosisCodeMH`(`DiagnosisTranslationSerNum`,`SourceUID`, `DiagnosisCode`, `Description`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (NEW.DiagnosisTranslationSerNum, NEW.SourceUID, NEW.DiagnosisCode, NEW.Description, NEW.LastUpdatedBy, NEW.SessionId, 'INSERT', NOW());
END