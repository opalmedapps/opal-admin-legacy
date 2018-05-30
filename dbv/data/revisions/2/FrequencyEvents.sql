CREATE TABLE `FrequencyEvents` (
  `ControlTable` varchar(50) NOT NULL,
  `ControlTableSerNum` int(11) NOT NULL,
  `MetaKey` varchar(50) NOT NULL,
  `MetaValue` varchar(150) NOT NULL,
  `CustomFlag` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `ControlTable` (`ControlTable`,`ControlTableSerNum`,`MetaKey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1