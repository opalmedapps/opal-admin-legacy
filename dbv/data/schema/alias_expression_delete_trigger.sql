CREATE TRIGGER `alias_expression_delete_trigger` AFTER DELETE ON `AliasExpression`
 FOR EACH ROW BEGIN
   INSERT INTO `AliasExpressionMH`(`AliasSerNum`, `ExpressionName`, `Description`, `LastTransferred`, `LastUpdatedBy`, `SessionId`, ModificationAction, DateAdded) VALUES (OLD.AliasSerNum, OLD.ExpressionName, OLD.Description, OLD.LastTransferred, OLD.LastUpdatedBy, OLD.SessionId, 'DELETE', NOW());
END