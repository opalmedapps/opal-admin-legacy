CREATE DEFINER=`ackeem`@`%` TRIGGER `diagnosis_code_update_trigger` AFTER UPDATE ON `DiagnosisCode`
 FOR EACH ROW BEGIN
   INSERT INTO `DiagnosisCodeMH`(`DiagnosisTranslationSerNum`,`SourceUID`, `DiagnosisCode`, `Description`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (NEW.DiagnosisTranslationSerNum, NEW.SourceUID, NEW.DiagnosisCode, NEW.Description, NEW.LastUpdatedBy, NEW.SessionId, 'UPDATE', NOW());
END