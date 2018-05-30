CREATE TABLE `AllowableExtension` (
  `Type` enum('video','website','pdf') NOT NULL,
  `Name` varchar(50) NOT NULL,
  PRIMARY KEY (`Type`,`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1