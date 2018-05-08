CREATE TABLE `PatientControl` (
  `PatientSerNum` int(11) NOT NULL,
  `PatientUpdate` int(11) NOT NULL DEFAULT '1',
  `LastTransferred` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientSerNum`),
  CONSTRAINT `PatientControl_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1