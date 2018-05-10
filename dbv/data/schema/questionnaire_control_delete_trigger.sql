CREATE DEFINER=`ackeem`@`%` TRIGGER `questionnaire_control_delete_trigger` AFTER DELETE ON `QuestionnaireControl`
 FOR EACH ROW BEGIN
   INSERT INTO `QuestionnaireControlMH`(`QuestionnaireControlSerNum`, `QuestionnaireDBSerNum`, `QuestionnaireName_EN`, `QuestionnaireName_FR`, `Intro_EN`, `Intro_FR`, `PublishFlag`, `DateAdded`, `LastUpdatedBy`, `LastPublished`, `SessionId`, `ModificationAction` ) VALUES (OLD.QuestionnaireControlSerNum, OLD.QuestionnaireDBSerNum, OLD.QuestionnaireName_EN, OLD.QuestionnaireName_FR, OLD.Intro_EN, OLD.Intro_FR, OLD.PublishFlag,NOW(), OLD.LastUpdatedBy, OLD.LastPublished, OLD.SessionId, 'DELETE');
END