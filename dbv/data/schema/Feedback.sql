CREATE TABLE `Feedback` (
  `FeedbackSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `FeedbackContent` text,
  `AppRating` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`FeedbackSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `AppRating` (`AppRating`),
  CONSTRAINT `Feedback_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1