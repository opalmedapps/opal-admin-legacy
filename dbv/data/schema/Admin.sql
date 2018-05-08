CREATE TABLE `Admin` (
  `AdminSerNum` int(11) NOT NULL,
  `ResourceSerNum` int(11) NOT NULL,
  `FirstName` text NOT NULL,
  `LastName` text NOT NULL,
  `Email` text NOT NULL,
  `Phone` bigint(20) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `ResourceSerNum` (`ResourceSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1