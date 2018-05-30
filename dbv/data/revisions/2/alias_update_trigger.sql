CREATE TRIGGER `alias_update_trigger` AFTER UPDATE ON `Alias`
 FOR EACH ROW BEGIN
if NEW.LastTransferred <=> OLD.LastTransferred THEN
   INSERT INTO `AliasMH`(`AliasSerNum`, `AliasType`, `AliasUpdate`, `AliasName_FR`, `AliasName_EN`, `AliasDescription_FR`, `AliasDescription_EN`, `EducationalMaterialControlSerNum`, `SourceDatabaseSerNum`, `ColorTag`, `LastTransferred`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (NEW.AliasSerNum, NEW.AliasType, NEW.AliasUpdate, NEW.AliasName_FR, NEW.AliasName_EN, NEW.AliasDescription_FR, NEW.AliasDescription_EN, NEW.EducationalMaterialControlSerNum, NEW.SourceDatabaseSerNum, NEW.ColorTag, NEW.LastTransferred, NEW.LastUpdatedBy, NEW.SessionId, 'UPDATE', NOW());
END IF;
END