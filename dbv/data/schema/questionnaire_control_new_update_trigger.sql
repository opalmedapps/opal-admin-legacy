CREATE DEFINER=`ackeem`@`%` TRIGGER `questionnaire_control_new_update_trigger` AFTER UPDATE ON `QuestionnaireControlNew`
 FOR EACH ROW BEGIN
   INSERT INTO `QuestionnaireControlNewMH`(`serNum`, `name_EN`, `name_FR`, `private`, `publish`, `created`, `last_updated_by`, `created_by`, `session_id`, `modification_action`) VALUES (NEW.serNum, NEW.name_EN, NEW.name_FR, NEW.private, NEW.publish, NOW(), NEW.last_updated_by, NEW.created_by, NEW.session_id, 'UPDATE');
END