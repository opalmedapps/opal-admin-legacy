CREATE TABLE `TxTeamMessagePermissions` (
  `TxTeamMessagePermissionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `TxTeamMessageSerNum` int(11) DEFAULT NULL,
  `PatientCaregiverSer` int(11) DEFAULT NULL,
  PRIMARY KEY (`TxTeamMessagePermissionSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1