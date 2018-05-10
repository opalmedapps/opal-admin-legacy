CREATE TABLE `PatientActivityLog` (
  `ActivitySerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Request` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `DeviceId` varchar(255) NOT NULL COMMENT 'This will have information about the previous and current values of fields',
  `SessionId` text NOT NULL,
  `DateTime` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ActivitySerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1