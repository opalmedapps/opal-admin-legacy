CREATE TRIGGER `diagnosis_code_delete_trigger` AFTER DELETE ON `DiagnosisCode`
 FOR EACH ROW BEGIN
   INSERT INTO `DiagnosisCodeMH`(`DiagnosisTranslationSerNum`,`SourceUID`, `DiagnosisCode`, `Description`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (OLD.DiagnosisTranslationSerNum, OLD.SourceUID, OLD.DiagnosisCode, OLD.Description, OLD.LastUpdatedBy, OLD.SessionId, 'DELETE', NOW());
END