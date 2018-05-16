CREATE TRIGGER `questionnaire_control_insert_trigger` AFTER INSERT ON `QuestionnaireControl`
 FOR EACH ROW BEGIN
   INSERT INTO `QuestionnaireControlMH`(`QuestionnaireControlSerNum`, `QuestionnaireDBSerNum`, `QuestionnaireName_EN`, `QuestionnaireName_FR`, `Intro_EN`, `Intro_FR`, `PublishFlag`, `DateAdded`, `LastUpdatedBy`, `LastPublished`, `SessionId`, `ModificationAction` ) VALUES (NEW.QuestionnaireControlSerNum, NEW.QuestionnaireDBSerNum, NEW.QuestionnaireName_EN, NEW.QuestionnaireName_FR, NEW.Intro_EN, NEW.Intro_FR, NEW.PublishFlag,NOW(), NEW.LastUpdatedBy, NEW.LastPublished, NEW.SessionId, 'INSERT');
END