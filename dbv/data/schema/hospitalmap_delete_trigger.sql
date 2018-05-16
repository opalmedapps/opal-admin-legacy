CREATE TRIGGER `hospitalmap_delete_trigger` AFTER DELETE ON `HospitalMap`
 FOR EACH ROW BEGIN
   INSERT INTO `HospitalMapMH`(`HospitalMapSerNum`, `MapUrl`, `MapURL_EN`, `MapURL_FR`, `FilePath_EN`, `FilePath_FR`, `QRMapAlias`, `QRImageFileName`, `MapName_EN`, `MapDescription_EN`, `MapName_FR`, `MapDescription_FR`, `DateAdded`, `LastUpdatedBy`, `SessionId`, `ModificationAction`) VALUES (OLD.HospitalMapSerNum, OLD.MapUrl, OLD.MapURL_EN, OLD.MapURL_FR, OLD.FilePath_EN, OLD.FilePath_FR, OLD.QRMapAlias, OLD.QRImageFileName, OLD.MapName_EN, OLD.MapDescription_EN, OLD.MapName_FR, OLD.MapDescription_FR, NOW(), OLD.LastUpdatedBy, OLD.SessionId, 'DELETE');
END