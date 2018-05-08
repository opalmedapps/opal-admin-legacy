CREATE DEFINER=`ackeem`@`%` TRIGGER `questionnaire_control_new_delete_trigger` AFTER DELETE ON `QuestionnaireControlNew`
 FOR EACH ROW BEGIN
   INSERT INTO `QuestionnaireControlNewMH`(`serNum`, `name_EN`, `name_FR`, `private`, `publish`, `created`, `last_updated_by`, `created_by`, `session_id`, `modification_action`) VALUES (OLD.serNum, OLD.name_EN, OLD.name_FR, OLD.private, OLD.publish, NOW(), OLD.last_updated_by, OLD.created_by, OLD.session_id, 'DELETE');
END