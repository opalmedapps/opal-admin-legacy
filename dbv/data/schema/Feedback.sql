CREATE TABLE `Feedback` (
  `FeedbackSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `FeedbackContent` varchar(255) DEFAULT NULL,
  `AppRating` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`FeedbackSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1