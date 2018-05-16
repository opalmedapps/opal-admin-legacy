CREATE TRIGGER `alias_expression_insert_trigger` AFTER INSERT ON `AliasExpression`
 FOR EACH ROW BEGIN
   INSERT INTO `AliasExpressionMH`(`AliasSerNum`, `ExpressionName`, `Description`, `LastTransferred`, `LastUpdatedBy`, `SessionId`, ModificationAction, DateAdded) VALUES (NEW.AliasSerNum, NEW.ExpressionName, NEW.Description, NEW.LastTransferred, NEW.LastUpdatedBy, NEW.SessionId, 'INSERT', NOW());
END