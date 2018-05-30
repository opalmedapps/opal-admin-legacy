CREATE TRIGGER `test_result_expression_insert_trigger` AFTER INSERT ON `TestResultExpression`
 FOR EACH ROW BEGIN
   INSERT INTO `TestResultExpressionMH`(`TestResultControlSerNum`,`ExpressionName`,`LastPublished`, `LastUpdatedBy`, `SessionId`, ModificationAction, DateAdded) VALUES (NEW.TestResultControlSerNum, NEW.ExpressionName, NEW.LastPublished, NEW.LastUpdatedBy, NEW.SessionId, 'INSERT', NOW());
END