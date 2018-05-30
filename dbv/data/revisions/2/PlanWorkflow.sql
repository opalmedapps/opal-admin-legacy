CREATE TABLE `PlanWorkflow` (
  `PlanWorkflowSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PlanSerNum` int(11) NOT NULL,
  `OrderNum` int(11) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `TypeSerNum` int(11) NOT NULL,
  `PublishedName_EN` varchar(255) NOT NULL,
  `PublishedName_FR` varchar(255) NOT NULL,
  `PublishedDescription_EN` varchar(255) NOT NULL,
  `PublishedDescription_FR` varchar(255) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PlanWorkflowSerNum`),
  UNIQUE KEY `PlanSerNum` (`PlanSerNum`,`OrderNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1