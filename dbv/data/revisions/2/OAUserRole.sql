CREATE TABLE `OAUserRole` (
  `OAUserSerNum` int(11) NOT NULL,
  `RoleSerNum` int(11) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`OAUserSerNum`,`RoleSerNum`),
  KEY `OAUserSerNum` (`OAUserSerNum`),
  KEY `RoleSerNum` (`RoleSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1