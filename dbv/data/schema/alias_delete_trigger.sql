CREATE TRIGGER `alias_delete_trigger` AFTER DELETE ON `Alias`
 FOR EACH ROW BEGIN
   INSERT INTO `AliasMH`(`AliasSerNum`, `AliasType`, `AliasUpdate`, `AliasName_FR`, `AliasName_EN`, `AliasDescription_FR`, `AliasDescription_EN`, `EducationalMaterialControlSerNum`, `SourceDatabaseSerNum`, `ColorTag`, `LastTransferred`, `LastUpdatedBy`, `SessionId`, `ModificationAction`, `DateAdded`) VALUES (OLD.AliasSerNum, OLD.AliasType, OLD.AliasUpdate, OLD.AliasName_FR, OLD.AliasName_EN, OLD.AliasDescription_FR, OLD.AliasDescription_EN, OLD.EducationalMaterialControlSerNum, OLD.SourceDatabaseSerNum, OLD.ColorTag, OLD.LastTransferred, NULL, NULL, 'DELETE', NOW());
END