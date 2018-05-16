CREATE TRIGGER `legacy_questionnaire_delete_trigger` AFTER DELETE ON `Questionnaire`
 FOR EACH ROW BEGIN
INSERT INTO QuestionnaireMH (`QuestionnaireSerNum`, `CronLogSerNum`, `QuestionnaireControlSerNum`, `PatientSerNum`, `PatientQuestionnaireDBSerNum`, `CompletedFlag`, `CompletionDate`, `DateAdded`, ModificationAction) VALUES (OLD.QuestionnaireSerNum, OLD.CronLogSerNum, OLD.QuestionnaireControlSerNum, OLD.PatientSerNum, OLD.PatientQuestionnaireDBSerNum, OLD.CompletedFlag, OLD.CompletionDate, NOW(), 'DELETE');
END