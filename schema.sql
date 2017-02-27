-- MySQL dump 10.13  Distrib 5.5.43, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: OpalDB_AJ_Empty
-- ------------------------------------------------------
-- Server version	5.5.43-0ubuntu0.12.04.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Admin`
--

DROP TABLE IF EXISTS `Admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Admin` (
  `AdminSerNum` int(11) NOT NULL,
  `ResourceSerNum` int(11) NOT NULL,
  `FirstName` text NOT NULL,
  `LastName` text NOT NULL,
  `Email` text NOT NULL,
  `Phone` bigint(20) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `ResourceSerNum` (`ResourceSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Admin`
--

LOCK TABLES `Admin` WRITE;
/*!40000 ALTER TABLE `Admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `Admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Alias`
--

DROP TABLE IF EXISTS `Alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Alias` (
  `AliasSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `AliasType` varchar(25) NOT NULL,
  `AliasUpdate` int(11) NOT NULL,
  `AliasName_FR` varchar(100) NOT NULL,
  `AliasName_EN` varchar(100) NOT NULL,
  `AliasDescription_FR` text NOT NULL,
  `AliasDescription_EN` text NOT NULL,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL DEFAULT '1',
  `ColorTag` varchar(25) NOT NULL DEFAULT '#777777',
  `LastTransferred` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AliasSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Alias`
--

LOCK TABLES `Alias` WRITE;
/*!40000 ALTER TABLE `Alias` DISABLE KEYS */;
/*!40000 ALTER TABLE `Alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AliasExpression`
--

DROP TABLE IF EXISTS `AliasExpression`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AliasExpression` (
  `AliasExpressionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `AliasSerNum` int(11) NOT NULL DEFAULT '0',
  `ExpressionName` varchar(250) NOT NULL,
  `LastTransferred` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AliasExpressionSerNum`),
  UNIQUE KEY `ExpressionName` (`ExpressionName`),
  KEY `AliasSerNum` (`AliasSerNum`),
  CONSTRAINT `AliasExpression_ibfk_1` FOREIGN KEY (`AliasSerNum`) REFERENCES `Alias` (`AliasSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AliasExpression`
--

LOCK TABLES `AliasExpression` WRITE;
/*!40000 ALTER TABLE `AliasExpression` DISABLE KEYS */;
/*!40000 ALTER TABLE `AliasExpression` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Announcement`
--

DROP TABLE IF EXISTS `Announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Announcement` (
  `AnnouncementSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AnnouncementSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `PostSerNum` (`PostControlSerNum`),
  CONSTRAINT `Announcement_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Announcement_ibfk_2` FOREIGN KEY (`PostControlSerNum`) REFERENCES `PostControl` (`PostControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Announcement`
--

LOCK TABLES `Announcement` WRITE;
/*!40000 ALTER TABLE `Announcement` DISABLE KEYS */;
/*!40000 ALTER TABLE `Announcement` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `announcement_insert_trigger` AFTER INSERT ON `Announcement`
 FOR EACH ROW BEGIN
INSERT INTO `AnnouncementMH`(`AnnouncementSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.AnnouncementSerNum,NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.AnnouncementSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'Announcement';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `announcement_update_trigger` AFTER UPDATE ON `Announcement`
 FOR EACH ROW BEGIN
INSERT INTO `AnnouncementMH`(`AnnouncementSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.AnnouncementSerNum,NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `announcement_delete_trigger` AFTER DELETE ON `Announcement`
 FOR EACH ROW BEGIN
INSERT INTO `AnnouncementMH`(`AnnouncementSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (OLD.AnnouncementSerNum,OLD.PatientSerNum, OLD.PostControlSerNum, NOW(), OLD.ReadStatus, 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `AnnouncementMH`
--

DROP TABLE IF EXISTS `AnnouncementMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AnnouncementMH` (
  `AnnouncementSerNum` int(11) NOT NULL,
  `AnnouncementRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AnnouncementSerNum`,`AnnouncementRevSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `PostControlSerNum` (`PostControlSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AnnouncementMH`
--

LOCK TABLES `AnnouncementMH` WRITE;
/*!40000 ALTER TABLE `AnnouncementMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `AnnouncementMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Appointment`
--

DROP TABLE IF EXISTS `Appointment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Appointment` (
  `AppointmentSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `AliasExpressionSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `AppointmentAriaSer` int(11) NOT NULL,
  `PrioritySerNum` int(11) NOT NULL,
  `DiagnosisSerNum` int(11) NOT NULL,
  `Status` varchar(100) NOT NULL,
  `State` varchar(25) NOT NULL,
  `ScheduledStartTime` datetime NOT NULL,
  `ScheduledEndTime` datetime NOT NULL,
  `ActualStartDate` datetime NOT NULL,
  `ActualEndDate` datetime NOT NULL,
  `Location` int(10) NOT NULL DEFAULT '10',
  `RoomLocation_EN` varchar(100) NOT NULL,
  `RoomLocation_FR` varchar(100) NOT NULL,
  `Checkin` tinyint(4) NOT NULL,
  `ChangeRequest` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AppointmentSerNum`),
  KEY `AliasExpressionSerNum` (`AliasExpressionSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `AppointmentAriaSer` (`AppointmentAriaSer`),
  KEY `DiagnosisSerNum` (`DiagnosisSerNum`),
  KEY `PrioritySerNum` (`PrioritySerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  CONSTRAINT `Appointment_ibfk_5` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Appointment_ibfk_3` FOREIGN KEY (`AliasExpressionSerNum`) REFERENCES `AliasExpression` (`AliasExpressionSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Appointment_ibfk_4` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Appointment`
--

LOCK TABLES `Appointment` WRITE;
/*!40000 ALTER TABLE `Appointment` DISABLE KEYS */;
/*!40000 ALTER TABLE `Appointment` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `appointment_insert_trigger` AFTER INSERT ON `Appointment`
 FOR EACH ROW BEGIN
INSERT INTO `AppointmentMH`(`AppointmentSerNum`, `AppointmentRevSerNum`,`SessionId`, `AliasExpressionSerNum`, `PatientSerNum`, `SourceDatabaseSerNum`, `AppointmentAriaSer`, `PrioritySerNum`, `DiagnosisSerNum`, `Status`, `State`, `ScheduledStartTime`, `ScheduledEndTime`, `ActualStartDate`, `ActualEndDate`, `Location`,`RoomLocation_EN`, `RoomLocation_FR`, `Checkin`, `DateAdded`, `ReadStatus`, `LastUpdated`, `ModificationAction`) VALUES (NEW.AppointmentSerNum,NULL,NULL,NEW.AliasExpressionSerNum, NEW.PatientSerNum,NEW.SourceDatabaseSerNum, NEW.AppointmentAriaSer, NEW.PrioritySerNum, NEW.DiagnosisSerNum, NEW.Status, NEW.State, NEW.ScheduledStartTime,NEW.ScheduledEndTime, NEW.ActualStartDate, NEW.ActualEndDate, NEW.Location, NEW.RoomLocation_EN, NEW.RoomLocation_FR, NEW.Checkin, NEW.DateAdded,NEW.ReadStatus,NOW(), 'INSERT');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `appointment_update_trigger` AFTER UPDATE ON `Appointment`
 FOR EACH ROW BEGIN
 INSERT INTO `AppointmentMH`(`AppointmentSerNum`, `AppointmentRevSerNum`,`SessionId`, `AliasExpressionSerNum`, `PatientSerNum`, `SourceDatabaseSerNum`, `AppointmentAriaSer`, `PrioritySerNum`, `DiagnosisSerNum`, `Status`, `State`, `ScheduledStartTime`, `ScheduledEndTime`, `ActualStartDate`, `ActualEndDate`, `Location`, `RoomLocation_EN`, `RoomLocation_FR`, `Checkin`, `DateAdded`, `ReadStatus`, `LastUpdated`,  `ModificationAction`) VALUES (NEW.AppointmentSerNum,NULL,NEW.SessionId,NEW.AliasExpressionSerNum, NEW.PatientSerNum,NEW.SourceDatabaseSerNum,NEW.AppointmentAriaSer,NEW.PrioritySerNum, NEW.DiagnosisSerNum, NEW.Status, NEW.State, NEW.ScheduledStartTime,NEW.ScheduledEndTime, NEW.ActualStartDate, NEW.ActualEndDate, NEW.Location, NEW.RoomLocation_EN, NEW.RoomLocation_FR, NEW.Checkin, NEW.DateAdded,NEW.ReadStatus,NOW(), 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `appointment_delete_trigger` AFTER DELETE ON `Appointment`
 FOR EACH ROW BEGIN
 INSERT INTO `AppointmentMH`(`AppointmentSerNum`, `AppointmentRevSerNum`,`SessionId`, `AliasExpressionSerNum`, `PatientSerNum`, `SourceDatabaseSerNum`, `AppointmentAriaSer`, `PrioritySerNum`, `DiagnosisSerNum`, `Status`, `State`, `ScheduledStartTime`, `ScheduledEndTime`, `ActualStartDate`, `ActualEndDate`, `Location`, `RoomLocation_EN`, `RoomLocation_FR`, `Checkin`, `DateAdded`, `ReadStatus`, `LastUpdated`,  `ModificationAction`) VALUES (OLD.AppointmentSerNum,NULL,OLD.SessionId,OLD.AliasExpressionSerNum, OLD.PatientSerNum,OLD.SourceDatabaseSerNum,OLD.AppointmentAriaSer,OLD.PrioritySerNum, OLD.DiagnosisSerNum, OLD.Status, OLD.State, OLD.ScheduledStartTime,OLD.ScheduledEndTime, OLD.ActualStartDate, OLD.ActualEndDate, OLD.Location, OLD.RoomLocation_EN, OLD.RoomLocation_FR, OLD.Checkin, OLD.DateAdded,OLD.ReadStatus,NOW(), 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `AppointmentMH`
--

DROP TABLE IF EXISTS `AppointmentMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AppointmentMH` (
  `AppointmentSerNum` int(11) NOT NULL,
  `AppointmentRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SessionId` text,
  `AliasExpressionSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `AppointmentAriaSer` int(11) NOT NULL,
  `PrioritySerNum` int(11) NOT NULL,
  `DiagnosisSerNum` int(11) NOT NULL,
  `Status` varchar(100) NOT NULL,
  `State` varchar(25) NOT NULL,
  `ScheduledStartTime` datetime NOT NULL,
  `ScheduledEndTime` datetime NOT NULL,
  `ActualStartDate` datetime NOT NULL,
  `ActualEndDate` datetime NOT NULL,
  `Location` int(10) NOT NULL,
  `RoomLocation_EN` varchar(100) NOT NULL,
  `RoomLocation_FR` varchar(100) NOT NULL,
  `Checkin` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AppointmentSerNum`,`AppointmentRevSerNum`),
  KEY `AliasExpressionSerNum` (`AliasExpressionSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `AppointmentAriaSer` (`AppointmentAriaSer`),
  KEY `PrioritySerNum` (`PrioritySerNum`),
  KEY `DiagnosisSerNum` (`DiagnosisSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AppointmentMH`
--

LOCK TABLES `AppointmentMH` WRITE;
/*!40000 ALTER TABLE `AppointmentMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `AppointmentMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CheckinLog`
--

DROP TABLE IF EXISTS `CheckinLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CheckinLog` (
  `CheckinLogSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `AppointmentSerNum` int(11) NOT NULL,
  `DeviceId` varchar(100) NOT NULL,
  `Latitude` double NOT NULL COMMENT 'In meters, from 45.474127399999996, -73.6011402',
  `Longitude` double NOT NULL,
  `Accuracy` double NOT NULL COMMENT 'Accuracy in meters',
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`CheckinLogSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CheckinLog`
--

LOCK TABLES `CheckinLog` WRITE;
/*!40000 ALTER TABLE `CheckinLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `CheckinLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Cron`
--

DROP TABLE IF EXISTS `Cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cron` (
  `CronSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `NextCronDate` date NOT NULL,
  `RepeatUnits` varchar(50) NOT NULL,
  `NextCronTime` time NOT NULL,
  `RepeatInterval` int(11) NOT NULL,
  `LastCron` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`CronSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Cron`
--

LOCK TABLES `Cron` WRITE;
/*!40000 ALTER TABLE `Cron` DISABLE KEYS */;
INSERT INTO `Cron` VALUES (1,'2017-02-27','Minutes','14:00:00',5,'2017-02-27 19:12:30');
/*!40000 ALTER TABLE `Cron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CronLog`
--

DROP TABLE IF EXISTS `CronLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CronLog` (
  `CronLogSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronSerNum` int(11) NOT NULL,
  `CronStatus` varchar(25) NOT NULL,
  `CronDateTime` datetime NOT NULL,
  PRIMARY KEY (`CronLogSerNum`),
  KEY `CronSerNum` (`CronSerNum`),
  CONSTRAINT `CronLog_ibfk_1` FOREIGN KEY (`CronSerNum`) REFERENCES `Cron` (`CronSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CronLog`
--

LOCK TABLES `CronLog` WRITE;
/*!40000 ALTER TABLE `CronLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `CronLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Diagnosis`
--

DROP TABLE IF EXISTS `Diagnosis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Diagnosis` (
  `DiagnosisSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `DiagnosisAriaSer` varchar(32) NOT NULL,
  `DiagnosisCode` varchar(50) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Description_EN` varchar(200) NOT NULL,
  `Description_FR` varchar(255) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DiagnosisSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `DiagnosisAriaSer` (`DiagnosisAriaSer`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  CONSTRAINT `Diagnosis_ibfk_2` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Diagnosis_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Diagnosis`
--

LOCK TABLES `Diagnosis` WRITE;
/*!40000 ALTER TABLE `Diagnosis` DISABLE KEYS */;
/*!40000 ALTER TABLE `Diagnosis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DiagnosisTranslation`
--

DROP TABLE IF EXISTS `DiagnosisTranslation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DiagnosisTranslation` (
  `DiagnosisTranslationSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `AliasName` varchar(100) NOT NULL,
  `DiagnosisCode` varchar(100) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DiagnosisTranslationSerNum`),
  KEY `DiagnosisCode` (`DiagnosisCode`)
) ENGINE=InnoDB AUTO_INCREMENT=1129 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DiagnosisTranslation`
--

LOCK TABLES `DiagnosisTranslation` WRITE;
/*!40000 ALTER TABLE `DiagnosisTranslation` DISABLE KEYS */;
INSERT INTO `DiagnosisTranslation` VALUES (1,'HPB','C22.0','2015-09-23 16:41:15'),(2,'HPB','C22.0a','2015-09-23 16:41:15'),(3,'HPB','C22.0p','2015-09-23 16:41:15'),(4,'HPB','C22.0r','2015-09-23 16:41:15'),(5,'HPB','C22.1','2015-09-23 16:41:15'),(6,'HPB','C22.7','2015-09-23 16:41:15'),(7,'HPB','C22.9','2015-09-23 16:41:15'),(8,'HPB','C23','2015-09-23 16:41:15'),(9,'HPB','C23.9','2015-09-23 16:41:15'),(10,'HPB','C24.0','2015-09-23 16:41:15'),(11,'HPB','C24.1','2015-09-23 16:41:15'),(12,'HPB','C24.8','2015-09-23 16:41:15'),(13,'HPB','C24.9','2015-09-23 16:41:15'),(14,'HPB','C25.0','2015-09-23 16:41:15'),(15,'HPB','C25.0a','2015-09-23 16:41:15'),(16,'HPB','C25.1','2015-09-23 16:41:15'),(17,'HPB','C25.2','2015-09-23 16:41:15'),(18,'HPB','C25.3','2015-09-23 16:41:15'),(19,'HPB','C25.7','2015-09-23 16:41:15'),(20,'HPB','C25.9','2015-09-23 16:41:15'),(21,'HPB','D01.5','2015-09-23 16:44:37'),(22,'HPB','D01.7','2015-09-23 16:44:37'),(23,'HPB','D37.6','2015-09-23 16:44:37'),(24,'HPB','C74.1','2015-09-23 16:44:37'),(25,'HPB','C74.9','2015-09-23 16:44:37'),(26,'HPB','C74.9a','2015-09-23 16:44:37'),(27,'Gyne','C51.0','2015-09-23 16:49:30'),(28,'Gyne','C51.1','2015-09-23 16:49:30'),(29,'Gyne','C51.2','2015-09-23 16:49:30'),(30,'Gyne','C51.8','2015-09-23 16:49:30'),(31,'Gyne','C51.9','2015-09-23 16:49:30'),(32,'Gyne','C51.9a','2015-09-23 16:49:30'),(33,'Gyne','C51.9p','2015-09-23 16:49:30'),(34,'Gyne','C51.9r','2015-09-23 16:49:30'),(35,'Gyne','C51.9s','2015-09-23 16:49:30'),(36,'Gyne','C52','2015-09-23 16:49:30'),(37,'Gyne','C52.9','2015-09-23 16:49:30'),(38,'Gyne','C52.9p','2015-09-23 16:49:30'),(39,'Gyne','C52.9q','2015-09-23 16:49:30'),(40,'Gyne','C52.9r','2015-09-23 16:49:30'),(41,'Gyne','C53.0','2015-09-23 16:49:30'),(42,'Gyne','C53.1','2015-09-23 16:49:30'),(43,'Gyne','C53.8','2015-09-23 16:49:30'),(44,'Gyne','C53.9','2015-09-23 16:49:30'),(45,'Gyne','C53.9a','2015-09-23 16:49:30'),(46,'Gyne','C53.9b','2015-09-23 16:49:30'),(47,'Gyne','C53.9p','2015-09-23 18:09:59'),(48,'Gyne','C53.9r','2015-09-23 18:09:59'),(49,'Gyne','C54.1','2015-09-23 18:09:59'),(50,'Gyne','C54.10','2015-09-23 18:09:59'),(51,'Gyne','C54.1p','2015-09-23 18:09:59'),(52,'Gyne','C54.1r','2015-09-23 18:09:59'),(53,'Gyne','C54.2','2015-09-23 18:09:59'),(54,'Gyne','C54.3','2015-09-23 18:09:59'),(55,'Gyne','C54.8','2015-09-23 18:09:59'),(56,'Gyne','C54.9','2015-09-23 18:09:59'),(57,'Gyne','C55','2015-09-23 18:09:59'),(58,'Gyne','C55.9','2015-09-23 18:09:59'),(59,'Gyne','C56','2015-09-23 18:09:59'),(60,'Gyne','C56.9','2015-09-23 18:09:59'),(61,'Gyne','C56.9p','2015-09-23 18:09:59'),(62,'Gyne','C57.0','2015-09-23 18:09:59'),(63,'Gyne','C57.4','2015-09-23 18:09:59'),(64,'Gyne','C57.8','2015-09-23 18:09:59'),(65,'Gyne','C57.9','2015-09-23 18:09:59'),(66,'Gyne','C58','2015-09-23 18:09:59'),(67,'Gyne','D06.9','2015-09-23 18:10:32'),(68,'Gyne','D07.0','2015-09-23 18:11:51'),(69,'Gyne','D07.1','2015-09-23 18:11:51'),(70,'Gyne','D07.2','2015-09-23 18:11:51'),(71,'Gyne','D07.3','2015-09-23 18:11:51'),(72,'Gyne','D39.9','2015-09-23 18:11:51'),(73,'Haeme','C81.0','2015-09-23 18:17:59'),(74,'Haeme','C81.1','2015-09-23 18:17:59'),(75,'Haeme','C81.2','2015-09-23 18:17:59'),(76,'Haeme','C81.7','2015-09-23 18:17:59'),(77,'Haeme','C81.9','2015-09-23 18:17:59'),(78,'Haeme','C82.0','2015-09-23 18:17:59'),(79,'Haeme','C82.1','2015-09-23 18:17:59'),(80,'Haeme','C82.2','2015-09-23 18:17:59'),(81,'Haeme','C82.7','2015-09-23 18:17:59'),(82,'Haeme','C82.9','2015-09-23 18:17:59'),(83,'Haeme','C83.0','2015-09-23 18:17:59'),(84,'Haeme','C83.3','2015-09-23 18:17:59'),(85,'Haeme','C83.5','2015-09-23 18:17:59'),(86,'Haeme','C83.6','2015-09-23 18:17:59'),(87,'Haeme','C83.8','2015-09-23 18:17:59'),(88,'Haeme','C83.9','2015-09-23 18:17:59'),(89,'Haeme','C84.0','2015-09-23 18:17:59'),(90,'Haeme','C84.1','2015-09-23 18:17:59'),(91,'Haeme','C84.4','2015-09-23 18:17:59'),(92,'Haeme','C84.5','2015-09-23 18:17:59'),(93,'Haeme','C85.0','2015-09-23 18:17:59'),(94,'Haeme','C85.1','2015-09-23 18:17:59'),(95,'Haeme','C85.7','2015-09-23 18:17:59'),(96,'Haeme','C85.9','2015-09-23 18:17:59'),(97,'Haeme','C88.0','2015-09-23 18:17:59'),(98,'Haeme','C90.0','2015-09-23 18:17:59'),(99,'Haeme','C90.1','2015-09-23 18:17:59'),(100,'Haeme','C90.2','2015-09-23 18:17:59'),(101,'Haeme','C91.0','2015-09-23 18:17:59'),(102,'Haeme','C91.00','2015-09-23 18:17:59'),(103,'Haeme','C91.1','2015-09-23 18:17:59'),(104,'Haeme','C91.3','2015-09-23 18:17:59'),(105,'Haeme','C92.0','2015-09-23 18:17:59'),(106,'Haeme','C92.1','2015-09-23 18:17:59'),(107,'Haeme','C92.3','2015-09-23 18:17:59'),(108,'Haeme','C92.4','2015-09-23 18:17:59'),(109,'Haeme','C94.5','2015-09-23 18:17:59'),(110,'Haeme','C95.0','2015-09-23 18:17:59'),(111,'Haeme','C95.9','2015-09-23 18:17:59'),(112,'Haeme','C96.1','2015-09-23 18:17:59'),(113,'Haeme','C96.7','2015-09-23 18:18:18'),(114,'Haeme','C96.9','2015-09-23 18:18:18'),(115,'Haeme','D46.9','2015-09-23 18:18:49'),(116,'Haeme','C77.0','2015-09-23 18:23:00'),(117,'Haeme','C77.0a','2015-09-23 18:23:00'),(118,'Haeme','C77.0p','2015-09-23 18:23:00'),(119,'Haeme','C77.0q','2015-09-23 18:23:00'),(120,'Haeme','C77.0r','2015-09-23 18:23:00'),(121,'Haeme','C77.0s','2015-09-23 18:23:00'),(122,'Haeme','C77.1','2015-09-23 18:23:00'),(123,'Haeme','C77.1r','2015-09-23 18:23:00'),(124,'Haeme','C77.2','2015-09-23 18:23:00'),(125,'Haeme','C77.2a','2015-09-23 18:23:00'),(126,'Haeme','C77.2p','2015-09-23 18:23:00'),(127,'Haeme','C77.2r','2015-09-23 18:23:00'),(128,'Haeme','C77.3','2015-09-23 18:23:00'),(129,'Haeme','C77.3a','2015-09-23 18:23:00'),(130,'Haeme','C77.3b','2015-09-23 18:23:00'),(131,'Haeme','C77.3p','2015-09-23 18:23:00'),(132,'Haeme','C77.3r','2015-09-23 18:23:00'),(133,'Haeme','C77.4','2015-09-23 18:23:00'),(134,'Haeme','C77.4a','2015-09-23 18:23:00'),(135,'Haeme','C77.4p','2015-09-23 18:23:00'),(136,'Haeme','C77.4r','2015-09-23 18:23:00'),(137,'Haeme','C77.4s','2015-09-23 18:23:00'),(138,'Haeme','C77.5','2015-09-23 18:23:00'),(139,'Haeme','C77.5a','2015-09-23 18:23:00'),(140,'Haeme','C77.5p','2015-09-23 18:23:00'),(141,'Haeme','C77.5r','2015-09-23 18:23:00'),(142,'Haeme','C77.8','2015-09-23 18:23:00'),(143,'Haeme','C77.8a','2015-09-23 18:23:00'),(144,'Haeme','C77.8p','2015-09-23 18:23:00'),(145,'Haeme','C77.8r','2015-09-23 18:23:00'),(146,'Haeme','C77.9','2015-09-23 18:23:27'),(147,'Haeme','C77.9a','2015-09-23 18:23:27'),(148,'Haeme','C77.9r','2015-09-23 18:23:27'),(149,'HN','C00.0','2015-09-23 18:29:46'),(150,'HN','C00.1','2015-09-23 18:29:46'),(151,'HN','C00.3','2015-09-23 18:29:46'),(152,'HN','C00.5','2015-09-23 18:29:46'),(153,'HN','C00.6','2015-09-23 18:29:46'),(154,'HN','C00.9','2015-09-23 18:29:46'),(155,'HN','C01','2015-09-23 18:29:46'),(156,'HN','C01.9','2015-09-23 18:29:46'),(157,'HN','C01.9p','2015-09-23 18:29:46'),(158,'HN','C02.1','2015-09-23 18:29:46'),(159,'HN','C02.1p','2015-09-23 18:29:46'),(160,'HN','C02.1q','2015-09-23 18:29:46'),(161,'HN','C02.2','2015-09-23 18:29:46'),(162,'HN','C02.3','2015-09-23 18:29:46'),(163,'HN','C02.3p','2015-09-23 18:29:46'),(164,'HN','C02.4','2015-09-23 18:29:46'),(165,'HN','C02.8','2015-09-23 18:29:46'),(166,'HN','C02.9','2015-09-23 18:29:46'),(167,'HN','C02.9p','2015-09-23 18:29:46'),(168,'HN','C02.9q','2015-09-23 18:29:46'),(169,'HN','C03.0','2015-09-23 18:29:46'),(170,'HN','C03.1','2015-09-23 18:29:46'),(171,'HN','C03.9','2015-09-23 18:29:46'),(172,'HN','C03.9p','2015-09-23 18:29:46'),(173,'HN','C03.9q','2015-09-23 18:29:46'),(174,'HN','C04.0','2015-09-23 18:29:46'),(175,'HN','C04.1','2015-09-23 18:29:46'),(176,'HN','C04.8','2015-09-23 18:29:46'),(177,'HN','C04.8p','2015-09-23 18:29:46'),(178,'HN','C04.8q','2015-09-23 18:29:46'),(179,'HN','C04.9','2015-09-23 18:29:46'),(180,'HN','C05.0','2015-09-23 18:29:46'),(181,'HN','C05.1','2015-09-23 18:29:46'),(182,'HN','C05.9','2015-09-23 18:29:46'),(183,'HN','C05.9p','2015-09-23 18:29:46'),(184,'HN','C06.0','2015-09-23 18:29:46'),(185,'HN','C06.2','2015-09-23 18:29:46'),(186,'HN','C06.8','2015-09-23 18:29:46'),(187,'HN','C06.9','2015-09-23 18:29:46'),(188,'HN','C07','2015-09-23 18:29:46'),(189,'HN','C07.9','2015-09-23 18:34:29'),(190,'HN','C07.9a','2015-09-23 18:34:29'),(191,'HN','C07.9b','2015-09-23 18:34:29'),(192,'HN','C07.9p','2015-09-23 18:34:29'),(193,'HN','C07.9q','2015-09-23 18:34:29'),(194,'HN','C07.9r','2015-09-23 18:34:29'),(195,'HN','C07.9s','2015-09-23 18:34:29'),(196,'HN','C08.0','2015-09-23 18:34:29'),(197,'HN','C08.0p','2015-09-23 18:34:29'),(198,'HN','C08.8','2015-09-23 18:34:29'),(199,'HN','C08.9','2015-09-23 18:34:29'),(200,'HN','C09.0','2015-09-23 18:34:29'),(201,'HN','C09.1','2015-09-23 18:34:29'),(202,'HN','C09.8','2015-09-23 18:34:29'),(203,'HN','C09.9','2015-09-23 18:34:29'),(204,'HN','C09.9a','2015-09-23 18:34:29'),(205,'HN','C09.9b','2015-09-23 18:34:29'),(206,'HN','C09.9c','2015-09-23 18:34:29'),(207,'HN','C09.9p','2015-09-23 18:34:29'),(208,'HN','C09.9r','2015-09-23 18:34:29'),(209,'HN','C10.2','2015-09-23 18:34:29'),(210,'HN','C10.3','2015-09-23 18:34:29'),(211,'HN','C10.3r','2015-09-23 18:34:29'),(212,'HN','C10.4','2015-09-23 18:34:29'),(213,'HN','C10.4r','2015-09-23 18:34:29'),(214,'HN','C10.8','2015-09-23 18:34:29'),(215,'HN','C10.9','2015-09-23 18:34:29'),(216,'HN','C10.9r','2015-09-23 18:34:29'),(217,'HN','C11.0','2015-09-23 18:34:29'),(218,'HN','C11.1','2015-09-23 18:34:29'),(219,'HN','C11.2','2015-09-23 18:34:29'),(220,'HN','C11.3','2015-09-23 18:34:29'),(221,'HN','C11.8','2015-09-23 18:34:29'),(222,'HN','C11.9','2015-09-23 18:34:29'),(223,'HN','C11.9p','2015-09-23 18:34:29'),(224,'HN','C11.9r','2015-09-23 18:34:29'),(225,'HN','C12','2015-09-23 18:34:29'),(226,'HN','C12.9','2015-09-23 18:34:29'),(227,'HN','C13.0','2015-09-23 18:34:29'),(228,'HN','C13.2','2015-09-23 18:34:29'),(229,'HN','C13.2p','2015-09-23 18:35:19'),(230,'HN','C13.9','2015-09-23 18:35:19'),(231,'HN','C14.0','2015-09-23 18:35:19'),(232,'HN','C14.0r','2015-09-23 18:35:19'),(233,'HN','C14.8','2015-09-23 18:35:19'),(234,'HN','C30.0','2015-09-23 18:38:13'),(235,'HN','C30.0a','2015-09-23 18:38:13'),(236,'HN','C30.0r','2015-09-23 18:38:13'),(237,'HN','C30.1','2015-09-23 18:38:13'),(238,'HN','C31.0','2015-09-23 18:38:13'),(239,'HN','C31.1','2015-09-23 18:38:13'),(240,'HN','C31.2','2015-09-23 18:38:13'),(241,'HN','C31.2a','2015-09-23 18:38:13'),(242,'HN','C31.2p','2015-09-23 18:38:13'),(243,'HN','C31.3','2015-09-23 18:38:13'),(244,'HN','C31.8','2015-09-23 18:38:13'),(245,'HN','C31.9','2015-09-23 18:38:13'),(246,'HN','C32.0','2015-09-23 18:38:13'),(247,'HN','C32.0a','2015-09-23 18:38:13'),(248,'HN','C32.0b','2015-09-23 18:38:13'),(249,'HN','C32.0p','2015-09-23 18:38:13'),(250,'HN','C32.0r','2015-09-23 18:38:13'),(251,'HN','C32.1','2015-09-23 18:38:13'),(252,'HN','C32.1a','2015-09-23 18:38:13'),(253,'HN','C32.1p','2015-09-23 18:38:13'),(254,'HN','C32.8','2015-09-23 18:38:40'),(255,'HN','C32.9','2015-09-23 18:38:40'),(256,'HN','C32.9p','2015-09-23 18:38:40'),(257,'HN','C73','2015-09-23 18:39:43'),(258,'HN','C73.9','2015-09-23 18:39:43'),(259,'HN','C73.9a','2015-09-23 18:39:43'),(260,'HN','C73.9p','2015-09-23 18:39:43'),(261,'HN','C73.9q','2015-09-23 18:39:43'),(262,'HN','C73.9r','2015-09-23 18:39:43'),(263,'HN','D00.0','2015-09-23 18:45:13'),(264,'HN','D02.0','2015-09-23 18:45:13'),(265,'HN','D02.3','2015-09-23 18:45:13'),(266,'HN','D09.3','2015-09-23 18:45:13'),(267,'HN','D11.0','2015-09-23 18:45:13'),(268,'HN','D11.9','2015-09-23 18:45:13'),(269,'HN','D14.1','2015-09-23 18:45:13'),(270,'HN','D37.0','2015-09-23 18:45:13'),(271,'HN','D38.0','2015-09-23 18:45:13'),(272,'HN','D38.5','2015-09-23 18:45:13'),(273,'HN','D44.0','2015-09-23 18:45:13'),(274,'LGI','C17.0','2015-09-23 19:05:30'),(275,'LGI','C17.1','2015-09-23 19:05:30'),(276,'LGI','C17.2','2015-09-23 19:05:30'),(277,'LGI','C17.9','2015-09-23 19:05:30'),(278,'LGI','C18.0','2015-09-23 19:38:22'),(279,'LGI','C18.0a','2015-09-23 19:38:22'),(280,'LGI','C18.1','2015-09-23 19:38:22'),(281,'LGI','C18.2','2015-09-23 19:38:22'),(282,'LGI','C18.2r','2015-09-23 19:38:22'),(283,'LGI','C18.3','2015-09-23 19:38:22'),(284,'LGI','C18.4','2015-09-23 19:38:22'),(285,'LGI','C18.5','2015-09-23 19:38:22'),(286,'LGI','C18.6           ','2015-09-23 19:38:22'),(287,'LGI','C18.7','2015-09-23 19:38:22'),(288,'LGI','C18.7r','2015-09-23 19:38:22'),(289,'LGI','C18.9','2015-09-23 19:38:22'),(290,'LGI','C18.1','2015-09-23 19:38:22'),(291,'LGI','C18.2','2015-09-23 19:38:22'),(292,'LGI','C18.2r','2015-09-23 19:38:22'),(293,'LGI','C18.3','2015-09-23 19:38:22'),(294,'LGI','C18.4','2015-09-23 19:38:22'),(295,'LGI','C18.5','2015-09-23 19:38:22'),(296,'LGI','C18.6','2015-09-23 19:38:22'),(297,'LGI','C18.7','2015-09-23 19:38:22'),(298,'LGI','C18.7r','2015-09-23 19:38:22'),(299,'LGI','C18.9','2015-09-23 19:38:22'),(300,'LGI','C19','2015-09-23 19:38:22'),(301,'LGI','C19.9','2015-09-23 19:38:22'),(302,'LGI','C19.9r','2015-09-23 19:38:22'),(303,'LGI','C19.9','2015-09-23 19:38:22'),(304,'LGI','C19.9r','2015-09-23 19:38:22'),(305,'LGI','C20','2015-09-23 19:38:22'),(306,'LGI','C20.9','2015-09-23 19:38:22'),(307,'LGI','C20.9a','2015-09-23 19:38:22'),(308,'LGI','C20.9p','2015-09-23 19:38:22'),(309,'LGI','C20.9r','2015-09-23 19:38:22'),(310,'LGI','C20.9','2015-09-23 19:38:22'),(311,'LGI','C20.9a','2015-09-23 19:38:22'),(312,'LGI','C20.9p','2015-09-23 19:38:22'),(313,'LGI','C20.9r','2015-09-23 19:38:22'),(314,'LGI','C21.0','2015-09-23 19:38:22'),(315,'LGI','C21.1           ','2015-09-23 19:38:22'),(316,'LGI','C21.1a','2015-09-23 19:38:22'),(317,'LGI','C21.8','2015-09-23 19:38:22'),(318,'LGI','C21.1','2015-09-23 19:38:22'),(319,'LGI','C21.1a','2015-09-23 19:38:22'),(320,'LGI','C21.8','2015-09-23 19:38:22'),(321,'LGI','C26.0','2015-09-23 19:39:39'),(322,'LGI','C26.9','2015-09-23 19:39:39'),(323,'LGI','D01.2','2015-09-23 19:40:20'),(324,'LGI','D01.3','2015-09-23 19:40:28'),(325,'LGI','D37.5','2015-09-23 19:43:26'),(326,'LGI','C76.2','2015-09-23 19:43:48'),(327,'LGI','C76.20','2015-09-23 19:43:48'),(328,'LGI','C76.2p','2015-09-23 19:43:48'),(329,'LGI','C76.2r','2015-09-23 19:43:48'),(330,'LGI','C76.3','2015-09-23 19:43:55'),(331,'LGI','C76.3a','2015-09-23 19:43:55'),(332,'LGI','C76.3b','2015-09-23 19:43:55'),(333,'LGI','C76.3p','2015-09-23 19:43:55'),(334,'LGI','C76.3r','2015-09-23 19:43:55'),(335,'UGI','C15.0','2015-09-23 19:44:08'),(336,'UGI','C15.1','2015-09-23 19:44:08'),(337,'UGI','C15.3','2015-09-23 19:44:08'),(338,'UGI','C15.3r','2015-09-23 19:44:08'),(339,'UGI','C15.4','2015-09-23 19:44:08'),(340,'UGI','C15.4r','2015-09-23 19:44:08'),(341,'UGI','C15.5','2015-09-23 19:44:08'),(342,'UGI','C15.5a','2015-09-23 19:44:08'),(343,'UGI','C15.5p','2015-09-23 19:44:08'),(344,'UGI','C15.5pp','2015-09-23 19:44:08'),(345,'UGI','C15.5q','2015-09-23 19:44:08'),(346,'UGI','C15.5r','2015-09-23 19:44:08'),(347,'UGI','C15.9','2015-09-23 19:44:08'),(348,'UGI','C15.9r','2015-09-23 19:44:08'),(349,'UGI','C16.0','2015-09-23 19:44:15'),(350,'UGI','C16.0a','2015-09-23 19:44:15'),(351,'UGI','C16.0p','2015-09-23 19:44:15'),(352,'UGI','C16.0r','2015-09-23 19:44:15'),(353,'UGI','C16.1','2015-09-23 19:44:15'),(354,'UGI','C16.2','2015-09-23 19:44:15'),(355,'UGI','C16.3           ','2015-09-23 19:44:15'),(356,'UGI','C16.3p','2015-09-23 19:44:15'),(357,'UGI','C16.4','2015-09-23 19:44:15'),(358,'UGI','C16.5           ','2015-09-23 19:44:15'),(359,'UGI','C16.6','2015-09-23 19:44:15'),(360,'UGI','C16.8','2015-09-23 19:44:15'),(361,'UGI','C16.8r','2015-09-23 19:44:15'),(362,'UGI','C16.9','2015-09-23 19:44:15'),(363,'UGI','C16.9r','2015-09-23 19:44:15'),(364,'UGI','D37.1','2015-09-23 19:45:17'),(365,'HN','C75.0','2015-09-23 19:47:42'),(366,'HN','C76.0','2015-09-23 19:48:06'),(367,'HN','H05.1','2015-09-23 19:48:52'),(368,'HN','H05.2','2015-09-23 19:48:52'),(369,'HN','H06.2','2015-09-23 19:48:52'),(370,'HN','H18.6','2015-09-23 19:48:52'),(371,'HN','H93.3','2015-09-23 19:48:52'),(372,'Skin','C43.0','2015-09-23 19:49:33'),(373,'Skin','C43.2','2015-09-23 19:49:33'),(374,'Skin','C43.3','2015-09-23 19:49:33'),(375,'Skin','C43.4','2015-09-23 19:49:33'),(376,'Skin','C43.5','2015-09-23 19:49:33'),(377,'Skin','C43.6','2015-09-23 19:49:33'),(378,'Skin','C43.7','2015-09-23 19:49:33'),(379,'Skin','C43.9','2015-09-23 19:49:33'),(380,'Skin','C43.2','2015-09-23 19:49:33'),(381,'Skin','C43.3','2015-09-23 19:49:33'),(382,'Skin','C43.4','2015-09-23 19:49:33'),(383,'Skin','C43.5','2015-09-23 19:49:33'),(384,'Skin','C43.6','2015-09-23 19:49:33'),(385,'Skin','C43.7','2015-09-23 19:49:33'),(386,'Skin','C43.9','2015-09-23 19:49:33'),(387,'Skin','C44.0','2015-09-23 19:49:33'),(388,'Skin','C44.0a','2015-09-23 19:49:33'),(389,'Skin','C44.0r','2015-09-23 19:49:33'),(390,'Skin','C44.1','2015-09-23 19:49:33'),(391,'Skin','C44.1a','2015-09-23 19:49:33'),(392,'Skin','C44.1b','2015-09-23 19:49:33'),(393,'Skin','C44.1r','2015-09-23 19:49:33'),(394,'Skin','C44.1s','2015-09-23 19:49:33'),(395,'Skin','C44.2','2015-09-23 19:49:33'),(396,'Skin','C44.20','2015-09-23 19:49:33'),(397,'Skin','C44.2p','2015-09-23 19:49:33'),(398,'Skin','C44.2r','2015-09-23 19:49:33'),(399,'Skin','C44.3','2015-09-23 19:49:33'),(400,'Skin','C44.30','2015-09-23 19:49:33'),(401,'Skin','C44.31','2015-09-23 19:49:33'),(402,'Skin','C44.3a','2015-09-23 19:49:33'),(403,'Skin','C44.3p','2015-09-23 19:49:33'),(404,'Skin','C44.3r','2015-09-23 19:49:33'),(405,'Skin','C44.3s','2015-09-23 19:49:33'),(406,'Skin','C44.3t','2015-09-23 19:49:33'),(407,'Skin','C44.3u','2015-09-23 19:49:33'),(408,'Skin','C44.4','2015-09-23 19:49:33'),(409,'Skin','C44.40','2015-09-23 19:49:33'),(410,'Skin','C44.4p','2015-09-23 19:49:33'),(411,'Skin','C44.4r','2015-09-23 19:49:33'),(412,'Skin','C44.4s','2015-09-23 19:49:33'),(413,'Skin','C44.4t','2015-09-23 19:49:33'),(414,'Skin','C44.4u','2015-09-23 19:49:33'),(415,'Skin','C44.5','2015-09-23 19:49:33'),(416,'Skin','C44.50','2015-09-23 19:49:33'),(417,'Skin','C44.51','2015-09-23 19:49:33'),(418,'Skin','C44.52','2015-09-23 19:49:33'),(419,'Skin','C44.53','2015-09-23 19:49:33'),(420,'Skin','C44.5p','2015-09-23 19:49:33'),(421,'Skin','C44.5r','2015-09-23 19:49:33'),(422,'Skin','C44.5s','2015-09-23 19:49:33'),(423,'Skin','C44.6','2015-09-23 19:49:33'),(424,'Skin','C44.60','2015-09-23 19:49:33'),(425,'Skin','C44.6r','2015-09-23 19:49:33'),(426,'Skin','C44.7','2015-09-23 19:49:33'),(427,'Skin','C44.7a','2015-09-23 19:49:33'),(428,'Skin','C44.7b','2015-09-23 19:49:33'),(429,'Skin','C44.7c','2015-09-23 19:49:33'),(430,'Skin','C44.7d','2015-09-23 19:49:33'),(431,'Skin','C44.7e','2015-09-23 19:49:33'),(432,'Skin','C44.7f','2015-09-23 19:49:33'),(433,'Skin','C44.7p','2015-09-23 19:49:33'),(434,'Skin','C44.7q','2015-09-23 19:49:33'),(435,'Skin','C44.7r','2015-09-23 19:49:33'),(436,'Skin','C44.7s','2015-09-23 19:49:33'),(437,'Skin','C44.8','2015-09-23 19:49:33'),(438,'Skin','C44.8p','2015-09-23 19:49:33'),(439,'Skin','C44.8r','2015-09-23 19:49:33'),(440,'Skin','C44.9','2015-09-23 19:49:33'),(441,'Skin','C44.9a','2015-09-23 19:49:33'),(442,'Skin','C44.1','2015-09-23 19:49:33'),(443,'Skin','C44.1a','2015-09-23 19:49:33'),(444,'Skin','C44.1b','2015-09-23 19:49:33'),(445,'Skin','C44.1r','2015-09-23 19:49:33'),(446,'Skin','C44.1s','2015-09-23 19:49:33'),(447,'Skin','C44.2','2015-09-23 19:49:33'),(448,'Skin','C44.20','2015-09-23 19:49:33'),(449,'Skin','C44.2p','2015-09-23 19:49:33'),(450,'Skin','C44.2r','2015-09-23 19:49:33'),(451,'Skin','C44.3','2015-09-23 19:49:33'),(452,'Skin','C44.30','2015-09-23 19:49:33'),(453,'Skin','C44.31','2015-09-23 19:49:33'),(454,'Skin','C44.3a','2015-09-23 19:49:33'),(455,'Skin','C44.3p','2015-09-23 19:49:33'),(456,'Skin','C44.3r','2015-09-23 19:49:33'),(457,'Skin','C44.3s','2015-09-23 19:49:33'),(458,'Skin','C44.3t','2015-09-23 19:49:33'),(459,'Skin','C44.3u','2015-09-23 19:49:33'),(460,'Skin','C44.4','2015-09-23 19:49:33'),(461,'Skin','C44.40','2015-09-23 19:49:33'),(462,'Skin','C44.4p','2015-09-23 19:49:33'),(463,'Skin','C44.4r','2015-09-23 19:49:33'),(464,'Skin','C44.4s','2015-09-23 19:49:33'),(465,'Skin','C44.4t','2015-09-23 19:49:33'),(466,'Skin','C44.4u','2015-09-23 19:49:33'),(467,'Skin','C44.5','2015-09-23 19:49:33'),(468,'Skin','C44.50','2015-09-23 19:49:33'),(469,'Skin','C44.51','2015-09-23 19:49:33'),(470,'Skin','C44.52','2015-09-23 19:49:33'),(471,'Skin','C44.53','2015-09-23 19:49:33'),(472,'Skin','C44.5p','2015-09-23 19:49:33'),(473,'Skin','C44.5r','2015-09-23 19:49:33'),(474,'Skin','C44.5s','2015-09-23 19:49:33'),(475,'Skin','C44.6','2015-09-23 19:49:33'),(476,'Skin','C44.60','2015-09-23 19:49:33'),(477,'Skin','C44.6r','2015-09-23 19:49:33'),(478,'Skin','C44.7','2015-09-23 19:49:33'),(479,'Skin','C44.7a','2015-09-23 19:49:33'),(480,'Skin','C44.7b','2015-09-23 19:49:33'),(481,'Skin','C44.7c','2015-09-23 19:49:33'),(482,'Skin','C44.7d','2015-09-23 19:49:33'),(483,'Skin','C44.7e','2015-09-23 19:49:33'),(484,'Skin','C44.7f','2015-09-23 19:49:33'),(485,'Skin','C44.7p','2015-09-23 19:49:33'),(486,'Skin','C44.7q','2015-09-23 19:49:33'),(487,'Skin','C44.7r','2015-09-23 19:49:33'),(488,'Skin','C44.7s','2015-09-23 19:49:33'),(489,'Skin','C44.8','2015-09-23 19:49:33'),(490,'Skin','C44.8p','2015-09-23 19:49:33'),(491,'Skin','C44.8r','2015-09-23 19:49:33'),(492,'Skin','C44.9','2015-09-23 19:49:33'),(493,'Skin','C44.9a','2015-09-23 19:49:33'),(494,'Skin','D03.1','2015-09-23 19:50:17'),(495,'Skin','D04.3','2015-09-23 19:50:25'),(496,'Skin','L91.0','2015-09-23 19:50:34'),(497,'Resp','C33','2015-09-23 19:50:54'),(498,'Resp','C34.0','2015-09-23 19:50:55'),(499,'Resp','C34.0a','2015-09-23 19:50:55'),(500,'Resp','C34.0b','2015-09-23 19:50:55'),(501,'Resp','C34.1','2015-09-23 19:50:55'),(502,'Resp','C34.10','2015-09-23 19:50:55'),(503,'Resp','C34.1a','2015-09-23 19:50:55'),(504,'Resp','C34.1p','2015-09-23 19:50:55'),(505,'Resp','C34.1q','2015-09-23 19:50:55'),(506,'Resp','C34.1r','2015-09-23 19:50:55'),(507,'Resp','C34.2','2015-09-23 19:50:55'),(508,'Resp','C34.2a','2015-09-23 19:50:55'),(509,'Resp','C34.2p','2015-09-23 19:50:55'),(510,'Resp','C34.3','2015-09-23 19:50:55'),(511,'Resp','C34.3a','2015-09-23 19:50:55'),(512,'Resp','C34.3b','2015-09-23 19:50:55'),(513,'Resp','C34.3p','2015-09-23 19:50:55'),(514,'Resp','C34.3pp','2015-09-23 19:50:55'),(515,'Resp','C34.3q','2015-09-23 19:50:55'),(516,'Resp','C34.3r','2015-09-23 19:50:55'),(517,'Resp','C34.8','2015-09-23 19:50:55'),(518,'Resp','C34.9','2015-09-23 19:50:55'),(519,'Resp','C34.9a','2015-09-23 19:50:55'),(520,'Resp','C34.9p','2015-09-23 19:50:55'),(521,'Resp','C34.9q','2015-09-23 19:50:55'),(522,'Resp','C34.9r','2015-09-23 19:50:55'),(523,'Resp','C34.1','2015-09-23 19:50:55'),(524,'Resp','C34.10','2015-09-23 19:50:55'),(525,'Resp','C34.1a','2015-09-23 19:50:55'),(526,'Resp','C34.1p','2015-09-23 19:50:55'),(527,'Resp','C34.1q','2015-09-23 19:50:55'),(528,'Resp','C34.1r','2015-09-23 19:50:55'),(529,'Resp','C34.2','2015-09-23 19:50:55'),(530,'Resp','C34.2a','2015-09-23 19:50:55'),(531,'Resp','C34.2p','2015-09-23 19:50:55'),(532,'Resp','C34.3','2015-09-23 19:50:55'),(533,'Resp','C34.3a','2015-09-23 19:50:55'),(534,'Resp','C34.3b','2015-09-23 19:50:55'),(535,'Resp','C34.3p','2015-09-23 19:50:55'),(536,'Resp','C34.3pp','2015-09-23 19:50:55'),(537,'Resp','C34.3q','2015-09-23 19:50:55'),(538,'Resp','C34.3r','2015-09-23 19:50:55'),(539,'Resp','C34.8','2015-09-23 19:50:55'),(540,'Resp','C34.9','2015-09-23 19:50:55'),(541,'Resp','C34.9a','2015-09-23 19:50:55'),(542,'Resp','C34.9p','2015-09-23 19:50:55'),(543,'Resp','C34.9q','2015-09-23 19:50:55'),(544,'Resp','C34.9r','2015-09-23 19:50:55'),(545,'Resp','C37','2015-09-23 19:50:55'),(546,'Resp','C37.9','2015-09-23 19:50:55'),(547,'Resp','C37.9p','2015-09-23 19:50:55'),(548,'Resp','C37.9q','2015-09-23 19:50:55'),(549,'Resp','C37.9','2015-09-23 19:50:55'),(550,'Resp','C37.9p','2015-09-23 19:50:55'),(551,'Resp','C37.9q','2015-09-23 19:50:55'),(552,'Resp','C38.1','2015-09-23 19:50:55'),(553,'Resp','C38.2','2015-09-23 19:50:55'),(554,'Resp','C38.3','2015-09-23 19:50:55'),(555,'Resp','C38.3a','2015-09-23 19:50:55'),(556,'Resp','C38.3p','2015-09-23 19:50:55'),(557,'Resp','C38.3r','2015-09-23 19:50:55'),(558,'Resp','C38.4','2015-09-23 19:50:55'),(559,'Resp','C38.4a','2015-09-23 19:50:55'),(560,'Resp','C38.4b','2015-09-23 19:50:55'),(561,'Resp','C38.4c','2015-09-23 19:50:55'),(562,'Resp','C38.4p','2015-09-23 19:50:55'),(563,'Resp','C38.4r','2015-09-23 19:50:55'),(564,'Resp','C38.1','2015-09-23 19:50:55'),(565,'Resp','C38.2','2015-09-23 19:50:55'),(566,'Resp','C38.3','2015-09-23 19:50:55'),(567,'Resp','C38.3a','2015-09-23 19:50:55'),(568,'Resp','C38.3p','2015-09-23 19:50:55'),(569,'Resp','C38.3r','2015-09-23 19:50:55'),(570,'Resp','C38.4','2015-09-23 19:50:55'),(571,'Resp','C38.4a','2015-09-23 19:50:55'),(572,'Resp','C38.4b','2015-09-23 19:50:55'),(573,'Resp','C38.4c','2015-09-23 19:50:55'),(574,'Resp','C38.4p','2015-09-23 19:50:55'),(575,'Resp','C38.4r','2015-09-23 19:50:55'),(576,'Resp','D02.2','2015-09-23 19:51:15'),(577,'Resp','D15.0','2015-09-23 19:51:55'),(578,'Resp','D15.2','2015-09-23 19:51:55'),(579,'Resp','D38.1','2015-09-23 19:52:06'),(580,'Resp','D38.4','2015-09-23 19:52:21'),(581,'Resp','C75.5','2015-09-23 19:52:38'),(582,'Resp','C75.5p','2015-09-23 19:52:38'),(583,'Resp','C76.1','2015-09-23 19:52:46'),(584,'Resp','C76.1a','2015-09-23 19:52:46'),(585,'Resp','C76.1b','2015-09-23 19:52:46'),(586,'Resp','C76.1p','2015-09-23 19:52:46'),(587,'Resp','C76.1r','2015-09-23 19:52:46'),(588,'Prostate','C61','2015-09-23 19:53:01'),(589,'Prostate','C61.9','2015-09-23 19:53:01'),(590,'Prostate','C61.9a','2015-09-23 19:53:01'),(591,'Prostate','C61.9b','2015-09-23 19:53:01'),(592,'Prostate','C61.9p','2015-09-23 19:53:01'),(593,'Prostate','C61.9r','2015-09-23 19:53:01'),(594,'Prostate','D07.5','2015-09-23 19:53:13'),(595,'Prostate','D40.0','2015-09-23 19:53:28'),(596,'CNS','C70.0','2015-09-23 19:53:53'),(597,'CNS','C70.0a','2015-09-23 19:53:53'),(598,'CNS','C70.0r','2015-09-23 19:53:53'),(599,'CNS','C70.0s','2015-09-23 19:53:53'),(600,'CNS','C70.1','2015-09-23 19:53:53'),(601,'CNS','C70.9','2015-09-23 19:53:53'),(602,'CNS','C70.9a','2015-09-23 19:53:53'),(603,'CNS','C70.9r','2015-09-23 19:53:53'),(604,'CNS','C70.1','2015-09-23 19:53:53'),(605,'CNS','C70.9','2015-09-23 19:53:53'),(606,'CNS','C70.9a','2015-09-23 19:53:53'),(607,'CNS','C70.9r','2015-09-23 19:53:53'),(608,'CNS','C71.0','2015-09-23 19:53:53'),(609,'CNS','C71.0a','2015-09-23 19:53:53'),(610,'CNS','C71.0p','2015-09-23 19:53:53'),(611,'CNS','C71.0r','2015-09-23 19:53:53'),(612,'CNS','C71.1','2015-09-23 19:53:53'),(613,'CNS','C71.1a','2015-09-23 19:53:53'),(614,'CNS','C71.1p','2015-09-23 19:53:53'),(615,'CNS','C71.1r','2015-09-23 19:53:53'),(616,'CNS','C71.1s','2015-09-23 19:53:53'),(617,'CNS','C71.2','2015-09-23 19:53:53'),(618,'CNS','C71.2a','2015-09-23 19:53:53'),(619,'CNS','C71.2b','2015-09-23 19:53:53'),(620,'CNS','C71.2p','2015-09-23 19:53:53'),(621,'CNS','C71.2r','2015-09-23 19:53:53'),(622,'CNS','C71.3','2015-09-23 19:53:53'),(623,'CNS','C71.3a','2015-09-23 19:53:53'),(624,'CNS','C71.3p','2015-09-23 19:53:53'),(625,'CNS','C71.3r','2015-09-23 19:53:53'),(626,'CNS','C71.4','2015-09-23 19:53:53'),(627,'CNS','C71.5','2015-09-23 19:53:53'),(628,'CNS','C71.5a','2015-09-23 19:53:53'),(629,'CNS','C71.6','2015-09-23 19:53:53'),(630,'CNS','C71.6a','2015-09-23 19:53:53'),(631,'CNS','C71.6p','2015-09-23 19:53:53'),(632,'CNS','C71.6r','2015-09-23 19:53:53'),(633,'CNS','C71.7','2015-09-23 19:53:53'),(634,'CNS','C71.7a','2015-09-23 19:53:53'),(635,'CNS','C71.7p','2015-09-23 19:53:53'),(636,'CNS','C71.7r','2015-09-23 19:53:53'),(637,'CNS','C71.8','2015-09-23 19:53:53'),(638,'CNS','C71.80','2015-09-23 19:53:53'),(639,'CNS','C71.8a','2015-09-23 19:53:53'),(640,'CNS','C71.8b','2015-09-23 19:53:53'),(641,'CNS','C71.8p','2015-09-23 19:53:53'),(642,'CNS','C71.8r','2015-09-23 19:53:53'),(643,'CNS','C71.9','2015-09-23 19:53:53'),(644,'CNS','C71.90','2015-09-23 19:53:53'),(645,'CNS','C71.9a','2015-09-23 19:53:53'),(646,'CNS','C71.9b','2015-09-23 19:53:53'),(647,'CNS','C71.9p','2015-09-23 19:53:53'),(648,'CNS','C71.9q','2015-09-23 19:53:53'),(649,'CNS','C71.9r','2015-09-23 19:53:53'),(650,'CNS','C71.1','2015-09-23 19:53:53'),(651,'CNS','C71.1a','2015-09-23 19:53:53'),(652,'CNS','C71.1p','2015-09-23 19:53:53'),(653,'CNS','C71.1r','2015-09-23 19:53:53'),(654,'CNS','C71.1s','2015-09-23 19:53:53'),(655,'CNS','C71.2','2015-09-23 19:53:53'),(656,'CNS','C71.2a','2015-09-23 19:53:53'),(657,'CNS','C71.2b','2015-09-23 19:53:53'),(658,'CNS','C71.2p','2015-09-23 19:53:53'),(659,'CNS','C71.2r','2015-09-23 19:53:53'),(660,'CNS','C71.3','2015-09-23 19:53:53'),(661,'CNS','C71.3a','2015-09-23 19:53:53'),(662,'CNS','C71.3p','2015-09-23 19:53:53'),(663,'CNS','C71.3r','2015-09-23 19:53:53'),(664,'CNS','C71.4','2015-09-23 19:53:53'),(665,'CNS','C71.5','2015-09-23 19:53:53'),(666,'CNS','C71.5a','2015-09-23 19:53:53'),(667,'CNS','C71.6','2015-09-23 19:53:53'),(668,'CNS','C71.6a','2015-09-23 19:53:53'),(669,'CNS','C71.6p','2015-09-23 19:53:53'),(670,'CNS','C71.6r','2015-09-23 19:53:53'),(671,'CNS','C71.7','2015-09-23 19:53:53'),(672,'CNS','C71.7a','2015-09-23 19:53:53'),(673,'CNS','C71.7p','2015-09-23 19:53:53'),(674,'CNS','C71.7r','2015-09-23 19:53:53'),(675,'CNS','C71.8','2015-09-23 19:53:53'),(676,'CNS','C71.80','2015-09-23 19:53:53'),(677,'CNS','C71.8a','2015-09-23 19:53:53'),(678,'CNS','C71.8b','2015-09-23 19:53:53'),(679,'CNS','C71.8p','2015-09-23 19:53:53'),(680,'CNS','C71.8r','2015-09-23 19:53:53'),(681,'CNS','C71.9','2015-09-23 19:53:53'),(682,'CNS','C71.90','2015-09-23 19:53:53'),(683,'CNS','C71.9a','2015-09-23 19:53:53'),(684,'CNS','C71.9b','2015-09-23 19:53:53'),(685,'CNS','C71.9p','2015-09-23 19:53:53'),(686,'CNS','C71.9q','2015-09-23 19:53:53'),(687,'CNS','C71.9r','2015-09-23 19:53:53'),(688,'CNS','C72.0','2015-09-23 19:53:53'),(689,'CNS','C72.0a','2015-09-23 19:53:53'),(690,'CNS','C72.0b','2015-09-23 19:53:53'),(691,'CNS','C72.0p','2015-09-23 19:53:53'),(692,'CNS','C72.0r','2015-09-23 19:53:53'),(693,'CNS','C72.1','2015-09-23 19:53:53'),(694,'CNS','C72.2','2015-09-23 19:53:53'),(695,'CNS','C72.2r','2015-09-23 19:53:53'),(696,'CNS','C72.3','2015-09-23 19:53:53'),(697,'CNS','C72.3a','2015-09-23 19:53:53'),(698,'CNS','C72.4','2015-09-23 19:53:53'),(699,'CNS','C72.4a','2015-09-23 19:53:53'),(700,'CNS','C72.4p','2015-09-23 19:53:53'),(701,'CNS','C72.5','2015-09-23 19:53:53'),(702,'CNS','C72.8','2015-09-23 19:53:53'),(703,'CNS','C72.8r','2015-09-23 19:53:53'),(704,'CNS','C72.9','2015-09-23 19:53:53'),(705,'CNS','C72.9a','2015-09-23 19:53:53'),(706,'CNS','C72.9p','2015-09-23 19:53:53'),(707,'CNS','C72.9r','2015-09-23 19:53:53'),(708,'CNS','C72.9s','2015-09-23 19:53:53'),(709,'CNS','C72.1','2015-09-23 19:53:53'),(710,'CNS','C72.2','2015-09-23 19:53:53'),(711,'CNS','C72.2r','2015-09-23 19:53:53'),(712,'CNS','C72.3','2015-09-23 19:53:53'),(713,'CNS','C72.3a','2015-09-23 19:53:53'),(714,'CNS','C72.4','2015-09-23 19:53:53'),(715,'CNS','C72.4a','2015-09-23 19:53:53'),(716,'CNS','C72.4p','2015-09-23 19:53:53'),(717,'CNS','C72.5','2015-09-23 19:53:53'),(718,'CNS','C72.8','2015-09-23 19:53:54'),(719,'CNS','C72.8r','2015-09-23 19:53:54'),(720,'CNS','C72.9','2015-09-23 19:53:54'),(721,'CNS','C72.9a','2015-09-23 19:53:54'),(722,'CNS','C72.9p','2015-09-23 19:53:54'),(723,'CNS','C72.9r','2015-09-23 19:53:54'),(724,'CNS','C72.9s','2015-09-23 19:53:54'),(725,'CNS','C73','2015-09-23 19:53:54'),(726,'CNS','C73.9','2015-09-23 19:53:54'),(727,'CNS','C73.9a','2015-09-23 19:53:54'),(728,'CNS','C73.9p','2015-09-23 19:53:54'),(729,'CNS','C73.9q','2015-09-23 19:53:54'),(730,'CNS','C73.9r','2015-09-23 19:53:54'),(731,'CNS','D32.0','2015-09-23 19:54:08'),(732,'CNS','D32.9','2015-09-23 19:54:08'),(733,'CNS','D32.9','2015-09-23 19:54:08'),(734,'CNS','D33.0','2015-09-23 19:54:09'),(735,'CNS','D33.1','2015-09-23 19:54:09'),(736,'CNS','D33.2           ','2015-09-23 19:54:09'),(737,'CNS','D33.3','2015-09-23 19:54:09'),(738,'CNS','D33.7','2015-09-23 19:54:09'),(739,'CNS','D33.9           ','2015-09-23 19:54:09'),(740,'CNS','D33.1','2015-09-23 19:54:09'),(741,'CNS','D33.2           ','2015-09-23 19:54:09'),(742,'CNS','D33.3','2015-09-23 19:54:09'),(743,'CNS','D33.7','2015-09-23 19:54:09'),(744,'CNS','D33.9','2015-09-23 19:54:09'),(745,'CNS','D42.0','2015-09-23 19:54:20'),(746,'CNS','D42.9','2015-09-23 19:54:20'),(747,'CNS','D42.9','2015-09-23 19:54:20'),(748,'CNS','D43.1','2015-09-23 19:54:20'),(749,'CNS','D43.2           ','2015-09-23 19:54:20'),(750,'CNS','D43.9           ','2015-09-23 19:54:20'),(751,'CNS','D43.1','2015-09-23 19:54:20'),(752,'CNS','D43.2           ','2015-09-23 19:54:20'),(753,'CNS','D43.9','2015-09-23 19:54:20'),(754,'CNS','C75.1','2015-09-23 19:54:32'),(755,'CNS','C75.1a','2015-09-23 19:54:32'),(756,'CNS','C75.1r','2015-09-23 19:54:32'),(757,'CNS','C75.2','2015-09-23 19:54:38'),(758,'CNS','C75.3','2015-09-23 19:54:44'),(759,'CNS','C75.3r','2015-09-23 19:54:44'),(760,'Eye','C69.0','2015-09-23 19:54:56'),(761,'Eye','C69.2','2015-09-23 19:54:56'),(762,'Eye','C69.2a','2015-09-23 19:54:56'),(763,'Eye','C69.2p','2015-09-23 19:54:56'),(764,'Eye','C69.2q','2015-09-23 19:54:56'),(765,'Eye','C69.3','2015-09-23 19:54:56'),(766,'Eye','C69.3r','2015-09-23 19:54:56'),(767,'Eye','C69.4','2015-09-23 19:54:56'),(768,'Eye','C69.5','2015-09-23 19:54:56'),(769,'Eye','C69.5p','2015-09-23 19:54:56'),(770,'Eye','C69.6','2015-09-23 19:54:56'),(771,'Eye','C69.6a','2015-09-23 19:54:56'),(772,'Eye','C69.6p','2015-09-23 19:54:56'),(773,'Eye','C69.6r','2015-09-23 19:54:56'),(774,'Eye','C69.6s','2015-09-23 19:54:56'),(775,'Eye','C69.9','2015-09-23 19:54:56'),(776,'Eye','D31.6','2015-09-23 19:55:10'),(777,'Sarcoma','C40.0','2015-09-23 19:55:27'),(778,'Sarcoma','C40.00','2015-09-23 19:55:27'),(779,'Sarcoma','C40.01','2015-09-23 19:55:27'),(780,'Sarcoma','C40.0a','2015-09-23 19:55:27'),(781,'Sarcoma','C40.0b','2015-09-23 19:55:27'),(782,'Sarcoma','C40.0p','2015-09-23 19:55:27'),(783,'Sarcoma','C40.0r','2015-09-23 19:55:27'),(784,'Sarcoma','C40.1','2015-09-23 19:55:27'),(785,'Sarcoma','C40.2','2015-09-23 19:55:27'),(786,'Sarcoma','C40.20','2015-09-23 19:55:27'),(787,'Sarcoma','C40.21','2015-09-23 19:55:27'),(788,'Sarcoma','C40.2a','2015-09-23 19:55:27'),(789,'Sarcoma','C40.2b','2015-09-23 19:55:27'),(790,'Sarcoma','C40.2c','2015-09-23 19:55:27'),(791,'Sarcoma','C40.2p','2015-09-23 19:55:27'),(792,'Sarcoma','C40.2r','2015-09-23 19:55:27'),(793,'Sarcoma','C40.3','2015-09-23 19:55:27'),(794,'Sarcoma','C40.3p','2015-09-23 19:55:27'),(795,'Sarcoma','C40.8','2015-09-23 19:55:27'),(796,'Sarcoma','C40.9','2015-09-23 19:55:27'),(797,'Sarcoma','C40.1','2015-09-23 19:55:27'),(798,'Sarcoma','C40.2','2015-09-23 19:55:27'),(799,'Sarcoma','C40.20','2015-09-23 19:55:27'),(800,'Sarcoma','C40.21','2015-09-23 19:55:27'),(801,'Sarcoma','C40.2a','2015-09-23 19:55:27'),(802,'Sarcoma','C40.2b','2015-09-23 19:55:27'),(803,'Sarcoma','C40.2c','2015-09-23 19:55:27'),(804,'Sarcoma','C40.2p','2015-09-23 19:55:27'),(805,'Sarcoma','C40.2r','2015-09-23 19:55:27'),(806,'Sarcoma','C40.3','2015-09-23 19:55:27'),(807,'Sarcoma','C40.3p','2015-09-23 19:55:27'),(808,'Sarcoma','C40.8','2015-09-23 19:55:27'),(809,'Sarcoma','C40.9','2015-09-23 19:55:27'),(810,'Sarcoma','C41.0','2015-09-23 19:55:27'),(811,'Sarcoma','C41.02','2015-09-23 19:55:27'),(812,'Sarcoma','C41.0p','2015-09-23 19:55:27'),(813,'Sarcoma','C41.0q','2015-09-23 19:55:27'),(814,'Sarcoma','C41.0r','2015-09-23 19:55:27'),(815,'Sarcoma','C41.1','2015-09-23 19:55:27'),(816,'Sarcoma','C41.1a','2015-09-23 19:55:27'),(817,'Sarcoma','C41.2','2015-09-23 19:55:27'),(818,'Sarcoma','C41.20','2015-09-23 19:55:27'),(819,'Sarcoma','C41.21','2015-09-23 19:55:27'),(820,'Sarcoma','C41.2a','2015-09-23 19:55:27'),(821,'Sarcoma','C41.2b','2015-09-23 19:55:27'),(822,'Sarcoma','C41.2p','2015-09-23 19:55:27'),(823,'Sarcoma','C41.2q','2015-09-23 19:55:27'),(824,'Sarcoma','C41.2r','2015-09-23 19:55:27'),(825,'Sarcoma','C41.2s','2015-09-23 19:55:27'),(826,'Sarcoma','C41.2t','2015-09-23 19:55:27'),(827,'Sarcoma','C41.3','2015-09-23 19:55:27'),(828,'Sarcoma','C41.30','2015-09-23 19:55:27'),(829,'Sarcoma','C41.3a','2015-09-23 19:55:27'),(830,'Sarcoma','C41.3p','2015-09-23 19:55:27'),(831,'Sarcoma','C41.3q','2015-09-23 19:55:27'),(832,'Sarcoma','C41.3r','2015-09-23 19:55:27'),(833,'Sarcoma','C41.4','2015-09-23 19:55:27'),(834,'Sarcoma','C41.40','2015-09-23 19:55:27'),(835,'Sarcoma','C41.41','2015-09-23 19:55:27'),(836,'Sarcoma','C41.42','2015-09-23 19:55:27'),(837,'Sarcoma','C41.4a','2015-09-23 19:55:27'),(838,'Sarcoma','C41.4b','2015-09-23 19:55:27'),(839,'Sarcoma','C41.4p','2015-09-23 19:55:27'),(840,'Sarcoma','C41.4r','2015-09-23 19:55:27'),(841,'Sarcoma','C41.8','2015-09-23 19:55:27'),(842,'Sarcoma','C41.8a','2015-09-23 19:55:27'),(843,'Sarcoma','C41.8b','2015-09-23 19:55:27'),(844,'Sarcoma','C41.9','2015-09-23 19:55:27'),(845,'Sarcoma','C41.1','2015-09-23 19:55:27'),(846,'Sarcoma','C41.1a','2015-09-23 19:55:27'),(847,'Sarcoma','C41.2','2015-09-23 19:55:27'),(848,'Sarcoma','C41.20','2015-09-23 19:55:27'),(849,'Sarcoma','C41.21','2015-09-23 19:55:27'),(850,'Sarcoma','C41.2a','2015-09-23 19:55:27'),(851,'Sarcoma','C41.2b','2015-09-23 19:55:27'),(852,'Sarcoma','C41.2p','2015-09-23 19:55:27'),(853,'Sarcoma','C41.2q','2015-09-23 19:55:27'),(854,'Sarcoma','C41.2r','2015-09-23 19:55:27'),(855,'Sarcoma','C41.2s','2015-09-23 19:55:27'),(856,'Sarcoma','C41.2t','2015-09-23 19:55:27'),(857,'Sarcoma','C41.3','2015-09-23 19:55:27'),(858,'Sarcoma','C41.30','2015-09-23 19:55:27'),(859,'Sarcoma','C41.3a','2015-09-23 19:55:27'),(860,'Sarcoma','C41.3p','2015-09-23 19:55:27'),(861,'Sarcoma','C41.3q','2015-09-23 19:55:27'),(862,'Sarcoma','C41.3r','2015-09-23 19:55:27'),(863,'Sarcoma','C41.4','2015-09-23 19:55:27'),(864,'Sarcoma','C41.40','2015-09-23 19:55:27'),(865,'Sarcoma','C41.41','2015-09-23 19:55:27'),(866,'Sarcoma','C41.42','2015-09-23 19:55:27'),(867,'Sarcoma','C41.4a','2015-09-23 19:55:27'),(868,'Sarcoma','C41.4b','2015-09-23 19:55:27'),(869,'Sarcoma','C41.4p','2015-09-23 19:55:27'),(870,'Sarcoma','C41.4r','2015-09-23 19:55:27'),(871,'Sarcoma','C41.8','2015-09-23 19:55:27'),(872,'Sarcoma','C41.8a','2015-09-23 19:55:27'),(873,'Sarcoma','C41.8b','2015-09-23 19:55:27'),(874,'Sarcoma','C41.9','2015-09-23 19:55:27'),(875,'Sarcoma','C45.0','2015-09-23 19:55:37'),(876,'Sarcoma','C45.1           ','2015-09-23 19:55:37'),(877,'Sarcoma','C45.1           ','2015-09-23 19:55:37'),(878,'Sarcoma','C46.0','2015-09-23 19:55:38'),(879,'Sarcoma','C46.9           ','2015-09-23 19:55:38'),(880,'Sarcoma','C46.9','2015-09-23 19:55:38'),(881,'Sarcoma','C47.0','2015-09-23 19:55:38'),(882,'Sarcoma','C47.2           ','2015-09-23 19:55:38'),(883,'Sarcoma','C47.3','2015-09-23 19:55:38'),(884,'Sarcoma','C47.9','2015-09-23 19:55:38'),(885,'Sarcoma','C47.2           ','2015-09-23 19:55:38'),(886,'Sarcoma','C47.3','2015-09-23 19:55:38'),(887,'Sarcoma','C47.9','2015-09-23 19:55:38'),(888,'Sarcoma','C48.0','2015-09-23 19:55:38'),(889,'Sarcoma','C48.0r','2015-09-23 19:55:38'),(890,'Sarcoma','C48.1','2015-09-23 19:55:38'),(891,'Sarcoma','C48.1a','2015-09-23 19:55:38'),(892,'Sarcoma','C48.1r','2015-09-23 19:55:38'),(893,'Sarcoma','C48.2','2015-09-23 19:55:38'),(894,'Sarcoma','C48.8','2015-09-23 19:55:38'),(895,'Sarcoma','C48.1','2015-09-23 19:55:38'),(896,'Sarcoma','C48.1a','2015-09-23 19:55:38'),(897,'Sarcoma','C48.1r','2015-09-23 19:55:38'),(898,'Sarcoma','C48.2','2015-09-23 19:55:38'),(899,'Sarcoma','C48.8','2015-09-23 19:55:38'),(900,'Sarcoma','C49.0','2015-09-23 19:55:38'),(901,'Sarcoma','C49.0a','2015-09-23 19:55:38'),(902,'Sarcoma','C49.0p','2015-09-23 19:55:38'),(903,'Sarcoma','C49.0r','2015-09-23 19:55:38'),(904,'Sarcoma','C49.1','2015-09-23 19:55:38'),(905,'Sarcoma','C49.1a','2015-09-23 19:55:38'),(906,'Sarcoma','C49.1p','2015-09-23 19:55:38'),(907,'Sarcoma','C49.1r','2015-09-23 19:55:38'),(908,'Sarcoma','C49.1s','2015-09-23 19:55:38'),(909,'Sarcoma','C49.1t','2015-09-23 19:55:38'),(910,'Sarcoma','C49.2           ','2015-09-23 19:55:38'),(911,'Sarcoma','C49.2a','2015-09-23 19:55:38'),(912,'Sarcoma','C49.2p','2015-09-23 19:55:38'),(913,'Sarcoma','C49.2r','2015-09-23 19:55:38'),(914,'Sarcoma','C49.2s','2015-09-23 19:55:38'),(915,'Sarcoma','C49.3','2015-09-23 19:55:38'),(916,'Sarcoma','C49.3a','2015-09-23 19:55:38'),(917,'Sarcoma','C49.3b','2015-09-23 19:55:38'),(918,'Sarcoma','C49.3r','2015-09-23 19:55:38'),(919,'Sarcoma','C49.4','2015-09-23 19:55:38'),(920,'Sarcoma','C49.40','2015-09-23 19:55:38'),(921,'Sarcoma','C49.4a','2015-09-23 19:55:38'),(922,'Sarcoma','C49.4p','2015-09-23 19:55:38'),(923,'Sarcoma','C49.4r','2015-09-23 19:55:38'),(924,'Sarcoma','C49.4s','2015-09-23 19:55:38'),(925,'Sarcoma','C49.4t','2015-09-23 19:55:38'),(926,'Sarcoma','C49.5','2015-09-23 19:55:38'),(927,'Sarcoma','C49.50','2015-09-23 19:55:38'),(928,'Sarcoma','C49.5a','2015-09-23 19:55:38'),(929,'Sarcoma','C49.5p','2015-09-23 19:55:38'),(930,'Sarcoma','C49.5r','2015-09-23 19:55:38'),(931,'Sarcoma','C49.5s','2015-09-23 19:55:38'),(932,'Sarcoma','C49.5t','2015-09-23 19:55:38'),(933,'Sarcoma','C49.6','2015-09-23 19:55:38'),(934,'Sarcoma','C49.6a','2015-09-23 19:55:38'),(935,'Sarcoma','C49.9','2015-09-23 19:55:38'),(936,'Sarcoma','C49.1','2015-09-23 19:55:38'),(937,'Sarcoma','C49.1a','2015-09-23 19:55:38'),(938,'Sarcoma','C49.1p','2015-09-23 19:55:38'),(939,'Sarcoma','C49.1r','2015-09-23 19:55:38'),(940,'Sarcoma','C49.1s','2015-09-23 19:55:38'),(941,'Sarcoma','C49.1t','2015-09-23 19:55:38'),(942,'Sarcoma','C49.2','2015-09-23 19:55:38'),(943,'Sarcoma','C49.2a','2015-09-23 19:55:38'),(944,'Sarcoma','C49.2p','2015-09-23 19:55:38'),(945,'Sarcoma','C49.2r','2015-09-23 19:55:38'),(946,'Sarcoma','C49.2s','2015-09-23 19:55:38'),(947,'Sarcoma','C49.3','2015-09-23 19:55:38'),(948,'Sarcoma','C49.3a','2015-09-23 19:55:38'),(949,'Sarcoma','C49.3b','2015-09-23 19:55:38'),(950,'Sarcoma','C49.3r','2015-09-23 19:55:38'),(951,'Sarcoma','C49.4','2015-09-23 19:55:38'),(952,'Sarcoma','C49.40','2015-09-23 19:55:38'),(953,'Sarcoma','C49.4a','2015-09-23 19:55:38'),(954,'Sarcoma','C49.4p','2015-09-23 19:55:38'),(955,'Sarcoma','C49.4r','2015-09-23 19:55:38'),(956,'Sarcoma','C49.4s','2015-09-23 19:55:38'),(957,'Sarcoma','C49.4t','2015-09-23 19:55:38'),(958,'Sarcoma','C49.5','2015-09-23 19:55:38'),(959,'Sarcoma','C49.50','2015-09-23 19:55:38'),(960,'Sarcoma','C49.5a','2015-09-23 19:55:38'),(961,'Sarcoma','C49.5p','2015-09-23 19:55:38'),(962,'Sarcoma','C49.5r','2015-09-23 19:55:38'),(963,'Sarcoma','C49.5s','2015-09-23 19:55:38'),(964,'Sarcoma','C49.5t','2015-09-23 19:55:38'),(965,'Sarcoma','C49.6','2015-09-23 19:55:38'),(966,'Sarcoma','C49.6a','2015-09-23 19:55:38'),(967,'Sarcoma','C49.9','2015-09-23 19:55:38'),(968,'Sarcoma','D16.0','2015-09-23 19:55:50'),(969,'Sarcoma','D16.4','2015-09-23 19:55:50'),(970,'Sarcoma','D16.9','2015-09-23 19:55:50'),(971,'Sarcoma','D16.4','2015-09-23 19:55:50'),(972,'Sarcoma','D16.9','2015-09-23 19:55:51'),(973,'Sarcoma','D18.0','2015-09-23 19:55:51'),(974,'Sarcoma','D21.0','2015-09-23 19:55:51'),(975,'Sarcoma','D21.1','2015-09-23 19:55:51'),(976,'Sarcoma','D21.2','2015-09-23 19:55:51'),(977,'Sarcoma','D21.5           ','2015-09-23 19:55:51'),(978,'Sarcoma','D21.9','2015-09-23 19:55:51'),(979,'Sarcoma','D21.1','2015-09-23 19:55:51'),(980,'Sarcoma','D21.2','2015-09-23 19:55:51'),(981,'Sarcoma','D21.5           ','2015-09-23 19:55:51'),(982,'Sarcoma','D21.9','2015-09-23 19:55:51'),(983,'Sarcoma','D48.1','2015-09-23 19:56:02'),(984,'Sarcoma','D48.4','2015-09-23 19:56:02'),(985,'Sarcoma','D48.6','2015-09-23 19:56:02'),(986,'Sarcoma','D48.9','2015-09-23 19:56:02'),(987,'Sarcoma','C76.5','2015-09-23 19:56:19'),(988,'Breast','C50.0','2015-09-23 19:56:33'),(989,'Breast','C50.1','2015-09-23 19:56:33'),(990,'Breast','C50.10','2015-09-23 19:56:33'),(991,'Breast','C50.1p','2015-09-23 19:56:33'),(992,'Breast','C50.2','2015-09-23 19:56:33'),(993,'Breast','C50.3','2015-09-23 19:56:33'),(994,'Breast','C50.4','2015-09-23 19:56:33'),(995,'Breast','C50.40','2015-09-23 19:56:33'),(996,'Breast','C50.4p','2015-09-23 19:56:33'),(997,'Breast','C50.4q','2015-09-23 19:56:33'),(998,'Breast','C50.5','2015-09-23 19:56:33'),(999,'Breast','C50.5p','2015-09-23 19:56:33'),(1000,'Breast','C50.5q','2015-09-23 19:56:33'),(1001,'Breast','C50.5r','2015-09-23 19:56:33'),(1002,'Breast','C50.6','2015-09-23 19:56:33'),(1003,'Breast','C50.6r','2015-09-23 19:56:33'),(1004,'Breast','C50.8','2015-09-23 19:56:33'),(1005,'Breast','C50.8a','2015-09-23 19:56:33'),(1006,'Breast','C50.8b','2015-09-23 19:56:33'),(1007,'Breast','C50.8p','2015-09-23 19:56:33'),(1008,'Breast','C50.8r','2015-09-23 19:56:33'),(1009,'Breast','C50.9','2015-09-23 19:56:33'),(1010,'Breast','C50.90','2015-09-23 19:56:33'),(1011,'Breast','C50.9a','2015-09-23 19:56:33'),(1012,'Breast','C50.9b','2015-09-23 19:56:33'),(1013,'Breast','C50.9p','2015-09-23 19:56:33'),(1014,'Breast','C50.9r','2015-09-23 19:56:33'),(1015,'Breast','D05.0','2015-09-23 19:56:42'),(1016,'Breast','D05.1','2015-09-23 19:56:42'),(1017,'Breast','D05.7           ','2015-09-23 19:56:42'),(1018,'Breast','D05.9','2015-09-23 19:56:42'),(1019,'Breast','D24','2015-09-23 19:56:49'),(1020,'GU except prostate','C60.1','2015-09-23 19:57:20'),(1021,'GU except prostate','C60.9','2015-09-23 19:57:20'),(1022,'GU except prostate','C62.0','2015-09-23 19:57:30'),(1023,'GU except prostate','C62.1','2015-09-23 19:57:30'),(1024,'GU except prostate','C62.9','2015-09-23 19:57:30'),(1025,'GU except prostate','C62.9p','2015-09-23 19:57:30'),(1026,'GU except prostate','C62.1','2015-09-23 19:57:30'),(1027,'GU except prostate','C62.9','2015-09-23 19:57:30'),(1028,'GU except prostate','C62.9p','2015-09-23 19:57:30'),(1029,'GU except prostate','C63.1','2015-09-23 19:57:30'),(1030,'GU except prostate','C63.2','2015-09-23 19:57:30'),(1031,'GU except prostate','C63.9','2015-09-23 19:57:30'),(1032,'GU except prostate','C63.1','2015-09-23 19:57:30'),(1033,'GU except prostate','C63.2','2015-09-23 19:57:30'),(1034,'GU except prostate','C63.9','2015-09-23 19:57:30'),(1035,'GU except prostate','C64','2015-09-23 19:57:44'),(1036,'GU except prostate','C64.9           ','2015-09-23 19:57:44'),(1037,'GU except prostate','C64.9a','2015-09-23 19:57:44'),(1038,'GU except prostate','C64.9p','2015-09-23 19:57:44'),(1039,'GU except prostate','C64.9','2015-09-23 19:57:44'),(1040,'GU except prostate','C64.9a','2015-09-23 19:57:44'),(1041,'GU except prostate','C64.9p','2015-09-23 19:57:44'),(1042,'GU except prostate','C65','2015-09-23 19:57:44'),(1043,'GU except prostate','C65.9','2015-09-23 19:57:44'),(1044,'GU except prostate','C65.9','2015-09-23 19:57:44'),(1045,'GU except prostate','C66','2015-09-23 19:57:44'),(1046,'GU except prostate','C66.9','2015-09-23 19:57:44'),(1047,'GU except prostate','C66.9','2015-09-23 19:57:44'),(1048,'GU except prostate','C67.0','2015-09-23 19:57:44'),(1049,'GU except prostate','C67.1','2015-09-23 19:57:44'),(1050,'GU except prostate','C67.2           ','2015-09-23 19:57:44'),(1051,'GU except prostate','C67.2a','2015-09-23 19:57:44'),(1052,'GU except prostate','C67.3','2015-09-23 19:57:44'),(1053,'GU except prostate','C67.4','2015-09-23 19:57:44'),(1054,'GU except prostate','C67.5','2015-09-23 19:57:44'),(1055,'GU except prostate','C67.5p','2015-09-23 19:57:44'),(1056,'GU except prostate','C67.7','2015-09-23 19:57:44'),(1057,'GU except prostate','C67.7a','2015-09-23 19:57:44'),(1058,'GU except prostate','C67.8','2015-09-23 19:57:44'),(1059,'GU except prostate','C67.8p','2015-09-23 19:57:44'),(1060,'GU except prostate','C67.9','2015-09-23 19:57:44'),(1061,'GU except prostate','C67.9a','2015-09-23 19:57:44'),(1062,'GU except prostate','C67.9p','2015-09-23 19:57:44'),(1063,'GU except prostate','C67.9r','2015-09-23 19:57:44'),(1064,'GU except prostate','C67.1','2015-09-23 19:57:44'),(1065,'GU except prostate','C67.2','2015-09-23 19:57:44'),(1066,'GU except prostate','C67.2a','2015-09-23 19:57:44'),(1067,'GU except prostate','C67.3','2015-09-23 19:57:44'),(1068,'GU except prostate','C67.4','2015-09-23 19:57:44'),(1069,'GU except prostate','C67.5','2015-09-23 19:57:44'),(1070,'GU except prostate','C67.5p','2015-09-23 19:57:44'),(1071,'GU except prostate','C67.7','2015-09-23 19:57:44'),(1072,'GU except prostate','C67.7a','2015-09-23 19:57:44'),(1073,'GU except prostate','C67.8','2015-09-23 19:57:44'),(1074,'GU except prostate','C67.8p','2015-09-23 19:57:44'),(1075,'GU except prostate','C67.9','2015-09-23 19:57:44'),(1076,'GU except prostate','C67.9a','2015-09-23 19:57:44'),(1077,'GU except prostate','C67.9p','2015-09-23 19:57:44'),(1078,'GU except prostate','C67.9r','2015-09-23 19:57:44'),(1079,'GU except prostate','C68.0','2015-09-23 19:57:44'),(1080,'GU except prostate','C68.9','2015-09-23 19:57:44'),(1081,'GU except prostate','C68.9','2015-09-23 19:57:44'),(1082,'GU except prostate','C69.0','2015-09-23 19:57:44'),(1083,'GU except prostate','C69.2','2015-09-23 19:57:44'),(1084,'GU except prostate','C69.2a','2015-09-23 19:57:44'),(1085,'GU except prostate','C69.2p','2015-09-23 19:57:44'),(1086,'GU except prostate','C69.2q','2015-09-23 19:57:44'),(1087,'GU except prostate','C69.3','2015-09-23 19:57:44'),(1088,'GU except prostate','C69.3r','2015-09-23 19:57:44'),(1089,'GU except prostate','C69.4','2015-09-23 19:57:44'),(1090,'GU except prostate','C69.5','2015-09-23 19:57:44'),(1091,'GU except prostate','C69.5p','2015-09-23 19:57:44'),(1092,'GU except prostate','C69.6','2015-09-23 19:57:44'),(1093,'GU except prostate','C69.6a','2015-09-23 19:57:44'),(1094,'GU except prostate','C69.6p','2015-09-23 19:57:44'),(1095,'GU except prostate','C69.6r','2015-09-23 19:57:44'),(1096,'GU except prostate','C69.6s','2015-09-23 19:57:44'),(1097,'GU except prostate','C69.9','2015-09-23 19:57:44'),(1098,'GU except prostate','D07.4','2015-09-23 19:57:54'),(1099,'GU except prostate','D09.0','2015-09-23 19:58:07'),(1101,'Other','C76.7','2015-09-23 19:59:59'),(1102,'Other','C97','2015-09-23 20:00:13'),(1103,'Other','D35.2','2015-09-23 20:00:35'),(1104,'Other','D35.3','2015-09-23 20:00:35'),(1105,'Other','D35.5','2015-09-23 20:00:35'),(1106,'Other','D35.6','2015-09-23 20:00:35'),(1107,'Other','D36.1','2015-09-23 20:00:43'),(1108,'Other','D44.4','2015-09-23 20:00:48'),(1109,'Other','D44.7','2015-09-23 20:00:48'),(1110,'METS','C78.0','2015-09-23 20:01:12'),(1111,'METS','C78.1','2015-09-23 20:01:12'),(1112,'METS','C78.2','2015-09-23 20:01:12'),(1113,'METS','C78.5','2015-09-23 20:01:12'),(1114,'METS','C78.6','2015-09-23 20:01:12'),(1115,'METS','C78.7','2015-09-23 20:01:12'),(1116,'METS','C78.8','2015-09-23 20:01:12'),(1117,'METS','C79.0','2015-09-23 20:01:19'),(1118,'METS','C79.1','2015-09-23 20:01:19'),(1119,'METS','C79.2','2015-09-23 20:01:19'),(1120,'METS','C79.3           ','2015-09-23 20:01:19'),(1121,'METS','C79.4','2015-09-23 20:01:19'),(1122,'METS','C79.5           ','2015-09-23 20:01:19'),(1123,'METS','C79.7','2015-09-23 20:01:19'),(1124,'METS','C79.8           ','2015-09-23 20:01:19'),(1125,'METS','C79.82','2015-09-23 20:01:19'),(1126,'METS','C79.88','2015-09-23 20:01:19'),(1127,'Unknown Prim','C80','2015-09-23 20:01:40'),(1128,'Unknown Prim','C80.9','2015-09-23 20:01:40');
/*!40000 ALTER TABLE `DiagnosisTranslation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Doctor`
--

DROP TABLE IF EXISTS `Doctor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Doctor` (
  `DoctorSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `DoctorAriaSer` int(20) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Role` varchar(100) NOT NULL,
  `Workplace` varchar(100) NOT NULL,
  `Email` text,
  `Phone` int(20) DEFAULT NULL,
  `Address` text,
  `ProfileImage` varchar(255) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DoctorSerNum`),
  KEY `DoctorAriaSer` (`DoctorAriaSer`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Doctor`
--

LOCK TABLES `Doctor` WRITE;
/*!40000 ALTER TABLE `Doctor` DISABLE KEYS */;
/*!40000 ALTER TABLE `Doctor` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `doctor_insert_trigger` AFTER INSERT ON `Doctor`
 FOR EACH ROW BEGIN
 INSERT INTO DoctorMH (DoctorSerNum, DoctorRevSerNum, ResourceSerNum, SourceDatabaseSerNum, DoctorAriaSer, FirstName, LastName, Role, Workplace, Email, Phone, Address, ProfileImage, LastUpdated, ModificationAction) VALUES (NEW.DoctorSerNum, NULL, NEW.ResourceSerNum, NEW.SourceDatabaseSerNum, NEW.DoctorAriaSer, NEW.FirstName, NEW.LastName, NEW.Role, NEW.Workplace, NEW.Email, NEW.Phone, NEW.Address,NEW.ProfileImage,NOW(), 'INSERT');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `doctor_update_trigger` AFTER UPDATE ON `Doctor`
 FOR EACH ROW BEGIN
 INSERT INTO DoctorMH (DoctorSerNum, DoctorRevSerNum, ResourceSerNum, SourceDatabaseSerNum, DoctorAriaSer, FirstName, LastName, Role, Workplace, Email, Phone, Address, ProfileImage, LastUpdated, ModificationAction) VALUES (NEW.DoctorSerNum, NULL, NEW.ResourceSerNum, NEW.SourceDatabaseSerNum, NEW.DoctorAriaSer, NEW.FirstName, NEW.LastName, NEW.Role, NEW.Workplace, NEW.Email, NEW.Phone, NEW.Address,NEW.ProfileImage,NOW(), 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `doctor_delete_trigger` AFTER DELETE ON `Doctor`
 FOR EACH ROW BEGIN
 INSERT INTO DoctorMH (DoctorSerNum, DoctorRevSerNum, ResourceSerNum, SourceDatabaseSer, DoctorAriaSer, FirstName, LastName, Role, Workplace, Email, Phone, Address, ProfileImage, LastUpdated, ModificationAction) VALUES (OLD.DoctorSerNum, NULL, OLD.ResourceSerNum, OLD.SourceDatabaseSerNum, OLD.DoctorAriaSer, OLD.FirstName, OLD.LastName, OLD.Role, OLD.Workplace, OLD.Email, OLD.Phone, OLD.Address,OLD.ProfileImage,NOW(), 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `DoctorMH`
--

DROP TABLE IF EXISTS `DoctorMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DoctorMH` (
  `DoctorSerNum` int(11) NOT NULL,
  `DoctorRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `DoctorAriaSer` int(20) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Role` varchar(100) NOT NULL,
  `Workplace` varchar(100) NOT NULL,
  `Email` text,
  `Phone` int(20) DEFAULT NULL,
  `Address` text,
  `ProfileImage` varchar(255) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DoctorSerNum`,`DoctorRevSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DoctorMH`
--

LOCK TABLES `DoctorMH` WRITE;
/*!40000 ALTER TABLE `DoctorMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `DoctorMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Document`
--

DROP TABLE IF EXISTS `Document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Document` (
  `DocumentSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `DocumentId` varchar(100) NOT NULL,
  `AliasExpressionSerNum` int(11) NOT NULL,
  `ApprovedBySerNum` int(11) NOT NULL,
  `ApprovedTimeStamp` datetime NOT NULL,
  `AuthoredBySerNum` int(11) NOT NULL,
  `DateOfService` datetime NOT NULL,
  `Revised` varchar(5) NOT NULL,
  `ValidEntry` varchar(5) NOT NULL,
  `ErrorReasonText` text NOT NULL,
  `OriginalFileName` varchar(500) NOT NULL,
  `FinalFileName` varchar(500) NOT NULL,
  `CreatedBySerNum` int(11) NOT NULL,
  `CreatedTimeStamp` datetime NOT NULL,
  `TransferStatus` varchar(10) NOT NULL,
  `TransferLog` varchar(1000) NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `SessionId` text NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DocumentSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `AliasExpressionSerNum` (`AliasExpressionSerNum`),
  KEY `ApprovedBySerNum` (`ApprovedBySerNum`),
  KEY `AuthoredBySerNum` (`AuthoredBySerNum`),
  KEY `CreatedBySerNum` (`CreatedBySerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  CONSTRAINT `Document_ibfk_4` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Document_ibfk_2` FOREIGN KEY (`AliasExpressionSerNum`) REFERENCES `AliasExpression` (`AliasExpressionSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Document_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Document`
--

LOCK TABLES `Document` WRITE;
/*!40000 ALTER TABLE `Document` DISABLE KEYS */;
/*!40000 ALTER TABLE `Document` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `document_insert_trigger` AFTER INSERT ON `Document`
 FOR EACH ROW BEGIN
INSERT INTO `DocumentMH`(`DocumentSerNum`, `DocumentRevSerNum`,`SessionId`, `PatientSerNum`, `SourceDatabaseSerNum`, `DocumentId`, `AliasExpressionSerNum`, `ApprovedBySerNum`, `ApprovedTimeStamp`, `AuthoredBySerNum`, `DateOfService`, `Revised`, `ValidEntry`, `ErrorReasonText`, `OriginalFileName`, `FinalFileName`, `CreatedBySerNum`, `CreatedTimeStamp`, `TransferStatus`, `TransferLog`, `ReadStatus`, `DateAdded`, `LastUpdated`, `ModificationAction`) VALUES (NEW.DocumentSerNum,NULL,NULL,NEW.PatientSerNum,NEW.SourceDatabaseSerNum,NEW.DocumentId,NEW.AliasExpressionSerNum,NEW.ApprovedBySerNum,NEW.ApprovedTimeStamp, NEW.AuthoredBySerNum, NEW.DateOfService, NEW.Revised, NEW.ValidEntry,NEW.ErrorReasonText,NEW.OriginalFileName,NEW.FinalFileName, NEW.CreatedBySerNum, NEW.CreatedTimeStamp, NEW.TransferStatus,NEW.TransferLog, NEW.ReadStatus, NEW.DateAdded, NOW(), 'INSERT');
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.DocumentSerNum,NOW(),0 FROM NotificationControl ntc, Patient pt WHERE ntc.NotificationType = 'Document' AND pt.PatientSerNum = NEW.PatientSerNum AND pt.AccessLevel = 3;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `document_update_trigger` AFTER UPDATE ON `Document`
 FOR EACH ROW BEGIN
INSERT INTO `DocumentMH`(`DocumentSerNum`, `DocumentRevSerNum`, `SessionId`,`PatientSerNum`, `SourceDatabaseSerNum`, `DocumentId`, `AliasExpressionSerNum`, `ApprovedBySerNum`, `ApprovedTimeStamp`, `AuthoredBySerNum`, `DateOfService`, `Revised`, `ValidEntry`, `ErrorReasonText`, `OriginalFileName`, `FinalFileName`, `CreatedBySerNum`, `CreatedTimeStamp`, `TransferStatus`, `TransferLog`, `ReadStatus`, `DateAdded`, `LastUpdated`, `ModificationAction`)
 VALUES (NEW.DocumentSerNum,NULL,NEW.SessionId,NEW.PatientSerNum,NEW.SourceDatabaseSerNum,NEW.DocumentId,NEW.AliasExpressionSerNum,NEW.ApprovedBySerNum,NEW.ApprovedTimeStamp, NEW.AuthoredBySerNum, NEW.DateOfService, NEW.Revised, NEW.ValidEntry,NEW.ErrorReasonText,NEW.OriginalFileName,NEW.FinalFileName, NEW.CreatedBySerNum, NEW.CreatedTimeStamp, NEW.TransferStatus,NEW.TransferLog, NEW.ReadStatus, NEW.DateAdded, NOW(), 'UPDATE');
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.DocumentSerNum,NOW(),0 FROM NotificationControl ntc, Patient pt WHERE ntc.NotificationType = 'UpdDocument' AND NEW.ReadStatus = 0  AND pt.PatientSerNum = NEW.PatientSerNum AND pt.AccessLevel = 3;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `document_delete_trigger` AFTER DELETE ON `Document`
 FOR EACH ROW BEGIN
INSERT INTO `DocumentMH`(`DocumentSerNum`, `DocumentRevSerNum`, `SessionId`,`PatientSerNum`, `SourceDatabaseSerNum`, `DocumentId`, `AliasExpressionSerNum`, `ApprovedBySerNum`, `ApprovedTimeStamp`, `AuthoredBySerNum`, `DateOfService`, `Revised`, `ValidEntry`, `ErrorReasonText`, `OriginalFileName`, `FinalFileName`, `CreatedBySerNum`, `CreatedTimeStamp`, `TransferStatus`, `TransferLog`, `ReadStatus`, `DateAdded`, `LastUpdated`, `ModificationAction`)
 VALUES (OLD.DocumentSerNum,NULL,OLD.SessionId,OLD.PatientSerNum,OLD.SourceDatabaseSerNum,OLD.DocumentId,OLD.AliasExpressionSerNum,OLD.ApprovedBySerNum,OLD.ApprovedTimeStamp, OLD.AuthoredBySerNum, OLD.DateOfService, OLD.Revised, OLD.ValidEntry,OLD.ErrorReasonText,OLD.OriginalFileName,OLD.FinalFileName, OLD.CreatedBySerNum, OLD.CreatedTimeStamp, OLD.TransferStatus,OLD.TransferLog, OLD.ReadStatus, OLD.DateAdded, NOW(), 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `DocumentMH`
--

DROP TABLE IF EXISTS `DocumentMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DocumentMH` (
  `DocumentSerNum` int(11) NOT NULL,
  `DocumentRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SessionId` text,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `DocumentId` varchar(100) NOT NULL,
  `AliasExpressionSerNum` int(11) NOT NULL,
  `ApprovedBySerNum` int(11) NOT NULL,
  `ApprovedTimeStamp` datetime NOT NULL,
  `AuthoredBySerNum` int(11) NOT NULL,
  `DateOfService` datetime NOT NULL,
  `Revised` varchar(5) NOT NULL,
  `ValidEntry` varchar(5) NOT NULL,
  `ErrorReasonText` text NOT NULL,
  `OriginalFileName` varchar(500) NOT NULL,
  `FinalFileName` varchar(500) NOT NULL,
  `CreatedBySerNum` int(11) NOT NULL,
  `CreatedTimeStamp` datetime NOT NULL,
  `TransferStatus` varchar(10) NOT NULL,
  `TransferLog` varchar(1000) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DocumentSerNum`,`DocumentRevSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DocumentMH`
--

LOCK TABLES `DocumentMH` WRITE;
/*!40000 ALTER TABLE `DocumentMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `DocumentMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EducationalMaterial`
--

DROP TABLE IF EXISTS `EducationalMaterial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EducationalMaterial` (
  `EducationalMaterialSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialSerNum`),
  KEY `EducationalMaterialSerNum` (`EducationalMaterialControlSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  CONSTRAINT `EducationalMaterial_ibfk_4` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `EducationalMaterial_ibfk_3` FOREIGN KEY (`EducationalMaterialControlSerNum`) REFERENCES `EducationalMaterialControl` (`EducationalMaterialControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EducationalMaterial`
--

LOCK TABLES `EducationalMaterial` WRITE;
/*!40000 ALTER TABLE `EducationalMaterial` DISABLE KEYS */;
/*!40000 ALTER TABLE `EducationalMaterial` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `educationalmaterial_insert_trigger` AFTER INSERT ON `EducationalMaterial`
 FOR EACH ROW BEGIN
INSERT INTO `EducationalMaterialMH`(`EducationalMaterialSerNum`, `EducationalMaterialControlSerNum`, `PatientSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.EducationalMaterialSerNum,NEW.EducationalMaterialControlSerNum, NEW.PatientSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.EducationalMaterialSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'EducationalMaterial';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `educationalmaterial_update_trigger` AFTER UPDATE ON `EducationalMaterial`
 FOR EACH ROW BEGIN
INSERT INTO `EducationalMaterialMH`(`EducationalMaterialSerNum`, `EducationalMaterialControlSerNum`, `PatientSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.EducationalMaterialSerNum,NEW.EducationalMaterialControlSerNum, NEW.PatientSerNum, NOW(), NEW.ReadStatus, 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `educationalmaterial_delete_trigger` AFTER DELETE ON `EducationalMaterial`
 FOR EACH ROW BEGIN
INSERT INTO `EducationalMaterialMH`(`EducationalMaterialSerNum`, `EducationalMaterialControlSerNum`, `PatientSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (OLD.EducationalMaterialSerNum,OLD.EducationalMaterialControlSerNum, OLD.PatientSerNum, NOW(), OLD.ReadStatus, 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `EducationalMaterialControl`
--

DROP TABLE IF EXISTS `EducationalMaterialControl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EducationalMaterialControl` (
  `EducationalMaterialControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialType_EN` varchar(100) NOT NULL,
  `EducationalMaterialType_FR` varchar(100) NOT NULL,
  `PublishFlag` int(11) NOT NULL DEFAULT '0',
  `Name_EN` varchar(200) NOT NULL,
  `Name_FR` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `URL_EN` varchar(2000) DEFAULT NULL,
  `URL_FR` varchar(2000) DEFAULT NULL,
  `ShareURL_EN` varchar(2000) DEFAULT NULL,
  `ShareURL_FR` varchar(2000) DEFAULT NULL,
  `PhaseInTreatmentSerNum` int(11) NOT NULL,
  `ParentFlag` int(11) NOT NULL DEFAULT '1',
  `DateAdded` datetime NOT NULL,
  `LastPublished` datetime NOT NULL DEFAULT '2002-01-01 00:00:00',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialControlSerNum`),
  KEY `PhaseInTreatmentSerNum` (`PhaseInTreatmentSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EducationalMaterialControl`
--

LOCK TABLES `EducationalMaterialControl` WRITE;
/*!40000 ALTER TABLE `EducationalMaterialControl` DISABLE KEYS */;
/*!40000 ALTER TABLE `EducationalMaterialControl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EducationalMaterialMH`
--

DROP TABLE IF EXISTS `EducationalMaterialMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EducationalMaterialMH` (
  `EducationalMaterialSerNum` int(11) NOT NULL,
  `EducationalMaterialRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialSerNum`,`EducationalMaterialRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EducationalMaterialMH`
--

LOCK TABLES `EducationalMaterialMH` WRITE;
/*!40000 ALTER TABLE `EducationalMaterialMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `EducationalMaterialMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EducationalMaterialRating`
--

DROP TABLE IF EXISTS `EducationalMaterialRating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EducationalMaterialRating` (
  `EducationalMaterialRatingSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `RatingValue` tinyint(6) NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialRatingSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EducationalMaterialRating`
--

LOCK TABLES `EducationalMaterialRating` WRITE;
/*!40000 ALTER TABLE `EducationalMaterialRating` DISABLE KEYS */;
/*!40000 ALTER TABLE `EducationalMaterialRating` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EducationalMaterialTOC`
--

DROP TABLE IF EXISTS `EducationalMaterialTOC`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EducationalMaterialTOC` (
  `EducationalMaterialTOCSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `OrderNum` int(11) NOT NULL,
  `ParentSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialTOCSerNum`),
  KEY `EducationalMaterialSerNum` (`EducationalMaterialControlSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EducationalMaterialTOC`
--

LOCK TABLES `EducationalMaterialTOC` WRITE;
/*!40000 ALTER TABLE `EducationalMaterialTOC` DISABLE KEYS */;
/*!40000 ALTER TABLE `EducationalMaterialTOC` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Feedback`
--

DROP TABLE IF EXISTS `Feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Feedback` (
  `FeedbackSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `FeedbackContent` varchar(255) DEFAULT NULL,
  `AppRating` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`FeedbackSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Feedback`
--

LOCK TABLES `Feedback` WRITE;
/*!40000 ALTER TABLE `Feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `Feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Filters`
--

DROP TABLE IF EXISTS `Filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Filters` (
  `FilterSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `ControlTable` varchar(100) NOT NULL,
  `ControlTableSerNum` int(11) NOT NULL,
  `FilterType` varchar(100) NOT NULL,
  `FilterId` varchar(150) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`FilterSerNum`),
  KEY `FilterTableSerNum` (`ControlTableSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Filters`
--

LOCK TABLES `Filters` WRITE;
/*!40000 ALTER TABLE `Filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `Filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HospitalMap`
--

DROP TABLE IF EXISTS `HospitalMap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HospitalMap` (
  `HospitalMapSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `MapUrl` varchar(255) DEFAULT NULL,
  `QRMapAlias` varchar(255) DEFAULT NULL,
  `QRImageFileName` varchar(255) NOT NULL,
  `MapName_EN` varchar(255) DEFAULT NULL,
  `MapDescription_EN` varchar(255) DEFAULT NULL,
  `MapName_FR` varchar(255) DEFAULT NULL,
  `MapDescription_FR` varchar(255) DEFAULT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`HospitalMapSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HospitalMap`
--

LOCK TABLES `HospitalMap` WRITE;
/*!40000 ALTER TABLE `HospitalMap` DISABLE KEYS */;
/*!40000 ALTER TABLE `HospitalMap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Messages`
--

DROP TABLE IF EXISTS `Messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Messages` (
  `MessageSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SenderRole` enum('Doctor','Patient','Admin') NOT NULL,
  `ReceiverRole` enum('Doctor','Patient','Admin') NOT NULL,
  `SenderSerNum` int(10) unsigned NOT NULL COMMENT 'Sender''s SerNum',
  `ReceiverSerNum` int(11) unsigned NOT NULL COMMENT 'Recipient''s SerNum',
  `MessageContent` varchar(255) NOT NULL,
  `ReadStatus` smallint(6) NOT NULL COMMENT 'Whether it  has been answered by the medical team',
  `Attachment` varchar(255) NOT NULL,
  `MessageDate` datetime NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`MessageSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Messages`
--

LOCK TABLES `Messages` WRITE;
/*!40000 ALTER TABLE `Messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `Messages` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `insert_message_trigger` AFTER INSERT ON `Messages`
 FOR EACH ROW BEGIN
INSERT INTO `MessagesMH`(`MessageSerNum`, `MessageRevSerNum`, `SessionId`, `SenderRole`, `ReceiverRole`, `SenderSerNum`, `ReceiverSerNum`, `MessageContent`, `ReadStatus`, `Attachment`, `MessageDate`, `LastUpdated`, `ModificationAction`) VALUES (NEW.MessageSerNum, NULL, New.SessionId, NEW.SenderRole, NEW.ReceiverRole, NEW.SenderSerNum, NEW.ReceiverSerNum, NEW.MessageContent, NEW.ReadStatus, NEW.Attachment, NEW.MessageDate, NOW(), 'INSERT');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `update_message_trigger` AFTER UPDATE ON `Messages`
 FOR EACH ROW BEGIN
INSERT INTO `MessagesMH`(`MessageSerNum`, `MessageRevSerNum`, `SessionId`, `SenderRole`, `ReceiverRole`, `SenderSerNum`, `ReceiverSerNum`, `MessageContent`, `ReadStatus`, `Attachment`, `MessageDate`, `LastUpdated`, `ModificationAction`) VALUES (NEW.MessageSerNum, NULL, New.SessionId, NEW.SenderRole, NEW.ReceiverRole, NEW.SenderSerNum, NEW.ReceiverSerNum, NEW.MessageContent, NEW.ReadStatus, NEW.Attachment, NEW.MessageDate, NOW(), 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `delete_message_trigger` AFTER DELETE ON `Messages`
 FOR EACH ROW BEGIN
INSERT INTO `MessagesMH`(`MessageSerNum`, `MessageRevSerNum`, `SessionId`, `SenderRole`, `ReceiverRole`, `SenderSerNum`, `ReceiverSerNum`, `MessageContent`, `ReadStatus`, `Attachment`, `MessageDate`, `LastUpdated`, `ModificationAction`) VALUES (OLD.MessageSerNum, NULL, OLD.SessionId, OLD.SenderRole, OLD.ReceiverRole, OLD.SenderSerNum, OLD.ReceiverSerNum, OLD.MessageContent, OLD.ReadStatus, OLD.Attachment, OLD.MessageDate, NOW(), 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `MessagesMH`
--

DROP TABLE IF EXISTS `MessagesMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MessagesMH` (
  `MessageSerNum` int(11) NOT NULL DEFAULT '0',
  `MessageRevSerNum` int(6) NOT NULL AUTO_INCREMENT,
  `SessionId` text NOT NULL,
  `SenderRole` enum('Doctor','Patient','Admin') NOT NULL,
  `ReceiverRole` enum('Doctor','Patient','Admin') NOT NULL,
  `SenderSerNum` int(10) unsigned NOT NULL COMMENT 'Sender''s SerNum',
  `ReceiverSerNum` int(11) unsigned NOT NULL COMMENT 'Recipient''s SerNum',
  `MessageContent` varchar(255) NOT NULL,
  `ReadStatus` smallint(6) NOT NULL COMMENT 'Whether it  has been answered by the medical team',
  `Attachment` varchar(255) NOT NULL,
  `MessageDate` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`MessageSerNum`,`MessageRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MessagesMH`
--

LOCK TABLES `MessagesMH` WRITE;
/*!40000 ALTER TABLE `MessagesMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `MessagesMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Notification`
--

DROP TABLE IF EXISTS `Notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Notification` (
  `NotificationSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `NotificationControlSerNum` int(11) NOT NULL,
  `RefTableRowSerNum` int(11) NOT NULL,
  `DateAdded` datetime DEFAULT NULL,
  `ReadStatus` tinyint(1) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationSerNum`),
  KEY `NotificationControlSerNum` (`NotificationControlSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `RefTableRowSerNum` (`RefTableRowSerNum`),
  CONSTRAINT `Notification_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Notification_ibfk_2` FOREIGN KEY (`NotificationControlSerNum`) REFERENCES `NotificationControl` (`NotificationControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Notification`
--

LOCK TABLES `Notification` WRITE;
/*!40000 ALTER TABLE `Notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `Notification` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `notification_insert_trigger` AFTER INSERT ON `Notification`
 FOR EACH ROW BEGIN
INSERT INTO `NotificationMH`(`NotificationSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`) VALUES (NEW.NotificationSerNum, NEW.PatientSerNum, NEW.NotificationControlSerNum, NEW.RefTableRowSerNum, NEW.ReadStatus, NOW(), 'INSERT');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `notification_update_trigger` AFTER UPDATE ON `Notification`
 FOR EACH ROW BEGIN
INSERT INTO `NotificationMH`(`NotificationSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`) VALUES (NEW.NotificationSerNum, NEW.PatientSerNum, NEW.NotificationControlSerNum, NEW.RefTableRowSerNum, NEW.ReadStatus, NOW(), 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `notification_delete_trigger` AFTER DELETE ON `Notification`
 FOR EACH ROW BEGIN
INSERT INTO `NotificationMH`(`NotificationSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `ReadStatus`, `DateAdded`, `ModificationAction`) VALUES (OLD.NotificationSerNum, OLD.PatientSerNum, OLD.NotificationControlSerNum, OLD.RefTableRowSerNum, OLD.ReadStatus, NOW(), 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `NotificationControl`
--

DROP TABLE IF EXISTS `NotificationControl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NotificationControl` (
  `NotificationControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Name_EN` varchar(100) NOT NULL,
  `Name_FR` varchar(100) NOT NULL,
  `Description_EN` text NOT NULL,
  `Description_FR` text NOT NULL,
  `NotificationType` varchar(100) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastPublished` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationControlSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NotificationControl`
--

LOCK TABLES `NotificationControl` WRITE;
/*!40000 ALTER TABLE `NotificationControl` DISABLE KEYS */;
INSERT INTO `NotificationControl` VALUES (2,'New Document','Nouveau Document','You have received a new document','Vous avez reçu un nouveau document','Document','2016-03-24 17:30:17','0000-00-00 00:00:00','2016-11-15 16:42:50'),(4,'New Treating Team Message','Nouveau message de l\'équipe soignante','New message from your treatment team','Nouveau message par votre équipe soignante','TxTeamMessage','2016-03-17 00:00:00','0000-00-00 00:00:00','2017-01-18 21:33:01'),(5,'New Announcement','Nouvelle annonce','New general announcement','Nouvelle annonce générale','Announcement','2016-03-30 12:57:50','0000-00-00 00:00:00','2017-01-18 21:32:06'),(6,'Appointment Modification','Mise à jour du rendez-vous','An appointment has been modified','Un rendez-vous a été modifié','AppointmentModified','2016-03-30 14:27:06','0000-00-00 00:00:00','2017-01-18 21:32:49'),(7,'New Educational Material','Nouveau matériel éducatif','You have received a new educational material','Vous avez reçu un nouveau matériel éducatif','EducationalMaterial','2016-05-06 16:24:32','0000-00-00 00:00:00','2017-01-18 21:31:49'),(8,'Next Appointment','Prochain rendez-vous','Next appointment','Prochain rendez-vous','NextAppointment','2016-05-06 17:45:26','0000-00-00 00:00:00','2017-01-18 21:31:39'),(9,'Updated Document','Document mis à jour','A document has been updated','Un document a été modifié','UpdDocument','2016-10-18 15:34:45','0000-00-00 00:00:00','2017-01-18 21:33:11'),(10,'Appointment Room Location','Salle de rendez vous','Please go to $roomNumber','Veuillez aller à $roomNumber','RoomAssignment','2016-11-30 15:41:32','0000-00-00 00:00:00','2017-01-18 21:29:30');
/*!40000 ALTER TABLE `NotificationControl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `NotificationMH`
--

DROP TABLE IF EXISTS `NotificationMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NotificationMH` (
  `NotificationSerNum` int(11) NOT NULL,
  `NotificationRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `NotificationControlSerNum` int(11) NOT NULL,
  `RefTableRowSerNum` int(11) NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationSerNum`,`NotificationRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NotificationMH`
--

LOCK TABLES `NotificationMH` WRITE;
/*!40000 ALTER TABLE `NotificationMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `NotificationMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `NotificationTypes`
--

DROP TABLE IF EXISTS `NotificationTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NotificationTypes` (
  `NotificationTypeSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `NotificationTypeId` varchar(100) NOT NULL,
  `NotificationTypeName` varchar(200) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationTypeSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NotificationTypes`
--

LOCK TABLES `NotificationTypes` WRITE;
/*!40000 ALTER TABLE `NotificationTypes` DISABLE KEYS */;
INSERT INTO `NotificationTypes` VALUES (2,'Document','Document','2016-03-23 12:56:39','2016-03-23 16:56:39'),(3,'TxTeamMessage','Treatment Team Message','2016-03-23 12:56:57','2016-03-23 16:56:57'),(4,'Announcement','Announcement','2016-03-23 12:57:14','2016-03-23 16:57:14'),(5,'EducationalMaterial','Educational Material','2016-03-23 12:58:04','2016-03-23 16:58:04'),(6,'NextAppointment','Next Appointment','2016-03-23 12:58:24','2016-03-23 16:58:24'),(7,'AppointmentModified','Modified Appointment','2016-03-23 12:59:47','2016-03-23 16:59:47'),(8,'NewMessage','New Message','2016-03-24 00:00:00','2016-03-24 21:45:59'),(9,'NewLabResult','New Lab Result','2016-03-24 00:00:00','2016-03-24 21:46:11'),(10,'UpdDocument','Updated Document','2016-10-18 00:00:00','2016-10-18 19:32:49'),(11,'RoomAssignment','Room Assignment','2016-11-30 15:38:00','2016-11-30 20:38:06'),(12,'PatientsForPatients','Patients For Patients Announcement	','2017-01-30 15:08:00','2017-01-30 20:08:00');
/*!40000 ALTER TABLE `NotificationTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `OAUser`
--

DROP TABLE IF EXISTS `OAUser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `OAUser` (
  `OAUserSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(1000) NOT NULL,
  `Password` varchar(1000) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`OAUserSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `OAUser`
--

LOCK TABLES `OAUser` WRITE;
/*!40000 ALTER TABLE `OAUser` DISABLE KEYS */;
/*!40000 ALTER TABLE `OAUser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `OAUserRole`
--

DROP TABLE IF EXISTS `OAUserRole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `OAUserRole` (
  `OAUserSerNum` int(11) NOT NULL,
  `RoleSerNum` int(11) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`OAUserSerNum`,`RoleSerNum`),
  KEY `OAUserSerNum` (`OAUserSerNum`),
  KEY `RoleSerNum` (`RoleSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `OAUserRole`
--

LOCK TABLES `OAUserRole` WRITE;
/*!40000 ALTER TABLE `OAUserRole` DISABLE KEYS */;
/*!40000 ALTER TABLE `OAUserRole` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Patient`
--

DROP TABLE IF EXISTS `Patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Patient` (
  `PatientSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientAriaSer` int(11) NOT NULL,
  `PatientId` varchar(50) NOT NULL,
  `PatientId2` varchar(50) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Alias` varchar(100) DEFAULT NULL,
  `ProfileImage` longtext,
  `Sex` varchar(25) NOT NULL,
  `DateOfBirth` datetime NOT NULL,
  `TelNum` bigint(11) DEFAULT NULL,
  `EnableSMS` tinyint(4) NOT NULL DEFAULT '0',
  `Email` varchar(50) NOT NULL,
  `Language` enum('EN','FR','SN') NOT NULL,
  `SSN` varchar(16) NOT NULL,
  `AccessLevel` enum('1','2','3') NOT NULL DEFAULT '1',
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientSerNum`),
  UNIQUE KEY `SSN` (`SSN`),
  KEY `PatientAriaSer` (`PatientAriaSer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Patient`
--

LOCK TABLES `Patient` WRITE;
/*!40000 ALTER TABLE `Patient` DISABLE KEYS */;
/*!40000 ALTER TABLE `Patient` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `patient_insert_trigger` AFTER INSERT ON `Patient`
 FOR EACH ROW BEGIN
INSERT INTO `PatientMH`(`PatientSerNum`, `PatientRevSerNum`, `SessionId`,`PatientAriaSer`, `PatientId`, `PatientId2`, `FirstName`, `LastName`, `Alias`, `Sex`, `DateOfBirth`, `TelNum`, `EnableSMS`, `Email`, `Language`, `SSN`, `AccessLevel`, `LastUpdated`, `ModificationAction`) VALUES (NEW.PatientSerNum,NULL,NULL,NEW.PatientAriaSer,NEW.PatientId, NEW.PatientId2, NEW.FirstName,NEW.LastName,NEW.Alias, NEW.Sex, NEW.DateOfBirth, NEW.TelNum,NEW.EnableSMS,NEW.Email,NEW.Language,NEW.SSN, NEW.AccessLevel,NOW(), 'INSERT');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `patient_update_trigger` AFTER UPDATE ON `Patient`
 FOR EACH ROW BEGIN
INSERT INTO `PatientMH`(`PatientSerNum`, `PatientRevSerNum`, `SessionId`,`PatientAriaSer`, `PatientId`, `PatientId2`, `FirstName`, `LastName`, `Alias`, `Sex`, `DateOfBirth`, `TelNum`, `EnableSMS`, `Email`, `Language`, `SSN`, `AccessLevel`, `LastUpdated`, `ModificationAction`) VALUES (NEW.PatientSerNum,NULL,NEW.SessionId,NEW.PatientAriaSer,NEW.PatientId, NEW.PatientId2, NEW.FirstName,NEW.LastName,NEW.Alias, NEW.Sex, NEW.DateOfBirth, NEW.TelNum,NEW.EnableSMS,NEW.Email,NEW.Language,NEW.SSN, NEW.AccessLevel,NOW(), 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `patient_delete_trigger` AFTER DELETE ON `Patient`
 FOR EACH ROW BEGIN
INSERT INTO `PatientMH`(`PatientSerNum`, `PatientRevSerNum`, `SessionId`,`PatientAriaSer`, `PatientId`, `PatientId2`, `FirstName`, `LastName`, `Alias`, `Sex`, `DateOfBirth`, `TelNum`, `EnableSMS`, `Email`, `Language`, `SSN`, `AccessLevel`, `LastUpdated`, `ModificationAction`) VALUES (OLD.PatientSerNum,NULL,OLD.SessionId,OLD.PatientAriaSer,OLD.PatientId, OLD.PatientId2, OLD.FirstName,OLD.LastName,OLD.Alias, OLD.Sex, OLD.DateOfBirth, OLD.TelNum,OLD.EnableSMS,OLD.Email,OLD.Language,OLD.SSN, OLD.AccessLevel, NOW(), 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `PatientActivityLog`
--

DROP TABLE IF EXISTS `PatientActivityLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PatientActivityLog` (
  `ActivitySerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Request` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `DeviceId` varchar(255) NOT NULL COMMENT 'This will have information about the previous and current values of fields',
  `SessionId` text NOT NULL,
  `DateTime` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ActivitySerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PatientActivityLog`
--

LOCK TABLES `PatientActivityLog` WRITE;
/*!40000 ALTER TABLE `PatientActivityLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `PatientActivityLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PatientControl`
--

DROP TABLE IF EXISTS `PatientControl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PatientControl` (
  `PatientSerNum` int(11) NOT NULL,
  `PatientUpdate` int(11) NOT NULL DEFAULT '0',
  `LastTransferred` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientSerNum`),
  CONSTRAINT `PatientControl_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PatientControl`
--

LOCK TABLES `PatientControl` WRITE;
/*!40000 ALTER TABLE `PatientControl` DISABLE KEYS */;
/*!40000 ALTER TABLE `PatientControl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PatientDeviceIdentifier`
--

DROP TABLE IF EXISTS `PatientDeviceIdentifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PatientDeviceIdentifier` (
  `PatientDeviceIdentifierSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `DeviceId` varchar(255) NOT NULL,
  `RegistrationId` varchar(256) NOT NULL,
  `DeviceType` tinyint(4) NOT NULL,
  `SessionId` text NOT NULL,
  `SecurityAnswerSerNum` int(11) DEFAULT NULL,
  `Trusted` tinyint(1) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientDeviceIdentifierSerNum`),
  UNIQUE KEY `patient_device` (`PatientSerNum`,`DeviceId`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `SecurityAnswerSerNum` (`SecurityAnswerSerNum`),
  CONSTRAINT `PatientDeviceIdentifier_ibfk_4` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `PatientDeviceIdentifier_ibfk_3` FOREIGN KEY (`SecurityAnswerSerNum`) REFERENCES `SecurityAnswer` (`SecurityAnswerSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PatientDeviceIdentifier`
--

LOCK TABLES `PatientDeviceIdentifier` WRITE;
/*!40000 ALTER TABLE `PatientDeviceIdentifier` DISABLE KEYS */;
/*!40000 ALTER TABLE `PatientDeviceIdentifier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PatientDoctor`
--

DROP TABLE IF EXISTS `PatientDoctor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PatientDoctor` (
  `PatientDoctorSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `DoctorSerNum` int(11) NOT NULL,
  `OncologistFlag` int(11) NOT NULL,
  `PrimaryFlag` int(11) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientDoctorSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `DoctorSerNum` (`DoctorSerNum`),
  CONSTRAINT `PatientDoctor_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `PatientDoctor_ibfk_2` FOREIGN KEY (`DoctorSerNum`) REFERENCES `Doctor` (`DoctorSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PatientDoctor`
--

LOCK TABLES `PatientDoctor` WRITE;
/*!40000 ALTER TABLE `PatientDoctor` DISABLE KEYS */;
/*!40000 ALTER TABLE `PatientDoctor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PatientMH`
--

DROP TABLE IF EXISTS `PatientMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PatientMH` (
  `PatientSerNum` int(11) NOT NULL,
  `PatientRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SessionId` text,
  `PatientAriaSer` int(11) NOT NULL,
  `PatientId` varchar(50) NOT NULL,
  `PatientId2` varchar(50) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Alias` varchar(100) DEFAULT NULL,
  `Sex` varchar(25) NOT NULL,
  `DateOfBirth` datetime NOT NULL,
  `TelNum` bigint(11) DEFAULT NULL,
  `EnableSMS` tinyint(4) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Language` enum('EN','FR','SN') NOT NULL,
  `SSN` text NOT NULL,
  `AccessLevel` enum('1','2','3') NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientSerNum`,`PatientRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PatientMH`
--

LOCK TABLES `PatientMH` WRITE;
/*!40000 ALTER TABLE `PatientMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `PatientMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PatientsForPatients`
--

DROP TABLE IF EXISTS `PatientsForPatients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PatientsForPatients` (
  `PatientsForPatientsSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientsForPatientsSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `PostSerNum` (`PostControlSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PatientsForPatients`
--

LOCK TABLES `PatientsForPatients` WRITE;
/*!40000 ALTER TABLE `PatientsForPatients` DISABLE KEYS */;
/*!40000 ALTER TABLE `PatientsForPatients` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `patients_for_patients_insert_trigger` AFTER INSERT ON `PatientsForPatients`
 FOR EACH ROW BEGIN
INSERT INTO `PatientsForPatientsMH`(`PatientsForPatientsSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.PatientsForPatientsSerNum,NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.PatientsForPatientsSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'PatientsForPatients';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `patients_for_patients_update_trigger` AFTER UPDATE ON `PatientsForPatients`
 FOR EACH ROW BEGIN
INSERT INTO `PatientsForPatientsMH`(`PatientsForPatientsSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (NEW.PatientsForPatientsSerNum,NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `patients_for_patients_delete_trigger` AFTER DELETE ON `PatientsForPatients`
 FOR EACH ROW BEGIN
INSERT INTO `PatientsForPatientsMH`(`PatientsForPatientsSerNum`, `PatientSerNum`, `PostControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (OLD.PatientsForPatientsSerNum,OLD.PatientSerNum, OLD.PostControlSerNum, NOW(), OLD.ReadStatus, 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `PatientsForPatientsMH`
--

DROP TABLE IF EXISTS `PatientsForPatientsMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PatientsForPatientsMH` (
  `PatientsForPatientsSerNum` int(11) NOT NULL,
  `PatientsForPatientsRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientsForPatientsSerNum`,`PatientsForPatientsRevSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `PostControlSerNum` (`PostControlSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PatientsForPatientsMH`
--

LOCK TABLES `PatientsForPatientsMH` WRITE;
/*!40000 ALTER TABLE `PatientsForPatientsMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `PatientsForPatientsMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PhaseInTreatment`
--

DROP TABLE IF EXISTS `PhaseInTreatment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PhaseInTreatment` (
  `PhaseInTreatmentSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Name_EN` varchar(200) NOT NULL,
  `Name_FR` varchar(200) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PhaseInTreatmentSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PhaseInTreatment`
--

LOCK TABLES `PhaseInTreatment` WRITE;
/*!40000 ALTER TABLE `PhaseInTreatment` DISABLE KEYS */;
INSERT INTO `PhaseInTreatment` VALUES (1,'Prior To Treatment','Avant le traitement','2016-04-01 00:28:25'),(2,'During Treatment','Au cours du traitement','2016-04-01 00:28:57'),(3,'After Treatment','Après le traitement','2016-04-01 00:29:18');
/*!40000 ALTER TABLE `PhaseInTreatment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PlanWorkflow`
--

DROP TABLE IF EXISTS `PlanWorkflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PlanWorkflow`
--

LOCK TABLES `PlanWorkflow` WRITE;
/*!40000 ALTER TABLE `PlanWorkflow` DISABLE KEYS */;
/*!40000 ALTER TABLE `PlanWorkflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PostControl`
--

DROP TABLE IF EXISTS `PostControl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PostControl` (
  `PostControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PostType` varchar(100) NOT NULL,
  `PublishFlag` int(11) NOT NULL DEFAULT '0',
  `PostName_FR` varchar(100) NOT NULL,
  `PostName_EN` varchar(100) NOT NULL,
  `Body_FR` text NOT NULL,
  `Body_EN` text NOT NULL,
  `PublishDate` datetime DEFAULT NULL,
  `Disabled` tinyint(1) NOT NULL DEFAULT '0',
  `DateAdded` datetime NOT NULL,
  `LastPublished` datetime NOT NULL DEFAULT '2002-01-01 00:00:00',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PostControlSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PostControl`
--

LOCK TABLES `PostControl` WRITE;
/*!40000 ALTER TABLE `PostControl` DISABLE KEYS */;
/*!40000 ALTER TABLE `PostControl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Priority`
--

DROP TABLE IF EXISTS `Priority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Priority` (
  `PrioritySerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `PriorityAriaSer` int(11) NOT NULL,
  `PriorityDateTime` datetime NOT NULL,
  `PriorityCode` varchar(25) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PrioritySerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Priority`
--

LOCK TABLES `Priority` WRITE;
/*!40000 ALTER TABLE `Priority` DISABLE KEYS */;
/*!40000 ALTER TABLE `Priority` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PushNotification`
--

DROP TABLE IF EXISTS `PushNotification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PushNotification` (
  `PushNotificationSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientDeviceIdentifierSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `NotificationControlSerNum` int(11) NOT NULL,
  `RefTableRowSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `SendStatus` varchar(3) NOT NULL,
  `SendLog` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PushNotificationSerNum`),
  KEY `PatientDeviceIdentifierSerNum` (`PatientDeviceIdentifierSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `NotificationControlSerNum` (`NotificationControlSerNum`),
  KEY `RefTableRowSerNum` (`RefTableRowSerNum`),
  CONSTRAINT `PushNotification_ibfk_1` FOREIGN KEY (`PatientDeviceIdentifierSerNum`) REFERENCES `PatientDeviceIdentifier` (`PatientDeviceIdentifierSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PushNotification`
--

LOCK TABLES `PushNotification` WRITE;
/*!40000 ALTER TABLE `PushNotification` DISABLE KEYS */;
/*!40000 ALTER TABLE `PushNotification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Questionnaire`
--

DROP TABLE IF EXISTS `Questionnaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Questionnaire` (
  `QuestionnaireSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `QuestionnaireControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `PatientQuestionnaireDBSerNum` int(11) DEFAULT NULL,
  `CompletedFlag` tinyint(4) NOT NULL,
  `CompletionDate` datetime DEFAULT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`QuestionnaireSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Questionnaire`
--

LOCK TABLES `Questionnaire` WRITE;
/*!40000 ALTER TABLE `Questionnaire` DISABLE KEYS */;
/*!40000 ALTER TABLE `Questionnaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `QuestionnaireControl`
--

DROP TABLE IF EXISTS `QuestionnaireControl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `QuestionnaireControl` (
  `QuestionnaireControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `QuestionnaireDBSerNum` int(11) NOT NULL,
  `PublishFlag` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastPublished` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`QuestionnaireControlSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `QuestionnaireControl`
--

LOCK TABLES `QuestionnaireControl` WRITE;
/*!40000 ALTER TABLE `QuestionnaireControl` DISABLE KEYS */;
/*!40000 ALTER TABLE `QuestionnaireControl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Resource`
--

DROP TABLE IF EXISTS `Resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Resource` (
  `ResourceSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `ResourceAriaSer` int(11) NOT NULL,
  `ResourceName` varchar(255) NOT NULL,
  `ResourceType` varchar(1000) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ResourceSerNum`),
  KEY `ResourceAriaSer` (`ResourceAriaSer`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Resource`
--

LOCK TABLES `Resource` WRITE;
/*!40000 ALTER TABLE `Resource` DISABLE KEYS */;
/*!40000 ALTER TABLE `Resource` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ResourceAppointment`
--

DROP TABLE IF EXISTS `ResourceAppointment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ResourceAppointment` (
  `ResourceAppointmentSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceSerNum` int(11) NOT NULL,
  `AppointmentSerNum` int(11) NOT NULL,
  `ExclusiveFlag` varchar(11) NOT NULL,
  `PrimaryFlag` varchar(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ResourceAppointmentSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ResourceAppointment`
--

LOCK TABLES `ResourceAppointment` WRITE;
/*!40000 ALTER TABLE `ResourceAppointment` DISABLE KEYS */;
/*!40000 ALTER TABLE `ResourceAppointment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Role`
--

DROP TABLE IF EXISTS `Role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Role` (
  `RoleSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `RoleName` varchar(100) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RoleSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Role`
--

LOCK TABLES `Role` WRITE;
/*!40000 ALTER TABLE `Role` DISABLE KEYS */;
INSERT INTO `Role` VALUES (1,'admin','2017-02-09 16:53:16','2017-02-09 22:07:48'),(2,'editor','2017-02-09 17:16:10','2017-02-09 22:16:10');
/*!40000 ALTER TABLE `Role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SMSSurvey`
--

DROP TABLE IF EXISTS `SMSSurvey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SMSSurvey` (
  `SMSSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SentToNumber` bigint(11) NOT NULL,
  `Provider` text NOT NULL,
  `ReceivedInTime` text NOT NULL,
  `SubmissionTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`SMSSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SMSSurvey`
--

LOCK TABLES `SMSSurvey` WRITE;
/*!40000 ALTER TABLE `SMSSurvey` DISABLE KEYS */;
/*!40000 ALTER TABLE `SMSSurvey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SecurityAnswer`
--

DROP TABLE IF EXISTS `SecurityAnswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SecurityAnswer` (
  `SecurityAnswerSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SecurityQuestionSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `AnswerText` varchar(2056) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SecurityAnswerSerNum`),
  UNIQUE KEY `SecurityQuestionSerNum` (`SecurityQuestionSerNum`,`PatientSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  CONSTRAINT `SecurityAnswer_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `SecurityAnswer_ibfk_2` FOREIGN KEY (`SecurityQuestionSerNum`) REFERENCES `SecurityQuestion` (`SecurityQuestionSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SecurityAnswer`
--

LOCK TABLES `SecurityAnswer` WRITE;
/*!40000 ALTER TABLE `SecurityAnswer` DISABLE KEYS */;
/*!40000 ALTER TABLE `SecurityAnswer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SecurityQuestion`
--

DROP TABLE IF EXISTS `SecurityQuestion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SecurityQuestion` (
  `SecurityQuestionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `QuestionText` varchar(2056) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SecurityQuestionSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SecurityQuestion`
--

LOCK TABLES `SecurityQuestion` WRITE;
/*!40000 ALTER TABLE `SecurityQuestion` DISABLE KEYS */;
INSERT INTO `SecurityQuestion` VALUES (1,'What is the name of your first pet?','2016-10-18 15:03:56','2016-10-18 19:03:56'),(2,'What was the name of your favorite superhero as a child?','2016-10-18 15:03:56','2016-10-18 19:03:56'),(3,'What is your favorite cartoon?','2016-10-18 15:03:56','2016-10-18 19:03:56'),(4,'What is your favorite musical instrument?','2016-10-18 15:03:56','2016-10-18 19:03:56'),(5,'What was the color of your first car?','2016-10-18 15:03:56','2016-10-18 19:03:56'),(6,'What is the first name of your childhood best friend?','2016-10-18 15:03:56','2016-10-18 19:03:56');
/*!40000 ALTER TABLE `SecurityQuestion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SourceDatabase`
--

DROP TABLE IF EXISTS `SourceDatabase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SourceDatabase` (
  `SourceDatabaseSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseName` varchar(255) NOT NULL,
  PRIMARY KEY (`SourceDatabaseSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SourceDatabase`
--

LOCK TABLES `SourceDatabase` WRITE;
/*!40000 ALTER TABLE `SourceDatabase` DISABLE KEYS */;
INSERT INTO `SourceDatabase` VALUES (1,'Aria'),(2,'MediVisit'),(3,'Mosaiq');
/*!40000 ALTER TABLE `SourceDatabase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Staff`
--

DROP TABLE IF EXISTS `Staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Staff` (
  `StaffSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `StaffId` varchar(11) NOT NULL,
  `FirstName` varchar(30) NOT NULL,
  `LastName` varchar(30) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`StaffSerNum`),
  KEY `StaffId` (`StaffId`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Staff`
--

LOCK TABLES `Staff` WRITE;
/*!40000 ALTER TABLE `Staff` DISABLE KEYS */;
/*!40000 ALTER TABLE `Staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Task`
--

DROP TABLE IF EXISTS `Task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Task` (
  `TaskSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `AliasExpressionSerNum` int(11) NOT NULL,
  `PrioritySerNum` int(11) NOT NULL,
  `DiagnosisSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `TaskAriaSer` int(11) NOT NULL,
  `Status` varchar(100) NOT NULL,
  `State` varchar(25) NOT NULL,
  `DueDateTime` datetime NOT NULL,
  `CreationDate` datetime NOT NULL,
  `CompletionDate` datetime NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TaskSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `AliasExpressionSerNum` (`AliasExpressionSerNum`),
  KEY `TaskAriaSer` (`TaskAriaSer`),
  KEY `PrioritySerNum` (`PrioritySerNum`),
  KEY `DiagnosisSerNum` (`DiagnosisSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  CONSTRAINT `Task_ibfk_4` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Task_ibfk_2` FOREIGN KEY (`AliasExpressionSerNum`) REFERENCES `AliasExpression` (`AliasExpressionSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Task_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Task`
--

LOCK TABLES `Task` WRITE;
/*!40000 ALTER TABLE `Task` DISABLE KEYS */;
/*!40000 ALTER TABLE `Task` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `insert_task_trigger` AFTER INSERT ON `Task`
 FOR EACH ROW BEGIN
INSERT INTO `TaskMH`(`TaskSerNum`, `PatientSerNum`, `AliasExpressionSerNum`, `PrioritySerNum`, `DiagnosisSerNum`, `SourceDatabaseSerNum`, `TaskAriaSer`, `Status` , `State`, `DueDateTime`, `DateAdded`, `CreationDate`, `CompletionDate`, `LastUpdated`, `ModificationAction`) VALUES (NEW.TaskSerNum,NEW.PatientSerNum,NEW.AliasExpressionSerNum, NEW.PrioritySerNum, NEW.DiagnosisSerNum, NEW.SourceDatabaseSerNum, NEW.TaskAriaSer, NEW.Status, NEW.State, NEW.DueDateTime, NEW.CreationDate, NEW.CompletionDate, NEW.DateAdded,NULL, 'INSERT');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `update_task_trigger` AFTER UPDATE ON `Task`
 FOR EACH ROW BEGIN
INSERT INTO `TaskMH`(`TaskSerNum`, `PatientSerNum`, `AliasExpressionSerNum`, `SourceDatabaseSerNum`, `TaskAriaSer`, `Status`, `State`, `PrioritySerNum`, `DiagnosisSerNum`, `DueDateTime`, `CreationDate`, `CompletionDate`, `DateAdded`, `LastUpdated`, `ModificationAction`) VALUES (NEW.TaskSerNum, NEW.PatientSerNum,NEW.AliasExpressionSerNum,NEW.SourceDatabaseSerNum,NEW.TaskAriaSer, NEW.Status, NEW.State, NEW.PrioritySerNum, NEW.DiagnosisSerNum, NEW.DueDateTime, NEW.CreationDate, NEW.CompletionDate, NEW.DateAdded,NULL, 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `delete_task_trigger` AFTER DELETE ON `Task`
 FOR EACH ROW BEGIN
INSERT INTO `TaskMH`(`TaskSerNum`, `PatientSerNum`, `AliasExpressionSerNum`, `SourceDatabaseSerNum`, `TaskAriaSer`, `Status`, `State`, `PrioritySerNum`, `DiagnosisSerNum`, `DueDateTime`, `CreationDate`, `CompletionDate`, `DateAdded`, `LastUpdated`, `ModificationAction`) VALUES (OLD.TaskSerNum, OLD.PatientSerNum,OLD.AliasExpressionSerNum,OLD.SourceDatabaseSerNum,OLD.TaskAriaSer, OLD.Status, OLD.State, OLD.PrioritySerNum, OLD.DiagnosisSerNum, OLD.DueDateTime, OLD.CreationDate, OLD.CompletionDate, OLD.DateAdded,NULL, 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `TaskMH`
--

DROP TABLE IF EXISTS `TaskMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TaskMH` (
  `TaskSerNum` int(11) NOT NULL,
  `TaskRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `AliasExpressionSerNum` int(11) NOT NULL,
  `PrioritySerNum` int(11) NOT NULL,
  `DiagnosisSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `TaskAriaSer` int(11) NOT NULL,
  `Status` varchar(100) NOT NULL,
  `State` varchar(25) NOT NULL,
  `DueDateTime` datetime NOT NULL,
  `CreationDate` datetime NOT NULL,
  `CompletionDate` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TaskSerNum`,`TaskRevSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `AliasExpressionSerNum` (`AliasExpressionSerNum`),
  KEY `PrioritySerNum` (`PrioritySerNum`),
  KEY `DiagnosisSerNum` (`DiagnosisSerNum`),
  KEY `TaskAriaSer` (`TaskAriaSer`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TaskMH`
--

LOCK TABLES `TaskMH` WRITE;
/*!40000 ALTER TABLE `TaskMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `TaskMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TestResult`
--

DROP TABLE IF EXISTS `TestResult`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TestResult` (
  `TestResultSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `TestResultGroupSerNum` int(11) NOT NULL,
  `TestResultControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `TestResultAriaSer` varchar(100) NOT NULL,
  `ComponentName` varchar(30) NOT NULL,
  `FacComponentName` varchar(30) NOT NULL,
  `AbnormalFlag` varchar(5) NOT NULL,
  `TestDate` datetime NOT NULL,
  `MaxNorm` float NOT NULL,
  `MinNorm` float NOT NULL,
  `ApprovedFlag` varchar(5) NOT NULL,
  `TestValue` float NOT NULL,
  `TestValueString` varchar(255) NOT NULL,
  `UnitDescription` varchar(40) NOT NULL,
  `ValidEntry` varchar(5) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TestResultSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `TestResultAriaSer` (`TestResultAriaSer`),
  KEY `TestResultControlSerNum` (`TestResultControlSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  CONSTRAINT `TestResult_ibfk_3` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `TestResult_ibfk_1` FOREIGN KEY (`TestResultControlSerNum`) REFERENCES `TestResultControl` (`TestResultControlSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `TestResult_ibfk_2` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TestResult`
--

LOCK TABLES `TestResult` WRITE;
/*!40000 ALTER TABLE `TestResult` DISABLE KEYS */;
/*!40000 ALTER TABLE `TestResult` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TestResultControl`
--

DROP TABLE IF EXISTS `TestResultControl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TestResultControl` (
  `TestResultControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Name_EN` varchar(200) NOT NULL,
  `Name_FR` varchar(200) NOT NULL,
  `Description_EN` text NOT NULL,
  `Description_FR` text NOT NULL,
  `Group_EN` varchar(200) NOT NULL,
  `Group_FR` varchar(200) NOT NULL,
  `PublishFlag` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastPublished` datetime NOT NULL DEFAULT '2002-01-01 00:00:00',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TestResultControlSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TestResultControl`
--

LOCK TABLES `TestResultControl` WRITE;
/*!40000 ALTER TABLE `TestResultControl` DISABLE KEYS */;
/*!40000 ALTER TABLE `TestResultControl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TestResultExpression`
--

DROP TABLE IF EXISTS `TestResultExpression`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TestResultExpression` (
  `TestResultExpressionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `TestResultControlSerNum` int(11) NOT NULL,
  `ExpressionName` varchar(100) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TestResultExpressionSerNum`),
  KEY `TestResultControlSerNum` (`TestResultControlSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TestResultExpression`
--

LOCK TABLES `TestResultExpression` WRITE;
/*!40000 ALTER TABLE `TestResultExpression` DISABLE KEYS */;
/*!40000 ALTER TABLE `TestResultExpression` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TxTeamMessage`
--

DROP TABLE IF EXISTS `TxTeamMessage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TxTeamMessage` (
  `TxTeamMessageSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TxTeamMessageSerNum`),
  KEY `PostSerNum` (`PostControlSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  CONSTRAINT `TxTeamMessage_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `TxTeamMessage_ibfk_2` FOREIGN KEY (`PostControlSerNum`) REFERENCES `PostControl` (`PostControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TxTeamMessage`
--

LOCK TABLES `TxTeamMessage` WRITE;
/*!40000 ALTER TABLE `TxTeamMessage` DISABLE KEYS */;
/*!40000 ALTER TABLE `TxTeamMessage` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `txteammessage_insert_trigger` AFTER INSERT ON `TxTeamMessage`
 FOR EACH ROW BEGIN
INSERT INTO `TxTeamMessageMH`(`TxTeamMessageSerNum`, `PatientSerNum`, `PatientControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)  VALUES (NEW.TxTeamMessageSerNum,NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'INSERT');
INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`,`RefTableRowSerNum`, `DateAdded`, `ReadStatus`) SELECT  NEW.PatientSerNum,ntc.NotificationControlSerNum,NEW.TxTeamMessageSerNum,NOW(),0 FROM NotificationControl ntc WHERE ntc.NotificationType = 'TxTeamMessage';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `txteammessage_update_trigger` AFTER UPDATE ON `TxTeamMessage`
 FOR EACH ROW BEGIN
INSERT INTO `TxTeamMessageMH`(`TxTeamMessageSerNum`, `PatientSerNum`, `PatientControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)  VALUES (NEW.TxTeamMessageSerNum,NEW.PatientSerNum, NEW.PostControlSerNum, NOW(), NEW.ReadStatus, 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `txteammessage_delete_trigger` AFTER DELETE ON `TxTeamMessage`
 FOR EACH ROW BEGIN
INSERT INTO `TxTeamMessageMH`(`TxTeamMessageSerNum`, `PatientSerNum`, `PatientControlSerNum`, `DateAdded`, `ReadStatus`, `ModificationAction`)  VALUES (OLD.TxTeamMessageSerNum,OLD.PatientSerNum, OLD.PostControlSerNum, NOW(), OLD.ReadStatus, 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `TxTeamMessageMH`
--

DROP TABLE IF EXISTS `TxTeamMessageMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TxTeamMessageMH` (
  `TxTeamMessageSerNum` int(11) NOT NULL,
  `TxTeamMessageRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `PatientControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TxTeamMessageSerNum`,`TxTeamMessageRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TxTeamMessageMH`
--

LOCK TABLES `TxTeamMessageMH` WRITE;
/*!40000 ALTER TABLE `TxTeamMessageMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `TxTeamMessageMH` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `UserSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `UserType` varchar(255) NOT NULL,
  `UserTypeSerNum` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `SessionId` text,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `users_insert_trigger` AFTER INSERT ON `Users`
 FOR EACH ROW BEGIN
INSERT INTO `UsersMH` (`UserSerNum`, `UserRevSerNum`,`SessionId`, `UserType`, `UserTypeSerNum`, `Username`, `Password`,`LastUpdated`, `ModificationAction`) 
VALUES (NEW.UserSerNum, NULL,NEW.SessionId,NEW.UserType,  NEW.UserTypeSerNum, NEW.Username,NEW.Password, NOW(), 'INSERT');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `users_update_trigger` AFTER UPDATE ON `Users`
 FOR EACH ROW BEGIN
INSERT INTO `UsersMH` (`UserSerNum`, `UserRevSerNum`,`SessionId`, `UserType`, `UserTypeSerNum`, `Username`, `Password`,`LastUpdated`, `ModificationAction`) 
VALUES (NEW.UserSerNum, NULL,NEW.SessionId,NEW.UserType, NEW.UserTypeSerNum, NEW.Username,NEW.Password, NOW(), 'UPDATE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`ackeem`@`%`*/ /*!50003 TRIGGER `users_delete_trigger` AFTER DELETE ON `Users`
 FOR EACH ROW BEGIN
INSERT INTO `UsersMH` (`UserSerNum`, `UserRevSerNum`,`SessionId`, `UserType`, `UserTypeSerNum`, `Username`, `Password`,`LastUpdated`, `ModificationAction`) 
VALUES (OLD.UserSerNum, NULL,OLD.SessionId,OLD.UserType, OLD.UserTypeSerNum, OLD.Username,OLD.Password, NOW(), 'DELETE');
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `UsersMH`
--

DROP TABLE IF EXISTS `UsersMH`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UsersMH` (
  `UserSerNum` int(11) NOT NULL,
  `UserRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SessionId` text NOT NULL,
  `UserType` varchar(255) NOT NULL,
  `UserTypeSerNum` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserSerNum`,`UserRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UsersMH`
--

LOCK TABLES `UsersMH` WRITE;
/*!40000 ALTER TABLE `UsersMH` DISABLE KEYS */;
/*!40000 ALTER TABLE `UsersMH` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-27 17:02:06
