CREATE TABLE `PatientActionLog` (
  `PatientActionLogSerNum` bigint(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `Action` varchar(125) NOT NULL DEFAULT '' COMMENT 'Action the user took.',
  `RefTable` varchar(125) NOT NULL DEFAULT '' COMMENT 'Table containing the item that was acted upon.',
  `RefTableSerNum` int(11) NOT NULL COMMENT 'SerNum identifying the item in RefTable.',
  `ActionTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when the user took the action.',
  PRIMARY KEY (`PatientActionLogSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `RefTable` (`RefTable`),
  KEY `ActionTime` (`ActionTime`),
  CONSTRAINT `PatientActionLog_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Log of the actions a user takes in the app (clicking, scrolling to bottom, etc.)';
