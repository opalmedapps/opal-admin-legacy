DROP TRIGGER `notification_delete_trigger`;
DROP TRIGGER `notification_insert_trigger`;
DROP TRIGGER `notification_update_trigger`;
DROP TRIGGER `announcement_insert_trigger`;
DROP TRIGGER `document_insert_trigger`;
DROP TRIGGER `document_update_trigger`;
DROP TRIGGER `educationalmaterial_insert_trigger`;
DROP TRIGGER `legacy_questionnaire_insert_trigger`;
DROP TRIGGER `patients_for_patients_insert_trigger`;
DROP TRIGGER `txteammessage_insert_trigger`;

DELIMITER $$
CREATE TRIGGER `notification_delete_trigger` AFTER DELETE ON `Notification` FOR EACH ROW BEGIN
	INSERT INTO `NotificationMH`(`NotificationSerNum`, `CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	VALUES (OLD.NotificationSerNum, OLD.CronLogSerNum, OLD.PatientSerNum, OLD.NotificationControlSerNum, OLD.RefTableRowSerNum, OLD.ReadStatus, NOW(), 'DELETE', OLD.RefTableRowTitle_EN, OLD.RefTableRowTitle_FR);
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `notification_insert_trigger` AFTER INSERT ON `Notification` FOR EACH ROW BEGIN
	INSERT INTO `NotificationMH`(`NotificationSerNum`, `CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	VALUES (NEW.NotificationSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.NotificationControlSerNum, NEW.RefTableRowSerNum, NEW.ReadStatus, NOW(), 'INSERT', NEW.RefTableRowTitle_EN, NEW.RefTableRowTitle_FR);
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `notification_update_trigger` AFTER UPDATE ON `Notification` FOR EACH ROW BEGIN
	INSERT INTO `NotificationMH`(`NotificationSerNum`, `CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	VALUES (NEW.NotificationSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.NotificationControlSerNum, NEW.RefTableRowSerNum, NEW.ReadStatus, NOW(), 'UPDATE', NEW.RefTableRowTitle_EN, NEW.RefTableRowTitle_FR);
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `announcement_insert_trigger` AFTER INSERT ON `Announcement` FOR EACH ROW BEGIN
	INSERT INTO `AnnouncementMH`(`AnnouncementSerNum`,`CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)
		VALUES (NEW.AnnouncementSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');

	INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
		SELECT NEW.CronLogSerNum, NEW.PatientSerNum, ntc.NotificationControlSerNum, NEW.AnnouncementSerNum, NOW(), 0,
				getRefTableRowTitle(NEW.PostControlSerNum, 'POST', 'EN') EN, getRefTableRowTitle(NEW.PostControlSerNum, 'POST', 'FR') FR
		FROM NotificationControl ntc
		WHERE ntc.NotificationType = 'Announcement';
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `document_insert_trigger` AFTER INSERT ON `Document` FOR EACH ROW BEGIN
	INSERT INTO `DocumentMH`(`DocumentSerNum`, `DocumentRevSerNum`, `CronLogSerNum`, `SessionId`, `PatientSerNum`, `SourceDatabaseSerNum`, `DocumentId`, `AliasExpressionSerNum`, `ApprovedBySerNum`,
					`ApprovedTimeStamp`, `AuthoredBySerNum`, `DateOfService`, `Revised`, `ValidEntry`, `ErrorReasonText`, `OriginalFileName`, `FinalFileName`, `CreatedBySerNum`, `CreatedTimeStamp`,
					`TransferStatus`, `TransferLog`, `ReadStatus`, `DateAdded`, `LastUpdated`, `ModificationAction`)
	VALUES (NEW.DocumentSerNum,NULL,NEW.CronLogSerNum, NULL,NEW.PatientSerNum,NEW.SourceDatabaseSerNum,NEW.DocumentId,NEW.AliasExpressionSerNum,NEW.ApprovedBySerNum,NEW.ApprovedTimeStamp,
				NEW.AuthoredBySerNum, NEW.DateOfService, NEW.Revised, NEW.ValidEntry,NEW.ErrorReasonText,NEW.OriginalFileName,NEW.FinalFileName, NEW.CreatedBySerNum, NEW.CreatedTimeStamp,
				NEW.TransferStatus,NEW.TransferLog, NEW.ReadStatus, NEW.DateAdded, NOW(), 'INSERT');

	INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	SELECT  NEW.PatientSerNum, ntc.NotificationControlSerNum, NEW.DocumentSerNum, NOW(), 0,
				getRefTableRowTitle(NEW.DocumentSerNum, 'DOCUMENT', 'EN') EN, getRefTableRowTitle(NEW.DocumentSerNum, 'DOCUMENT', 'FR') FR
	FROM NotificationControl ntc, Patient pt
	WHERE ntc.NotificationType = 'Document'
		AND pt.PatientSerNum = NEW.PatientSerNum
		AND pt.AccessLevel = 3;
END$$
DELIMITER ;

DELIMITER $$
CREATE  TRIGGER `document_update_trigger` AFTER UPDATE ON `Document` FOR EACH ROW BEGIN
	INSERT INTO `DocumentMH`(`DocumentSerNum`, `DocumentRevSerNum`, `SessionId`, `CronLogSerNum`, `PatientSerNum`, `SourceDatabaseSerNum`, `DocumentId`, `AliasExpressionSerNum`,
									`ApprovedBySerNum`, `ApprovedTimeStamp`, `AuthoredBySerNum`, `DateOfService`, `Revised`, `ValidEntry`, `ErrorReasonText`, `OriginalFileName`, `FinalFileName`,
									`CreatedBySerNum`, `CreatedTimeStamp`, `TransferStatus`, `TransferLog`, `ReadStatus`, `DateAdded`, `LastUpdated`, `ModificationAction`)
	VALUES (NEW.DocumentSerNum, NULL,NEW.SessionId, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.SourceDatabaseSerNum, NEW.DocumentId, NEW.AliasExpressionSerNum, NEW.ApprovedBySerNum,
				NEW.ApprovedTimeStamp, NEW.AuthoredBySerNum, NEW.DateOfService, NEW.Revised, NEW.ValidEntry, NEW.ErrorReasonText, NEW.OriginalFileName, NEW.FinalFileName, NEW.CreatedBySerNum,
				NEW.CreatedTimeStamp, NEW.TransferStatus, NEW.TransferLog, NEW.ReadStatus, NEW.DateAdded, NOW(), 'UPDATE');


	INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	SELECT  NEW.PatientSerNum, ntc.NotificationControlSerNum, NEW.DocumentSerNum, NOW(), 0,
				getRefTableRowTitle(NEW.DocumentSerNum, 'DOCUMENT', 'EN') EN, getRefTableRowTitle(NEW.DocumentSerNum, 'DOCUMENT', 'FR') FR
	FROM NotificationControl ntc, Patient pt
	WHERE ntc.NotificationType = 'UpdDocument'
		AND NEW.ReadStatus = 0
		AND pt.PatientSerNum = NEW.PatientSerNum
		AND pt.AccessLevel = 3;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `educationalmaterial_insert_trigger` AFTER INSERT ON `EducationalMaterial` FOR EACH ROW BEGIN
	INSERT INTO `EducationalMaterialMH`(`EducationalMaterialSerNum`, `CronLogSerNum`, `EducationalMaterialControlSerNum`, `PatientSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)
	VALUES (NEW.EducationalMaterialSerNum, NEW.CronLogSerNum, NEW.EducationalMaterialControlSerNum, NEW.PatientSerNum, NOW(), NEW.ReadStatus, 'INSERT');

	INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	SELECT  NEW.CronLogSerNum, NEW.PatientSerNum, ntc.NotificationControlSerNum, NEW.EducationalMaterialSerNum, NOW(), 0,
				getRefTableRowTitle(NEW.EducationalMaterialSerNum, 'EDUCATIONAL', 'EN') EN, getRefTableRowTitle(NEW.EducationalMaterialSerNum, 'EDUCATIONAL', 'FR') FR
	FROM NotificationControl ntc
	WHERE ntc.NotificationType = 'EducationalMaterial';
END$$
DELIMITER ;

DELIMITER $$
CREATE  TRIGGER `legacy_questionnaire_insert_trigger` AFTER INSERT ON `Questionnaire` FOR EACH ROW BEGIN
	INSERT INTO QuestionnaireMH (`QuestionnaireSerNum`, `CronLogSerNum`, `QuestionnaireControlSerNum`, `PatientSerNum`, `PatientQuestionnaireDBSerNum`, `CompletedFlag`, `CompletionDate`,
			`DateAdded`, ModificationAction)
	VALUES (NEW.QuestionnaireSerNum, NEW.CronLogSerNum, NEW.QuestionnaireControlSerNum, NEW.PatientSerNum, NEW.PatientQuestionnaireDBSerNum, NEW.CompletedFlag, NEW.CompletionDate,
			NOW(), 'INSERT');


	INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	SELECT NEW.CronLogSerNum, NEW.PatientSerNum, ntc.NotificationControlSerNum, NEW.QuestionnaireSerNum, NOW(), 0,
				getRefTableRowTitle(NEW.QuestionnaireControlSerNum, 'QUESTIONNAIRE', 'EN') EN, getRefTableRowTitle(NEW.QuestionnaireControlSerNum, 'QUESTIONNAIRE', 'FR') FR
	FROM NotificationControl ntc
	WHERE ntc.NotificationType = 'LegacyQuestionnaire';
END$$
DELIMITER ;

DELIMITER $$
CREATE  TRIGGER `patients_for_patients_insert_trigger` AFTER INSERT ON `PatientsForPatients` FOR EACH ROW BEGIN
	INSERT INTO `PatientsForPatientsMH`(`PatientsForPatientsSerNum`, `CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)
	VALUES (NEW.PatientsForPatientsSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');


	INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	SELECT NEW.CronLogSerNum, NEW.PatientSerNum, ntc.NotificationControlSerNum, NEW.PatientsForPatientsSerNum, NOW(), 0,
				getRefTableRowTitle(NEW.PostControlSerNum, 'POST', 'EN') EN, getRefTableRowTitle(NEW.PostControlSerNum, 'POST', 'FR') FR
	FROM NotificationControl ntc
	WHERE ntc.NotificationType = 'PatientsForPatients';
END$$
DELIMITER ;

DELIMITER $$
CREATE  TRIGGER `txteammessage_insert_trigger` AFTER INSERT ON `TxTeamMessage` FOR EACH ROW BEGIN
	INSERT INTO `TxTeamMessageMH`(`TxTeamMessageSerNum`, `CronLogSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)
	VALUES (NEW.TxTeamMessageSerNum, NEW.CronLogSerNum, NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');

	INSERT INTO `Notification` (`CronLogSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
	SELECT NEW.CronLogSerNum, NEW.PatientSerNum, ntc.NotificationControlSerNum, NEW.TxTeamMessageSerNum, NOW(), 0,
				getRefTableRowTitle(NEW.PostControlSerNum, 'POST', 'EN') EN, getRefTableRowTitle(NEW.PostControlSerNum, 'POST', 'FR') FR
	FROM NotificationControl ntc
	WHERE ntc.NotificationType = 'TxTeamMessage';
END$$
DELIMITER ;
