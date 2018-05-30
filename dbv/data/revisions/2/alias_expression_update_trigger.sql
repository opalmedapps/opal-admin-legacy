CREATE TRIGGER `alias_expression_update_trigger` AFTER UPDATE ON `AliasExpression`
 FOR EACH ROW BEGIN
if NEW.LastTransferred <=> OLD.LastTransferred THEN
   INSERT INTO `AliasExpressionMH`(`AliasSerNum`, `ExpressionName`, Description, `LastTransferred`, `LastUpdatedBy`, `SessionId`, ModificationAction, DateAdded) VALUES (NEW.AliasSerNum, NEW.ExpressionName, NEW.Description, NEW.LastTransferred, NEW.LastUpdatedBy, NEW.SessionId, 'UPDATE', NOW());
END IF;
END