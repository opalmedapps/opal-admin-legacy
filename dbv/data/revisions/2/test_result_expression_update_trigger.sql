CREATE TRIGGER `test_result_expression_update_trigger` AFTER UPDATE ON `TestResultExpression`
 FOR EACH ROW BEGIN
if NEW.LastPublished <=> OLD.LastPublished THEN
   INSERT INTO `TestResultExpressionMH`(`TestResultControlSerNum`,`ExpressionName`,`LastPublished`, `LastUpdatedBy`, `SessionId`, ModificationAction, DateAdded) VALUES (NEW.TestResultControlSerNum, NEW.ExpressionName, NEW.LastPublished, NEW.LastUpdatedBy, NEW.SessionId, 'UPDATE', NOW());
END IF;
END