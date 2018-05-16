CREATE TRIGGER `questionnaire_control_update_trigger` AFTER UPDATE ON `QuestionnaireControl`
 FOR EACH ROW BEGIN
if NEW.LastPublished <=> OLD.LastPublished THEN
   INSERT INTO `QuestionnaireControlMH`(`QuestionnaireControlSerNum`, `QuestionnaireDBSerNum`, `QuestionnaireName_EN`, `QuestionnaireName_FR`, `Intro_EN`, `Intro_FR`, `PublishFlag`, `DateAdded`, `LastUpdatedBy`, `LastPublished`, `SessionId`, `ModificationAction` ) VALUES (NEW.QuestionnaireControlSerNum, NEW.QuestionnaireDBSerNum, NEW.QuestionnaireName_EN, NEW.QuestionnaireName_FR, NEW.Intro_EN, NEW.Intro_FR, NEW.PublishFlag,NOW(), NEW.LastUpdatedBy, NEW.LastPublished, NEW.SessionId, 'UPDATE');
END IF;
END