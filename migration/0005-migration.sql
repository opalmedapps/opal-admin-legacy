# Tracking report for table `QuestionnaireSection`
# 2017-08-22 10:55:41

DROP TABLE IF EXISTS `QuestionnaireSection`;

CREATE TABLE `QuestionnaireSection` (
  `serNum` int(11) NOT NULL AUTO_INCREMENT,
  `name_EN` varchar(256) NOT NULL,
  `name_FR` varchar(256) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`serNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `QuestionnaireSection` CHANGE `serNum` `serNum` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `QuestionnaireSection` CHANGE `last_updated_by` `last_updated_by` INT(11) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `QuestionnaireSection` CHANGE `created_by` `created_by` INT(11) UNSIGNED NULL DEFAULT NULL;
INSERT INTO `QuestionnaireSection` (`serNum`, `name_EN`, `name_FR`, `last_updated`, `created`, `last_updated_by`, `created_by`) VALUES (NULL, 'Test Section', 'Test Section', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL, NULL);

ALTER TABLE  `Questionnaire_patient` CHANGE  `user_serNum`  `user_serNum` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0';

# Tracking report for table `Questionnaire_section`
# 2017-08-22 10:57:10

DROP TABLE IF EXISTS `Questionnaire_section`;

CREATE TABLE `Questionnaire_section` (
  `questionnaire_serNum` int(11) NOT NULL,
  `section_serNum` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `Questionnaire_section` ADD PRIMARY KEY( `questionnaire_serNum`, `section_serNum`);
ALTER TABLE `Questionnaire_section` ADD INDEX(`section_serNum`);
ALTER TABLE `Questionnaire_section` ADD FOREIGN KEY (`section_serNum`) REFERENCES `QuestionnaireSection`(`serNum`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `Questionnaire_section` CHANGE `questionnaire_serNum` `questionnaire_serNum` INT(11) UNSIGNED NOT NULL;
ALTER TABLE `Questionnaire_section` DROP FOREIGN KEY `Questionnaire_section_ibfk_1`;
ALTER TABLE `Questionnaire_section` CHANGE `section_serNum` `section_serNum` INT(11) UNSIGNED NOT NULL;
ALTER TABLE `Questionnaire_section` CHANGE `position` `position` INT(11) UNSIGNED NOT NULL;
ALTER TABLE `Questionnaire_section` CHANGE `last_updated_by` `last_updated_by` INT(11) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `Questionnaire_section` CHANGE `created_by` `created_by` INT(11) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `Questionnaire_section` ADD FOREIGN KEY (`questionnaire_serNum`) REFERENCES `Questionnaire`(`serNum`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `Questionnaire_section` ADD FOREIGN KEY (`section_serNum`) REFERENCES `QuestionnaireSection`(`serNum`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `Questionnaire_questiongroup`  ADD `section_serNum` INT UNSIGNED NOT NULL DEFAULT '1' AFTER `position`;
ALTER TABLE `Questionnaire_questiongroup` CHANGE `section_serNum` `section_serNum` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `Questionnaire_questiongroup` ADD INDEX(`section_serNum`);
ALTER TABLE `Questionnaire_questiongroup` CHANGE `section_serNum` `section_serNum` INT(11) UNSIGNED NOT NULL;
ALTER TABLE `Questionnaire_questiongroup` CHANGE `section_serNum` `section_serNum` INT(11) UNSIGNED NOT NULL DEFAULT '1';

INSERT INTO `NotificationTypes`(`NotificationTypeSerNum`, `NotificationTypeId`, `NotificationTypeName`, `DateAdded`, `LastUpdated`) VALUES (NULL,'Questionnaire','Questionnaire','2017-08-18 10:38:07','2017-08-18 10:38:07');

DROP TRIGGER IF EXISTS `questionnaire_insert_trigger`;
CREATE TRIGGER `questionnaire_insert_trigger` AFTER INSERT ON `Questionnaire_patient`
 FOR EACH ROW BEGIN
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.patient_serNum,ntc.NotificationControlSerNum,NEW.serNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'Questionnaire';
END;



