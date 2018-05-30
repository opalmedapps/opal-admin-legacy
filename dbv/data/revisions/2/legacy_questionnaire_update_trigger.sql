CREATE TRIGGER `legacy_questionnaire_update_trigger` AFTER UPDATE ON `Questionnaire`
 FOR EACH ROW BEGIN
INSERT INTO QuestionnaireMH (`QuestionnaireSerNum`, `CronLogSerNum`, `QuestionnaireControlSerNum`, `PatientSerNum`, `PatientQuestionnaireDBSerNum`, `CompletedFlag`, `CompletionDate`, `DateAdded`, ModificationAction) VALUES (NEW.QuestionnaireSerNum, NEW.CronLogSerNum, NEW.QuestionnaireControlSerNum, NEW.PatientSerNum, NEW.PatientQuestionnaireDBSerNum, NEW.CompletedFlag, NEW.CompletionDate, NOW(), 'UPDATE');
END