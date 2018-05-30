CREATE TRIGGER `filter_delete_trigger` AFTER DELETE ON `Filters`
 FOR EACH ROW BEGIN
   INSERT INTO `FiltersMH`(`FilterSerNum`,`ControlTable`, `ControlTableSerNum`, `FilterType`, `FilterId`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (OLD.FilterSerNum, OLD.ControlTable, OLD.ControlTableSerNum, OLD.FilterType, OLD.FilterId, OLD.LastUpdatedBy, OLD.SessionId, 'DELETE', NOW());
END