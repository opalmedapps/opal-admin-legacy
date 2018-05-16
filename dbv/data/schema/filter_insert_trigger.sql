CREATE TRIGGER `filter_insert_trigger` AFTER INSERT ON `Filters`
 FOR EACH ROW BEGIN
   INSERT INTO `FiltersMH`(`FilterSerNum`,`ControlTable`, `ControlTableSerNum`, `FilterType`, `FilterId`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (NEW.FilterSerNum, NEW.ControlTable, NEW.ControlTableSerNum, NEW.FilterType, NEW.FilterId, NEW.LastUpdatedBy, NEW.SessionId, 'INSERT', NOW());
END