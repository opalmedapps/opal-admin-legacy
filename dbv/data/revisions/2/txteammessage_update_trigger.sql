CREATE TRIGGER `txteammessage_update_trigger` AFTER UPDATE ON `TxTeamMessage`
 FOR EACH ROW BEGIN
INSERT INTO `TxTeamMessageMH`(`TxTeamMessageSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)  VALUES (NEW.TxTeamMessageSerNum,NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'UPDATE');
END