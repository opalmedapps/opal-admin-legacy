CREATE TRIGGER `test_result_expression_delete_trigger` AFTER DELETE ON `TestResultExpression`
 FOR EACH ROW BEGIN
   INSERT INTO `TestResultExpressionMH`(`TestResultControlSerNum`,`ExpressionName`,`LastPublished`, `LastUpdatedBy`, `SessionId`, ModificationAction, DateAdded) VALUES (OLD.TestResultControlSerNum, OLD.ExpressionName, OLD.LastPublished, OLD.LastUpdatedBy, OLD.SessionId, 'DELETE', NOW());
END