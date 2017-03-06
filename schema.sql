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
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Patient`
--

LOCK TABLES `Patient` WRITE;
/*!40000 ALTER TABLE `Patient` DISABLE KEYS */;
INSERT INTO `Patient` VALUES (110,49108,'Opal1','','Test1','QA_Opal','Tommy gun','ffd8ffe000104a46494600010101006000600000ffdb00430001010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffdb00430101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffc000110800b400f003012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00fefd80181c0e83b52e07a0fca81d07d07f2a5a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2ae4fc7be3df067c2df04f8b3e247c46f13e8de0bf017813c3dab78b3c61e2df10df43a6e87e1cf0e6856536a3abeb1aadf5c32c56d6561656f34f3c8c7eea6d40cecaa7f127c5df13ff698fdb634d5f17eb7f11fe2cfec55fb2ef88daeae3e18fc25f84ed0781ff6b8f8d3e069942e8bf127e30fc57d4ed6f75bfd9bbc3be32b095b56f0bfc29f879a2e9bf1734bd22e34dd53c65f107c3faec975e0ad27e0fc46f137827c27e1bc4f15f1e67986c8b26c3fbaaad552a988c556d3970d82c2d252ad8ac44dca30853a71d673a706d4aa414bd5c9b24cd33fc6d3cbf29c254c5e26a6bc90b28c23d675272b429c16ee5269249bd93b7d43f117c45f1ebf6d1f8abe36f83bf023e31eb5fb307eccff0007fc5377f0ff00e2d7ed0fe0183c2d7df1bfe367c5fd2362f8d3e09fc029fc5fa3f887c3ff000f7c25f0c219db4df89df19a6d0f5bf144ff0012a19be1ef80f4cd35bc1be37d75fe41fda2ff00649fd9e7f61bb2f09ebdf06ff6baff0082877c35fda87e276b173e1cf811e19f0bfc70f8dffb6c78b3e2c78974d6b7d6757d1ee3f661f8d3e27f881e09f1afc3cd20cba7eb7f13b579a0f87ba7f857c3d14325e7c4ff00035b5ec37371e5707fc13e7f62bb5b6d56da3fd9f7c3ba8cbae6a7a96bbac6bbe30f14fc43f887e33d47c43addd4fa86bde239bc6be3af176bfe22b3d7b5fd56eef758d6f51d12f3476d4757bfbfd4268fcebc9cbfcf171fb162fec91e2e8be39fec73f17fe327c04f16586857be0f86f17c59a8fc6df09699e1dd73c411f8a353f0f6b9f0dfe37dff008cb4cd57c1be20f14db695a9eb969e14d6fe1f6b924fa469b05bf892c42a5cc7fc6d90fed25f0173ce265907f6671e65f85af5a143039f62b26cba581c55493b5de1686715334a317a3a517829e26b6b4e187fac3a542afea353c0de2f860feb10c46515310a3ccf02b155a355bfe48d6961feaae7baf7eb53a5b7ef75d3ef5fd9e3fe0b31afe89e1dd73c2bff000514fd92fe3cfeca1f11be09c7e06d13f68df8b70f83b42f1a7ece3e0bd57e20587f6af807c67e23d5be1df8e7e2278abe17f833c7be1ef275dbcd5bc51a76a9e01f86fad36ade07f11fc50bcd47441a96a3fbb1a6ea3a6eb3a7586b1a3df58eaba4eab656ba9697aa69b736f7da76a5a7df40973657f617b6af2db5e595ddb4b1dc5add5bcb2417104892c4ef1bab1fe6e7f66afdb82fbe20ff00c1543c25f09bf699f0bfc15f01ea7fb437ec35e3af8750f8874fd7e0b9f06fed55e21f859f18740d47c15a2f87fc17e2595f5df0a7886c7c19f133e310f117c29f1a2f89966b79eebfe10df1af8bf494bbdffa63fb06e94ff027c79fb50fec39631f8a1fe1d7ecdfe27f00f8eff67a975cb078b48f0f7ecebfb42691e20d77c17f093c23a9bcd33ea9e18f823e37f057c4df86de15864266d0bc05a3782b419a69e4b03257f75f0e711e49c5b91e5bc49c3998d0cdb24cda87d672fcc30eaa469d7a71a93a3522e9d6852af42bd0af4aae1b1585c452a589c2626956c3626952af4aa538fe4b9865f8dcab1b88cbb31c354c26370b3f675f0f55253a7271538eb16e328ce128ce9d484a54ea53946a5394a128c9fe91607a0fca8c0f41f952d15ed1c62607a0fca8c0f41f952d1400981e83f2a303d07e54b45002607a0fca8c0f41f952d1400981e83f2a303d07e54b450020e83e83f952d20e83e83f952d001451450014514500145145001451450014514500145145007e4afeddbafa7c6af8eff097f64e92e4cdf0b3e1e689a77ed63fb4ee936f7eb12f8bec7c3fe2d5d1ff00667f833addaa24e2fbc33f107e2ae85e23f883e30d2efbecb6fa9787fe0c47a1cc2fac7c4b750ae3ea5a95eeaf7f75a9ea3299ef6f6569ae25393b9989215771242463091a9270a0672724f8c78035b1f11be2afed7ff1e27b3b649fe26fed31e28f865e16d56d75ad0bc436fa9fc22fd942c6dbe087854e9da8e8b636cf6da55f7c4ed3be3478a468da85d6a573637fe2099cdcfef057acd7fcfafed0ff0017330e37f1b731e06c3e2ea7fab5e1c430f94d2c2426d50af9f55c2d3c4e6b8dab14f96a55a12c4fd4694a49ca8c69d78a6bda491fd83e0d70ed1caf85e966d3a4bebd9d4a75e5524bdf8e123370a14e37d6319727b595b49b7196b64154754d36d759d36ff48be8fcdb3d4ed27b2b98f8f9a2b88cc6d8cf01d721d0ff000baab76abd457f0242a4e94e1569ca54ea539c6a539c5da509c24a509c5f4946494a2fa3499fafefa33f0fbc4df05acfc51fb4078f355f889e1fb1bbf08e8bf09fc35f09bc2bac59bf95e2db7d693e256adf1135df1778635b8a7373e16f12f813c4be1af87fad784358b3b6b5bcb7f1169f63a9dbdd4b15a4f6edfb65ff0004c6f8fbe3cf8f7f1ebf6a283e2d43a8b7c5ff0081bf06ff00653f829f11fc4eba12683e15f8af7763e29fda8fc71e0bf8cde12b3b569f4db4b3f895f0dbc65e10f13eada0e9f77243e13f13ea3aff00875516d2c6c6497f3e7e284690fc45f19c71aaa20d72760140033245048c7038cb3bb31f52493c9af5eff82686aba2fc2efdbeb5af0b6931f8acdc7ed65fb3878e3c6fe35bcbabb3aa7876efc69fb337c42f00683e13459af55eef44974ff007c6bd434bd1f45d3ae534afeccb072d6425b78a7aff006a7e85be28e6f4b8fb26e15c6e2ebcb25e3ae17a7470f95d3873e0b0b9fe49913cee866b0f6b898ac0cf159665f9ce1b1f2c352ad2ccb198bcba35a9c2960f0d2c2fe0de33f0ce16b70f2cfe8528471b9663633c4e224daad5b0798578d07425cb4ff7aa8626ae19e1e351c561e846b253729c954fe91e8a28aff570fe580a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a2800a28a2800a28a2800af8eff6c7fda57c43fb3ff85bc09a07c31f0bf877c75f1dfe36f8ca4f87ff0008bc29e2df123f857c236b73a7787b57f17f8d7e21f8ef58b3b0d6359b5f017c35f06683aa78875d8f41d1b54d6b5bd4dbc3fe10d2e18352f13d9dedb7d895f887fb7debfe3df8d1fb557c2ff85dfb3e78cb41f01ebbfb32f847c55af7ed0ff15fc49e17b2f1de9de0dd23f684d06d6c7c05f0b7c0fe0c9eff004b1ae7c69f15c3e09ff84e1750d5b5183c1ff0fbc056516ade29d37c4b3f8d3c33a05dfc178a5c5b3e05f0e78d38be8e3325c062b20e1ccd330c0e2f88b11570b9252cc69616a2cb9e6756852af89faacb1b2a11ab4b0b42b62aba7ec70f4e75aa422fd5c8f01fda99c65b97ba589ad1c5e32851a94f07053c4ce94a6bdaaa119350f69ecd49c6536a117ef4da8a6ce7fe0a7c3dbaf847f067e14fc2ad47c4d278df57f87be08d3bc3fe21f1c4ba4dae83278dfc5b3dcdf6bbe33f191d12c80b5d257c4de2dd675ad5a1b08f73dbdbdcc11dc3bdcacac7d32b8ed07c39af7873c156fe1b8fc6fae78c3c4963a5dddbdbf8ebe20dae8fa8eafa9eaf289a5b7d4fc43a7f8474df06e93716f1ddc881f4cd12cf4451a7c6b656b710ccab795f08f807f6d0f8abf1775cd47e047807e0ee8765fb50fc3fd7f57d0ff6814f126b7af3fc0ef81ba159cd13f843e245cf882db4ab0f1178f6cfe34e857763e29f841f0db443a6789753d34ebb0f8a7c45e1987c2fa8eab2ff00cbf66780e2cf13b8878db8caae3b2ecef152ceb199c714e78abd2ca70346966589c5d5fedfa943318602b60f24c457a7f55c34a786a75696371594e4b3c353cdb36ca7038dfeeaa188cbf87b0195e5df57c561a9470b470d82c32a6f15565529d38a58252a0eaaa98c5152a92517c92a74b115e33742856a94ff0047e94751f515e377da37c56b4f0cf867569fc49a1f887e21f87a1bd3e215d0344bdf0bf847c5705d5cc934b6f61e1cd435ef125de986ded12d21b55bad7f52b9925826945ea497010709e10fda97c11e2bf11f8e7e1edd5bea3e17f88de09f035b78fdb47d56dc8d37c49e18bd6beb01ae783f543b23d760d135db17d1fc536291c77fe1dbdb8d356fe1106ada74f73f3d4384735cc3078ac7e4becb3cc2e5f39c333ab96c6bca58087d76383c3e32be1b134b0f8d597e2fdae1eb51c64b0b4e34e155d3c5c30d5a8d5847d9fac422a93ac9d0957718c29d471e7f69284aa7b26e329439ed192f76728b716937a5fe75f1bde35ff8cbc5578cc18cdafea6032f20a437525bc647b18e14c7b735ecff00b106b9643f6fdfd9b7c20b6f3c9ac4bf01ff006c7f1bb4e96f335bdbe8167ae7ecc1e16713dc81e444f71ac6a102c10c8c2497c891e30446e47ce2d23cacd2ca49925669646249cbc8c5dce4e49f998f524e3a935f7a7fc1312c61f127ed6df18353b7d5b477ff00851ffb317c2ef08eaba2c5a75d1d76df5ffda2be24f8ebe22992fb5594a59c56b6fe14f84de12bbb5d3acd269a68bc471dddece9b2da1aff004ffe89f9354afe39786d86a50954a391d2cfb1d8a9536d7b1c2e078333bcbe8d695a325ec9e638dcb70d2bf2a72c4c23cdcd28a7f9af8bb898e1f80b388b928cb15572dc3524d37cf3798e16bca31fef2a342acef7d141bd763f7ae8a28aff00680fe310a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a2800a28a2800a28a2800afe7f7e05fec4f61fb5f7c23f8a1fb4ceb9fb6afed99f0eec7e38fed39fb4dfc625d3be04ebdf07fe1747a7f873c35f13fc4ff0008fc09a15cdf7fc29bf14f8cbc451787be18fc2cf0969769fdbde28be3b6dbc88ed2dd7746ff00d0157e037c2ffdb1fe21fecd1a97c54fd817e0dfecc2bf14fe397c1bf8e7f1f7c51a8c5aa78f3fe1567c06f867fb3cfc65f89de26f8cdf01bc7bf10fe27ebbe1ef10dfcfac7c49d33e22dcf85bc2bf0ebe1e7847c69aedeea7f0efe23cd72747d1fc2725eddfcbf1950e0cabc3d8eabc7f87e1bafc2b8154f1f98be2da196e2321c3fd5e69d1c4e3219bc2a6022e955947d8ceb46f1ab287b3fde389db97cb3058ba51cae58c8e3aa374a8fd4255a18a9b9ab4a149d06aabe649de317aabdf43c07f670d35bf6abfd84fc23fb6afec0dfb6e7ed15a9f82c4de31d325f0f7edbff09fe167c4cbcd46e7c21e30b9f0beafff000934ff000f7c39f0abc5f6ef6171a7cba8aeada1f8f3c416b368973e5ec8354b5b8b7b38ff00601fd84bf6f0f1cfecb92fed0907ed4bf027e167c46fdabbc43ae7ed12de1ed57f649d53c756da7db78e0a2780b4ff0011789a7f8f7e16d77c45a468fe00b1f0c58785d20d3fc3f369fe195b0b19ed46a2b7d7571f41f88ffe0a03f1afc05f0abc6bf0a7c4ff00f04a8f8b9e18f0cdce917fe11f048fd8ebe247ecebf1bbc2d7577e204bd9b58d5a3f0637893e0af883c19e1b17b79737c2f750f0b9bdbebeba98dce9914f23cd2fda9ff048df8a37ff0013ff00e09f5fb3cc5e21d3b55d03c71f09fc3579f003e21784fc43a23786bc53e0ff00177c0cd56f3e1b4fe1ff0016787fed9a8c7a5788a3d23c3fa3ea3a84369a8ea5a65c7f69457fa46a5a869379657b3fe0b91f859f442f10b119bd3e14e17f04f89aae330f86c467997f084b866baa9430b8b552862334ca787b1318429d3c6d6a6e35b15848afacba49cdd585351faec667fe20655430d0cc31fc478354abcfd84f1ef191973ba4a3154eb62a2dcad4fdac62a336941cd25cb73e07f1cf837fe0a2bfb3bea5e1bd17c65e1efd94bf6bf9f58d22f6f6d3c37f027c6da9fece7fb4a78ae0f0ddb5d6a5e34f10f81fe067c66d7fc5fe04f1c587876c8e916cfa3e89f18347d45af759b69eee5b3b45025fc70f8e9f1efe12eaff001afe0cfc44f0a9f127867c7de1afda374bf833e27f877e2ff0c4fe0af8afe10f09fed2ba26b5e19bed27c6be0af114116b367e1bd47c5361e0fd4acfc43a3cf7de1ad46e34c5b9d2758d4c0d8bfd617c4fff00827c7c00f8a9fb74fecedff050ff0015c9e348fe3bfecc1f0dbe20fc34f0041a7788d6d3c0d77a178facf5ab2bbbef12f87dec679ef354d0ac7c53e3187489f4fd4f4bb798789273af5b6b5fd95a12e99f80ff00f0513f0efc1bf1778bff00679d43c5de1ef0ecfe3187f692f13fc69f0af8f278ac2cfc41e03f823fb3ae8be23f883f123595d6e5b397518bc197de2bd6be166977fa3c37b0595f6a6d6eb2dbbb5b964fce78fbe883e07706e0b88bc42e0ccab19c098acb78733dad9ae5993e371b8dc8b3cc13cbb151c5606794e6589c54b058bc661ea57cbf035327c460a9509e325cd81c5bf674e3f5fc21e24f15e331586c9b30c4c336a58bc6e5f42855c5c29c31384ad2c6d19d1c4c7114e1153861aa4157a91c442a5e95292552925cca969ba9697ac6bf7fe14d3b52b2bbf10692da0aeb3a4db4f1cf7fa3af89fed67406d4ada32d25a1d5a2b0bdb8b08e50b25cdbdb3cc89e51466fd90ff824b7872f66f863fb427c5fd474fd4ec7fe16d7ed43f12f4bf0ab6a3a8e83a8c375f0dfe002693fb387836ef477d094fd9b47d4a6f859af6b76d6baa5c4faaacfab5d4f762033ac29fcbcfecc3e2fbaf869e2cfda67f69af1fcb3586a1e3ff80369fb67eab682c27bab9d0744f0a37c46d03e08f85a0d1daeef1f50d5ec3c0765f0e3439342b695ee751f12dccda55bc6d7b72226fe95bfe086ba87c7f1fb10e9be08fda1fc1ba37c35f14fc21f1743f0b345f87761e1db5f0e6bde1ad1745f871f0eb5ebebcf88d6b67713db7fc2d1f1878b3c4fe24f1a78fecad825b683e23d7ef7c3b14ba97f653eb1a8fca7d0ef80a9e57e23f1fe78aac7194b87384b86b87a8631d26e13cd78a950ce388e9612b45fb1953cbb15c3942851aa97b7af9763b075e5ec63899c2a7bbe32f104b1b92f0fe0791d196331f99e61528f37bf1c3e06ad4c1e5d3ab16b9a13c450c4caa54a6df2d3af0ab0f7bd9c5afd8ea28a2bfd133f9e028a28a0028a28a0028a28a0028a28a0041d07d07f2a5a41d07d07f2a5a0028a28a0028a28a0028a28a0028ae27e237c4af87bf083c15e20f891f157c6fe14f871f0ff00c2962fa9f897c69e36d7b4cf0cf86342b14658fed3a9eb5ac5cda585a23caf1c1089a7569ee258ade059279638db83f807fb4bfc06fda8bc27a8f8dff67ff8a3e15f8a5e19d1f5cbaf0d6b57de1bbb95a7d0f5fb486deea5d235dd2afa0b3d6345be92c6eecf52b48354b0b47bfd2afac755b1171a75edadd4c6bbdb4dafeb7b7df67f7329426e12a8a1270838c673516e1073e6e45292568b9f2cb95369cb9656bd99ee54514504857f3bdfb40dc691fb36ff00c14f7e3aeb7f13f52d2742d13f6def84df00f5ef82daec91c96dfdbfe23fd9bf48f187813e24fc3b9aea79fcbd4bc4be1fb0f187857c6ba4e91a75bb5edc787bc57ab5edbc777168dacbe9dfd10d7f263f1dbc1fe01ff82857c42f8a3f1dbe3ef8534bf1ef823c4be20d6be1f7ecbda06af0cef17c38f807f0c3c55acf87f42f891e0db8b8d3f49d77c29e3cf8efe35d2f5bf8afab7886c24b5d613c249f0b74282fe4b1f0fa4b77f84fd24f83f21f10bc1ee29e06e22c7e639660b895e5585a58ccaa34aa6368e37039be0b38c2558d1af2852c450a55b2d8d4c5e1e75292c461a356846b52a9521523f75e1c56c66178b72ec7e0e8d1af2c0c7135aac31129d3a5ec2786ab869af6908d4953a92f6ea34a6a9d4e4a8e33952a908ca12fd134f1d783246545f13e8c1db188def638e4c9e80c72157527d0807dabc9ac354f8ddfb29fc61f13fed1ff00b31f84e0f8c9e0df8c0da34bfb4d7ecc0de24d17c2d7df1075bd0b47b0f0c785fe3afc0df177892e6cfc2be18f8bda0786f4cd33c33e38f09f89aff4cf087c5ff08e9ba5cd75ad7873c69e16d2b53d5ff3074fff00827d782745d3d742d0be3c7ed55a4f86915963d0e2fda17e26dec48b23bc938fed0d5bc43a86b4e677763233eaa4ae710f9400c695bfec6fe3df023d9ea5f007f6d8fdabfe116b1a6452fd8b4bd6bc783e34fc38beb89645791fc43e02f8b50f89e2d496640f6dbacf56d324b5499ee6d596ed22917fcc2f0dbe8d7c7be09f1861b8f3c28f17725c1e7f82a788c1bc1f1370f66b2c8b3eca313ece788c9f8829e56f1389860b1552861e7386128e2eb61f11428637058bc3e330986af1fe8be25cc72de29cae595e6f91e26ad094e15554c263b0eb1585c447455b053af4e853738465515eb7b38d48c9c2a52709c947f67fe2affc158fc2bad7c3afec7f00feca1fb7aea3f10bc6115ee8cbe05b9fd9c353f06eada53496f7cbf66d5be20f8bb5ad3fe11787a0d63ecc2c22f142f8df5bd334b86fe3d4bc8bc648eddbf0d7e28fc2ef8a7fb4237c4af127c7a5d1fc03e33f8a7a4f817e17cdf09fc3da86a5e26f08fc0ffd9afc2fe2cb2f106b9f047c35ae6b169692f88bc5df11ed1f5dd53e2a7c465d1748b3f1478b75eb74b3d2e3f0f785b4191be81f85ff00b6dfc69f855f137c2bf03ff6def0d783ec1bc6b3d9687f0dbf699f86b06a7a4fc23f88de2ab92123f096bde1cd566d42ebe15f8eb5361249a1e87aaeb7aa68be299a2b8d3bc39ae3eb02cf46bdf4df8dd7a7c43e35f167f626a9f6496e2c23d334bd734f8ad35092c6ece8eb6f6daadac1769358df5ce9b7920b982daf639acee25b68edaea2780c919fa7f153e92bf485ce33da1e1e71e70970c702e5f0a184e23a39b70ad6cc334cb7897fb3333c1cf015e8e638eaf8c8d5cb2863e31c555cbe3428e3a9633090a198d173a13c34793827c3ce13cbfdae6583c4e3f35aea15f0b3a58ff65467819e2682a75e0e8d2a545c311530f5274a15a55674a542acaae166e1555697c2badfecfbe2dfda1ff6f6fd91fc036ba9592fc18f17fc49f829a67c64f01f87e1d1b59d67c5da4fc13f1af8c7f692b5f0bf8c7c397371bf44f82b1e99f0fcb78a2f2eed4c1adeaf3f84741b18eea34d46ddff00a9bfd80b48f13c5e00f8fbe34f15ea767a95ff00c4ff00db5bf6bff1569eb6513431e99e18f0dfc6ff0014fc23f096932a9fbf7765e1af865a58bc9416135cc924b9058a8fe32740f1e7c27f861e2bf89fa67c07f879e25f08fc68fd923c01e3dfda57c25fb6ecb75a4e95f12be24fc4ff00853e46b3f192d6d7c4ba85adaea1f1ff00c1fe3096d357f845f1eacefaeb51f01e9779e2c5d16c3c33158c5a7de5a7f7bbf0960f068f877e18d5bc03e1f83c31e17f195add7c48b4d1e0b416463bff008a1a8de7c45d7efeeed87dcd5359f10f8a355d675863f34baaea1792b7cce6bfb93e8998cc3d1e03ccf865d0c5d1cc723cd70f8cc454c66032fc0d6c76599d65985fec3cca6b0198e6552552be1b2dad87ab4736ad4738c0d4c1cb0588c2d2c250c0caa7e2de2c52ad2e2386612952787c761e50a31a35eb6229d2ad84ad38e368c675b0b844e31af57da46787a73c356557db53ad5673ab23d168a28afea83f2e0a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800ac6f11788744f08f87f5df15f89b54b2d0fc37e18d1b53f10f8835bd4ee23b4d3747d0f45b29f52d5b54d42ee66486dacb4fb0b6b8bbbbb89596386086491d82a92366bf0affe0abdfb65c3e28f843fb447ec65fb397c1ff8f5fb477c5ab8f0e683e19f8c7a9fc12f0d5b5c780be11e83e20d5f40d57c55e06f1a78fb56f16f82b4cbff00899e2bf8587c432e95f09bc23a9eb3e26bdb0beb587c53178734fd774b9353e2cc733cb327c1d6cc738ccb019465d874a589cc334c661f0181c346525153c462f1552950a30e66973549c55ddb73a30b86ab8cc451c35184a752b548538a842751ae6924e4e34e329b8c53e69593b45367f303ff00052cff008286fc4efdac3e2041f1dbe214bf18fe1ffc26f84ff15ac6f7e03fc24b4b6d3f40f09681f07f569ad2ebc21f157c6de11d56dbc43ff0b03e35fc45d2a2b1d4753b8bd8ede7f8576de2af0bf813e1adcf873c663c6de2197cc7fe0977fb68b7eccfff000522b8f88efaf7c4ff000efc33f1deb7e08bbf8dbe257d1bc55af681abfc25f18f81f5cf1a7886ebe226856575770358fc33bef1159f8ce3f101d06fbc5fe02b0d1f53be8f5f9bc2173e32d1cfcb3a96b1ad78ff00c55e39867d435ad6b5af8c1f10749f830de06f8c3e0cd52db4ad17c51e23d7a45d4ed757f05dacb6f7b149f0dbc3d71a9db7c48f0ecde1bf0edc5dea9e1fba9b51d48691f61d4ae7a2b2f899a37c2af8b5a1d9f86dbc79e25d3be03f8b93c25ad6ae352d5348f1bf89340f1afc1a163aef8cbe1eb5dcb3dd7896fbc39abdbf89fe21e9ba4783af2f60f12f858d9687e01b4922d33ec6df51c4b8fcae9e4f4787f87ea57ccb369e413e36c14700a9558e67f50af529e1b0d88c7e1eacf0d3a19ccd3a184c3e17db54ab0c15787b44eb51ad57fbef8bb8438472cf0fb2ff000cf25c556c0d0ce386f0fc6d470ffd9d94e3f3bceb3cc9a7c4d85598e639e6559de332fad82e21ca6b60b31cb327c0e0f1d8bcbf11c3b468c2a4e79ce1abd5ff004fff0006f8cfc21f113c29e1ef1df803c51e1ef1b7827c5ba4d96bde16f17784f58d3fc43e1af11e89a942b73a7eafa1eb9a4dc5de9baa69b7b03a4d6b7b657335bcf1b078e460735d2d7e237fc100be34f863e297fc13fbc39e1ad1fc5da4788f53f869f12fe31e986dadaded746d5c782fc59f14bc5fe39f879e27bef0b9bcb9d6349b0f18f873c451ea7613eb091cf3de47ab5839179a4df5bdb7edcd7cf61ab7d630d87c472b87b7a14ab723e6bc3dad38cf91f34612e687372be684249a778c5dd2fe01af49d0af5a84b5746ad4a4dd92bba73716f4725adafa49aecdee7cbffb6d7c46baf847fb1efed3df12b4e6d3d756f07fc09f8a1abe84baaeac341d3e6f1147e0fd5a2f0edadceaf90f60b79adcb616a93c1baebcc9912d124ba6851bf0b7c2fe00b1f84be0ff00037c24d322860d3fe13780fc13f0c2d62b6b8b9bbb551e03f0c697e19b892d6e6f2496ee7b7b9bdd3aeaf239aea592e65170659dccaef5f7d7fc1657c47abf84fe047ecffe20bf3e1b4f82ba67ed95f022e3f68d9fc5903dc7876d3e1a897c4abe15bed7a396297498f40b0f8e6df086fb58bbf1008f44b0b6b6fb75fcd0436e664f88e59259a479e691a696e19ae249ddbcc69de63e6b4e64cb79be716f33cc0cc1c36e0c4106bf06f1b71f2f6991e57c935054f13983a8d354ea4a525868538bda5528aa75253576e11af4de9cfafeade196123ecf34c7f345c9ce960d474e7828c557949abdd42a39454745cd2a72b37cad28e8a2b91f1cf8e340f877a02789bc4f34f6da3b6bfe15f0db5d41079e20d47c65e25d2bc27a235c7ce822b47d6b59d3e1bab82c45b432b4ecac91b57e11084ea4e34e11729ce4a108a5772949da314bab6da49756ec7ea92928c65293b4629ca4deca31576df9249b7e478f7ed37fb36f81bf69af879aa781bc7706b77da64f68c469ba5f8a7c43e1a8eee58245bbb7633e83a969f22df5b5cc31dd69b7339b882db5082d2e9eda496da0921f903e1ffc45f89bf0dbc41aa7c25f1ddceb3f1a7519bc15e29f1dfc0af11a3689a67c51f88f0f8124b27f1dfc29f1b191740f07cff133c2b06afa2ea7e18f17093c39a678ff00c3ba8e754b6d2bc43a16af3defea810518a9e195883ec54e3f98afcaefdaab53bbf0af8dbe10eaba0d9db4baadb7eda9f04745b191ac64bab8b3d27e210f197827c70b66d03c72db47378475cd5a5bc72c6d82d9c32de452476eaa3c2e2dc8307c5bc2f996539861b0f8b9e03078fcd324a9897283cbf31a387589fdce269b8d7c361732782a184cd69d1a90589c1b7276c561b0588c2ed85c5d4cb7154f1d42a54a137530f4714e8a4de230b2aca93855a6d38579e1d57a95f04ea465ec711b7ee6ae229d5ee3e12f867e18fedc5e3dd3fe0bfecb7a378bbc55f10b54d027f84bf153531f0b7c6de0cf04fec77f057e2778ab43f18fc76d53e3468de3ed07c3de12d03e2c78c3c3da6dfe8be0af875651eb9e33f1cf8b6fecafe37b5f07e89e25f115b7f6d50430db430db5b4315bdbdbc51c1041046b1430431208e28618a30a91c51a2aa471a2aa22285500002bf297fe095761a645a5fed6ba85a59dba6a373fb4278234ed4f5058905dde0d27f64afd9acdadbdc4e14492c360d7d762d524622117336c0a2420feafd7f48fd19f83324e18f0af8733aca659c55c471ee5192f18e633cf330a19962f0b2cdf2cc3e370f9461abe1b0196508e5d967d6abfd5e31c1d3a957138ac763b1129e231955afc13c44ceb1f9b713e63431bf5451c9f158aca70eb0587786a13860f115283c44a9cab6225edabfb38ba8dd69a8c234e941aa74a091451457f411f0a1451450014514500145145001451450020e83e83f952d20e83e83f952d00145145001451450015fcbef86bf68bf82bfb2f7ec81f0ebe297c66f17eafa5e9fe28bdf1bf8d7e206b16fe0cf11f89fc55e26f8f3f113e2078b3c55f1726d6b43f05786eeefbfe1286f88179aa787afef358b1d3edb4896c34dd0354d4ece1b0b7f2ff00a82afe5ebe3c7ecc9ab7c76f8c7fb6efecae7e346afe03fd8e6dbe2a4d7de3dd1fe1dc165a97c73f1cfc42f8f9e1ed0fe3d7c42f86ba2f8efc5f06b9e1cf847f0cbc0fe2bf135978ca3b8f0ff86b58f196b7ae78eb53d12d6fbc2fa5e92efa97f16fd3ab84f2fe29f093217c499fff00abfc0992f889c359a7885888e68f2aaf57849d1ccb058a8e5d52394e7b2c6e714f1f8bcbe594658b27c7471b8e74a15161694678fc27e99e14e615301c4d51e1b075319986232dc4e1f2d8d2a30ad2a78a955c3c9ce4aa55a10a74feaf1aea75a75a9c20adcce49f2cbf93bf1cfc49f11f8a3f69cf8c7fb4e7853c2f7d6da9fc58f1f7c798ecbe151d5fc3de1ad4fc05acfc54f04bf853c0dadea571145aae9d17c58b1d3349d3bc41e296fb6adeea1717faa5a5a4ecfa6cab2eafc6cf8f1aaeb9a4f81be2078cbc73a27c28f12f86ae3e13f82f5af037833c3cde24d0adb40f0df817e1f7c0ff00869e058ef759b687c53e24d0fc33e0bf0478794f8c25bc75d6f5ed5f5cf13becb0bed3add7f463f6e6ff00825f7847f645f015e7c7af007c50f1f7c45f819a678d2db55f8cbe1af8e177e0fd53c7fe0093c7f7c3c257bf14fc37f13b48d0bc3567acf82127d7a1b0f889e06f127875ef6cb4cb94f19683e258f51d33502ff99de0af0143fb4d26a169a17c3df10c9f0dafb5cd2edfc6df15756d2e2f12412f803c21ac5b68fe0ff037c1917ba70b9d6af354d3347b75b2fb25a2d9fc39d2ae66d6b5ed627d4bec427fe9ef03fc50f00b3ef0ce9f1ff85f9fe12af09f0de4b95f0061d62330cea867b81abc239652a79664388cbb1347078e8e6ef058dc0d555e8e2e50cd69e65524e9d2a346552bff006de4d8be08e15af578d72eab82c9f8830193e659665b94f13e71c494b8a94e9653808cebe4f9770c3a181af986699bd2ca9e2b38c3f1552c96be4b8ccff0ef23c9a9e519acb11fbddff06fc8d43c25fb537ece7e1c865f18683a8f8abf62df8e3a978abc2dabdbe9e961aaf8621f19fc04f17e85e20912d2de4bcb1b5b7f1bf8dbc4bfd8367ad5ddbea56326b3aedaa58c76b2a8afed7ebf8d7ff821bfc4bf887e22ff00828e6b7ad49a3f849be0d7c49fd99be37781fe15b78692e6f9ac3c3ff007e31fc2b4bff1b5b6bd6d0368f7de1cf176b3e3b87c17a6bd94b1e91a8c9e024bef0dcb79a7c725cdcff6514f8031ab30e18c2e33dbd2af56be3f3d9e29d093a946863967b99471d8353e58a955c163235f0b8b51e650c651c4537394a123fcff00e32939f1163e6d3f7e3839f34938baaa781c34a35d465294953ae9aad479a4dba33a72bb4d339ff15f853c31e3bf0c7883c15e36f0ee89e2ef07f8b346d4bc3be28f0b78934bb3d6bc3fe22d0358b496c356d175bd235186e2c353d2f52b29e6b4beb1bc826b6bab79648668dd1d94fe1c78c3fe0886fe0896f67fd8aff6d1f8f3fb39f86e18350b8f0f7c0cf1ed8f85bf690f819a1de34310d2b43f0f5a7c4bb5ff0085a7e12f065acab70c340d0fe282c16d25e97b3f26cecad74c1fbcf457d363b2ecbf33a3f57cc70584c7514f9952c5e1e96221197f341558c9427a7c51b4bccf070b8dc6606a7b5c1e2b1185a8ec9cf0f5aa51949277e593a728f346fbc6578bea8fe0c7e036a5ff000518f8e1a3f88b4ef167ed6ff05fe1bf8e3c0de26f107c39f8c3f0f3c3dfb3168f69f12be137c4df0a5ecda5f8a3c1fa85cf88bc75e26d266b8d2eea2fb5596a1378785beb7a3dce99af69c8da6ea36cedeb579ff04f8d43e28e9375a47ed5bfb59fed17fb44e8f771ba37835355f0c7c1ff0000bcb1dc457fa4ea1a9f873e15e81e1d6d7757f0ceab6f69abf876ff0052bf921b5beb481eeac2ee35f2ebf49ffe0b5be09d1bf676f8b1fb3afed91f04be0dc5ac7c59f16eabf107c09fb45db7812c56cbc51f16fe09785fe1f5f78d9b55bed174cb191be217c47f8677ba05adef8292e5a0d7f50f0cde78a3c1f69ab4a2f744b087c03e14fed25f067e33f84acbc69e00f1c68bafe877883fd2b4cb87d462b7b805926b2bb16914b73a76a36b3c72db5e695abdb69dabe9f770cd677f616d7704d127f2ff001a61b15c339f6330594d6c07b0a51c36294f0196e5787c7e5b0c64aacf094315570d868e2f0d397d5eafd5711cf49632342a4e9b752956852fddf85f1587cf72ca188c4d2c4bc42f6942ad2c4e371b88c3e2a54553556b52a35eb4a855a6fda43da53f673fabcaa724bdd9d39cfb2f0cc5ab7c38f873a2d97c40f1d6a5f11b59f0c691169faa78eb57d2f4dd2bc41e319ad5de0d3af755d3b4648b4d97c457d6bf63b7d4ee74fb6b68b56d4d2eb5616568d7725bc5f9d1a678d62f8eff001eb4bd17c1fa947adf843e0678cb5cf885f1b3c4ba54d14da1c3f17dbc37acf867e18fc1eb1d47ecd756dac6bde1bb0f15f8a3c6df10ad74db9b71e1bf2fc23a5dfde36a9773e9f07d03f1f7f6a3f87da14567f0eb41b3bff883f13bc513987c23f0a3c36d687c7de2ed4ad1d5a286d6cae189f03f85ede5649bc5bf14bc62347f0b783f401797cd717fa8bd86997991f023e185f7c29f874ba37886e342bff1ef8a3c41e23f88df14755f0b5a4961e19d4fe23f8daf06a1e217f0d58491c125a786b48822d37c31e1b864821b83a0e83a6cb7686f24b891ff009d7c5be2da7c2dc278dc2b9538f1071653ad80c1615a852952c9f1b4b114736cd7eab4a1054b0ea9ca580c137ec294b17895530b1af1cb7174e97e87c3f97cb1f98d0841cbea7974a9d7c5d44e5397b7c3ce954c1e09d794a4dd5a934b11898b73a9f57a5cb59d3fadd194ff0077bfe093513c5a07ed8e1e47903fed65613465f04a4537ec9ffb2eca912b02731c5b8ac60e36ae171802bf5a6bf29ffe09657fa7cfa6fed65636b3c2d796bf1ffc05757d6a8ea6587edffb227ecceb0cf2a281b16edecee4c6cc4b39864cfddafd58afee0f022abade09784551a69cbc34e08ba6acd35c3796c5dd74775aa3f9838c63c9c5bc4f0fe5e20ce17fe643101451457eae7cd85145140051451400514514005145140083a0fa0fe54b483a0fa0fe54b400514514005145140057f35fe24b4f8d3f197f6a4fdb67e227c00f8c3f0a7e0d7c14bff8eba5780ed356d7fe06ebdf163e20f8e7e31fc0af00f85be11fc6fd42d74e93e28fc3df0a68df0fec3c47e1a87c1961adbc17de20d73c4fe13f11dd4522e931d8cf3ff4a15fcceea5f14be257863e167c61d7fe0b7c3eb5f8a3e33f86dfb5c7ed63e0ef13fc35f146b13fc3ad675996dff6a3f8a3e21d5f4df076b3ace9afa4de6bcde19f14691af785d6fcd8787bc56ba8d9470f88b4f82e45f0fe2bfa7a67d9864fe07e0f0397647c359dd7e29e3ee18e1c51e2fa583abc3d809d5a39ae6b86c7e3e39954a395c3971794e1f0d46be675a96030f53131ad8997b38347e9be12e068e378b3f7f8bc6612384caf1d8b5fd9eeaac5d7707428ca851f611a95e4dc2bcea4a1429ceace34dc60aeeebe07fdbba3fdb5bc669a1fec8bf16bc4ff0001fc2bf023f686bbf10e8f69fb48fc31f0078f6fb59f16eafe136b2f1bf86fe04f8b3e17f8b3c6f269ff000cf58f16693e1dd5f52d5bc4969e3df16e9be39d2b4ad67c2fe1897c27a90f3eefccf4ff00d823c6fe29f08da4bf1d7e34fc4cf8a9f0f748b2b94d67c2de16f0be81f04be1cf8bb4567b5b6d374cf14378627d43c79aee936ab07d8ef74c87c770d86bf05c5c5b6b505cdbb3475ee7f197c5df1dbf6d7f12fc0ff85e3f65df8c1f057c35f0dbe34f823e357c42f147c53d33c35a6793a9fc3195f55d17c0be091a4788fc4bff000926b1e24d66f61d3eefc5965269fe14d1bc336fe22b93abdedf5de9fa6cff00467c08f883ad6b9fb3178dbc01e39d62d352f19fc21f8d7e3ffd9cb5ed75fcf497544f0978aa0d4fc3be21d64c91ac726b571f0fb56d0351f134f62ab6336a31dfde5b436e92fd962ff33703c79c6fe1df0170f65bc350e0fe16cc71dc4186a9c61907052c9f155302b3ec763b01438be39a65f5b36c665b8cc6e072dc0e0e585c2e7542860952cab1597e170f84c650a347fa4f0b93e5789ccab3c752c7e66aada960f1f9956c6c61528e1e952ab2cbeb61a73a143154684aa55a94eb4b0b529d67531346b4a75a8d6e6fbeff00e0923f05f45b0d6bf68af8dc5ec239744f13e97fb27fc31f0968f67058787fe1b7c31f833a4691acebf65a35a5a5bda5adb5e78ebe2278a752d4756b7b689acac741f0a780b44b03147a34cb27ed657e687fc124bc1b67a07ec61e1af1edb2eb81ff0068af897f1a7f696dde20d42cf50bc9b45f8cbf13fc4be24f015c406c26b9b5b1d3ee7e199f055c596951dccefa5c520b29e4f3e19557f4bebfdc8f0af22ff567c35e03c8a787faae272ee12c86966349d374e72cdea65b87af9ce22bc65ef7d6b179b55c6e2f1729b73a98aaf5aa4db9ca4cfe3ae25c77f69710e778f551d5862734c6d4a326d4bfd9beb152385845c7dde4a5868d2a54d47dd8d384631f7520afcf3ff828d7ed87e2bfd923e13f87af3e16e97e10d73e2f78f75bd622f0f5bf8decfc41adf86fc29e01f87de17d57e217c5ff008a1ae784fc27a8e8be29f1869fe0df0768874bd33c33a1ebfe1fb9d6fc77e2ef0469171ae695617f777b0fd99e3ef8b1f0b3e145a58ea1f14be25fc3ff0086d61aa5c9b3d32f7c7de32f0ef83ad351bc54321b5b1b9f116a5a74377722305cc16ef24a10162b819afe7f3e3a7c76f87bf1d7f6bdf1afc52935bd03c6ff000afc2baa7c39fd857f67bbbf074d69e2d3e3cf1d7c55d5b41f10fc7093c23369579749ae49aeeb9adf807c29ab5c68971e5e9de1af841e27d49f36f6bac13f793972c5b5bec9776cf2a853556a28b768abca72ed18eadfe9f3363e3b783ff686f127edd1fb31ea5f1b7f6b1f85df1a3c071fecddfb42fc56f0b7c1cf85df0413e16e81a0b788e7f82df0decfc5b26bb79f163e2b788fc6ba4ebb63e32d77fb06f753bfd36dac7fb3f501a69ba37d73f66fcedfdacbf651ff00825acdf1720d27e304de0ff0f7c72f15e9afe2f4f05f846dbe267fc2c9f156937324ba6c3adeb3a07c009b4ff17f8861966b1b9b3d26fbc576fa8dc4cd05e8d3256c5d39f4ef85fe3ef15fecefe26fda42ff00f682d3b52d457f60cf879f087fe09f1f0c34eb7d0ae27f8a5f1aeefc3de2ef1e78f7e1858f81ef354d7754d4bc5fa9fc6bf08fc52f807e1bf03e911b593df788adeedae6329653c967fa5bf08fe0678d7f638d7bf626f8c5f1252f358f8ebfb49fc7cf1d7843f6bcd5f431a25d699a6eadfb44fc26bbd4fc09e0559649927bdf03fc07f13fc17f835f067c01ab697733cf0e8967aceb2ba7093c75e2390ff9a99b7857c67e3afd303c57cf32ae3be32f0fb80bc38c8384f82313c41c1b8ca580cc339e237926033cafc3b82af89c356c338e5b573acc3139dd6ab87c655c24aa6030b051866146ae1bf6bc0714613837c3dc9309fd9d9666b9b6718fc7667f53cca82c561a860e188a987a58bab45bf8ebc68d3861da74dd4a7cf2bbf67289f8b1f08fc03f07be187867c6c9fb227ec8bfb5778d2ff4992cadbc4b07c3efd87fe3c7853c47e2b30aac3673eaff00127e3c68fe079fc66b059c00db5cea3e2dd59cc36f6ea1f09091d3c1ac7ed5de23ba7b4f087ec63e24d26d2ef426d4749f137c67f8ddf093e1ee929a9ba168347d7fc3de0ed43e2af8d34c909c0b864d25e484e54a06e2bfb3f911658e489c6524468dc7aaba9561f8826bf063c53a3bf877c51e24d0240cada2ebdab69986fbdb2d2fa68a22c3b1312a37e35fbde0fe87de15e1ebcf19c4398f1d71d63f1151d6c4e61c59c490962ebcd469457b5adc3f9770fcea24a0d45d59549a8c9c6739a50e5e1a1e2e715d6a6f0f838e4f9451a314a951cb32ca70a50836eea34f173c5d38ebafb908abbbdaf7bfe66fc17f86bfb5cfecbff13be3b7edc9e24f8ef7fa1b1d3be08f8f3c63fb30fecffa978a35ff00863ad783be1427c37f877f1221f188f886ba768de32d653e12c5e2fd4a2d6f4cf04e89ad5adde91a4dd687afe9915b4da7ddff006395fcf2789fc3779e34f871f1abc17a7ea49a3dff008b7e00fc75d0ec353960373059dfc9f0afc557d6134f6e0afda2017b63009e0c8f3622e99f9abf747e0c78dae3e257c1ef851f11aec592ddf8ff00e1af817c6b74ba6c9e6e9cb71e2af0be97aece2c250f2092c84b7ec2d640efbe0d8dbdb393fd33c3d93e5bc3d92e599064d84860727c930384cab2bc1539d5a94f0797e030d4b0f84c342a57a956bce3468d385353ad56ad5928f354a93936dfe639cd7ad8ac7d7c6e226eae271b52ae2f1555c631757115eacea56aae308c611752727271846304dda314ac97a5514541742e5ad6e56cde28ef0c130b5927567812e4c6c2079910abbc4b2ed322a90cc808520906bd79c9c212928ca6e31949420939cec9be58a938a7295ad1bca2aed5da5a9e5c5734a31ba8f334b9a57518dddaf2693692ddd93d3a13d15ce78461f14dbf8734b87c6b7ba4ea3e288e2946af7ba1413db6953cc6e66685ace0b90b322ada1b74937aaee9964755546503a3ae7c0e2658dc160f192c2e2b032c5e170f89960b1d0a74f1b83957a50aaf0b8ca746ad7a30c561dcfd962214abd6a71ab09c6156a4529bd7134561f115f0f1ad4712a856ab456230d29cf0f5d529ca0ab509548539ca8d551e7a529d3a7370945ca106dc514514575188514514005145140083a0fa0fe54b483a0fa0fe54b400514514005145140057e20fed37e13d5bf66cfdb1aebc737105e37c03fdb5eefc27a62ea71456d269df0e7f6bef0be87ff08cda6957f0db2477b67a47ed0ff0cf42d061d2f559a2bbb1b4f88bf0be4d36faf2cef7e2069315c7edf57e7b7fc150e0b8b7fd903c4be31b3bf874ebff0085ff00143f67df8a16525d32a59dcc9e0bf8ebf0f3529ec2f24765486def6c7ed76e6673b2191e395f08ac47e33f485f0f32bf14fc17f11383335a2ea2c6f0de618fcb2a4671a5530b9fe4949e739062a15a719aa4a9e6d81c24710f96d57073c4e1e7fbbad33e8f84738c4645c4993e6587928ca8e368d3aaa5ac2786c44961f13092baba742a4edfcb35192d628f9e4c8c067e670bf3040c06ec73805be504f405b819c9e2be4b7f81d2782be1978ae5835496fbc577ff00133e207c6ed5e7b0436f613ebfe36f104bac5ed9da40ead7725ae85a42d9699a44b7d7173750c762eab20b7921b787eb89d04534d1afdd8e59107b856201fc40aaf2471cd1c90caa1e29a3786543d1a2954a48a7d99188fc6bfe5e723e21c7e475212c1d4e5c3cf1d97e3b1741420feb5f50facaa5467269cbd94a9633150a94a2d46afb48caa294a95270fef67469d4952afcaf9e9c65ecdddab2aaa3cdeedf9799a8a4a4d3714da4ecda747fe0991fb45685e02f82bfb427c20f895af2e99a0fec90daa7c54f0eea97b13476da7fecb7e3bb0f1278f7c36b6816213de69ff0a757d03e257c329121fb6bd8695e09d062698aded923796fece9fb507c59ff0082a978ebc75e0ad47e3d78bbf604d0bc17a6786bc6177fb1e7802c745d13f6e5d63e17f88a7925f0a7c47f8adf183c436de23d17e1ef85bc712d9db6cf0b7c07d02e3c4fe13b6ba9fc39e2cf8b5a7f892f63d3ecfe0af197ec9a7e34fc5687c3927c42f881e01d27c37a3f8efc0ff1374ef0278dfc6be054f8c1f06bc582d5e4f869e35bbf05eb5a2ddeb3e137d6edf4cd567f0fea53369dac69f7daed9dccb0f9e239f9ef1afeccd17ec4f75f0dbe387ecade0fd1b42f1ffc07f10cbe3df06dbf85209341d63e2a6976f6d65ff0b3fe05fc43f1031d4357f13e93f1b7c17a4ffc23cd79e24bbd767b7f16e9de10f1446f1eabe1db6b81feebf85ff4eaf0eb0f80f0bbc3ee2579ad5e2ac665b966599e6735214e965583c243da60f2dce2ae2e739cf1d89c565b43079a66b4614e87b18e231552955ad5a82c2d4fe64e22f06335ad8ee25ccf2bad84860a356b6372ac0255a589c4fb4a70c555c1d38282a54542ad4ad85c23f6b51ce54a9c274e11a8aac3fa1cf87dff0004cdfd853e1b14d4f4efd99fe1978ebc689a8699aec9f133e396932fc7ef8ab7fe28d1b49b5d1b4ff13ea3f143e335c78e7c772eba2cecad927d460d7a09dd90c8bb1c823f22bfe09dff00b257c548bf6ead6340f8bff193e1a7c6af04fec376de2ef8937117c2ef83da97c3bf04c7fb59fed55aaf8af5eb4bbbdd6f5cf137881bc7fe31f87df0cbc43f1035bd42eb47d3bc31e1cf0e4bf187c2b71a5787ac6ec59bd9fea37c54fdb8bc05af7ecffe0df891f0db55bc4f0efc55f83da37c641e239ad6482ebc31f0d3c41e18ff0084a649ee2d33e745e268b495bbb6bad307fa46937d6979697223bf88471aff00c12bfe1adf783bf645f0b7c49f125aea56be3efda9bc41affed53e39b5d6ad45a6b1a3cff195ed75af04783f50864b0b0bc8ae3e1efc29b7f00f80a686fe396e23baf0ddc912f94f1c71ff00a23cca73b2d5435beeb99e892df657bbd1dec9753f0970952a3cf2bc5d6f7631d9b82b4a527d6cdf2a4baa77fe53b0d7bfe09fff00087c53fb6e69dfb6e788b57f16eade22d1bc17e15d2f4af84f73a9bb7c268be29782ff00e12cd27c31f1fef7c379f2f53f8ade1bf01f8cb5bf87de18d4eef7d8e83a2dedcea36b66dafae9ba9e977ffe0a2fe1ff00136adfb1efc5af10f81acb4dbef1e7c231e0cfda03c1316a5a06a5e240fe21f801e3cf0cfc628ad2cb4bd15e2d725d475ab0f065ff0087ad9b4695350c6af24712cf1cb2db4df6e5153470f87c3baf2c3e1e8509626bbc4e25d1a54e93c462654e9d29622bba718bad5e54a8d1a52ad539aa3a74a941c9c69c52c65294b979a52972c5463ccdbe58abb518df68a6db4969abee7e51db7fc16dff00e09adadcdaee9fe04f8f5af7c55f10683a536b13f85fe147c09fda0be20ebd7f6c22f3638f48b6f0e7c2ebcb5d42e6604245145780799f248f190d8fceff008adfb586bbf147c79aef88be007ec69fb52f8b2d7c5f2e8be25b6d7fe354bf0cbf65ff0003429e21710ea8167f1b7883c59f1526d4740222d4af7c3d2fc1eb4bebcb6926b6b1bb1731a6ff00d54fdb4fc21a768727823c49a258da694ba8cfabe93aac7a741f645bbb848adaf6c2e2e120d96e5a248ef221218c4cfe6aa972a8a07e5a78d3c23e38f8ff00f12fe167ec85f0db5fd63c21aafc6fff00848bc43f17be23f87aeee34df127c2dfd997c0371a0dbfc58d7fc23acc3a76a4ba27c49f1d5e78a7c37f0abe1b6b33c51ff606b1e2dd47c616ef25cf856389a27294a5c8e31ba6acef2b5da4efa38f9e8d3f2d6ccf4b0f4e14a8fd6555a8938be649415ed2b72da4a7ab92493f3e973f33be2cfc5ffda77e28e9ff0019fe1ff867e2678af57f89de12f0578afc337df043fe09bbe02bfd7acf40f8952e8434d83c21f177f6d1f8efff0008fe81a55bea17b7d683c55e0bf875a4784bc7167e1dd47c5da15dc91dee9b03c7fd1c7eccff00f050dfd9a7c31e00f817f083e227c2cf8a9fb08c8de1af00fc2ff86be03fda03c309a67c3eb4beb1d0e6d17c37f0d3c3df1cfc31ad78d7e10deeb36967e1d4d3343d275ff1ee8de29d7c4ba3c56da3cdaaeab1591ecfe377ecb1f0c7e0a7c0ff00877a1fc0af0368df0ffc03f06f49b2f07d87847c396c6df4fb3f0a4b26d8b50b977696f354d5c6b12b5f6b5afeab717dae6bd7fabea3ac6b7a85f6a571757737c3b756fa66a9a3eb9e19f10e83e1ff0017f83bc57a6cfa1f8c7c0be31d16c7c4be09f1a683758177a178b3c33aac371a66b5a5dc80330dd40d25bcab1dd59cb6f770c33c694e54a4e164d5d3becda6a3af4e8b677d74e6b0dd158ca6aac672535eeda4dc926adeeb6f5db5bab2d6ea2b5bff00417457e48fec4df156f7e08f8afc21fb2bf8a3c4de33f197c21f8876fe239ff649f1cf8e7527f12f893c0b79e0cd2a3d6bc63fb21fc41f1c6a17926b9e2fd6bc11e1d5b9f1bfc07f18ebf0dcebde31f83363ac787fc49abea5e29f85da8eafe21fd6eae94d349ad53febf0d8f2e519464e3256945b4d766828a28a620a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800af963f6e2f84b6bf1dbf637fda8be105ce9163aec9e3ef80ff14741d2b4bd4af24d3ac67f11cde10d567f0b4973a842cb25825a78920d2af05ea9ff00456b759c8611953f53d3258a39e29219a34961991e29629515e3963914a491c88c0aba3a92aeac0ab2920820d168bd24b9a2f4946ed5e2f46aeb5575a5fa6e09b5aad1ad53ecfa33f0dfe16f8c6dbe227c2ff86bf106ce2920b4f1dfc3df04f8ceda096786ea6b783c53e19d2f5c8eda6b9b667b79e7b74be104f2c2ed13cd1b9438aeeebe69fd993c36bf0a345f8a9fb329d27fb097f655f8e1f117e0df8774c316936864f853737f0fc49f81dabdb69fa3dd5c4165a5ea1f093c7be14b2b21225a3bdd68fa9426d2de4b59a08be96aff00931f13b83b13e1e788bc73c0d8b84a3578538ab3cc8e2e77bd6c365f98d7a182c52e694e4e9e2f071a18aa4dca4e54ab424e52bddffa0fc3b99c339c8b28cd20d358ecbf0b8895ba549d28fb583b2494a1539e3249594934b4461dbf8774ab5f106a1e2682068f57d52c2d74ebd9839f2e6b7b372f0930fdd130276bcab86745456ced06b8cf8c9650de7c3bd7a495417d37ec7ab5ab3748ee2caee22188c8dc0c524a8533860d83d057a7d785fc7ef12c1a4f839b424947f68f89268a0485705d74eb69527bcb86191b519961b75241de646007cac4799c2ffda18fe29e1e8d1a95ab6269e61972a7294a551d2c2e0a74e5257937cb430f84a325cbf0428c395251d0f5dd927d355b2eba74f91f9bde0bb7bbf19fecf7a57ec5b0eb77da6eb5e3bfdb275dfd8f7c1d7d7daa785f51bd8fe107c62f1958fed2bafae8ba7dd5ac76cb61e0efd9efc7ff0011b48b2d0efb4fb8bf82e74911c66e208c5d47fd8e59d9da69d676ba7e9f6b6d6361636d059d8d959c11db5a59d9dac4b05b5adadb42a90dbdb5bc2890c104489145122c71aaaa803f948ff825e7c289bc4dff000570f8dde28fec9dfe0af85dfb3b7c2ef8cd7b7d2ea3ab2c72fc76f89165e35f80be17bbb5d323b36d1669b44f837e0df1dd8de4973a8c37910f14e9f343a75c055bcb4feb02bfea67c1fcdeb67de16f87f9be2272ab8ac6f096432c5d592b4aae2e865d87c2e2eabf7e6dfb5c4d0ab5149c9b929a949464dc57f0571ae1a381e29cf7034d28d1c2e698e8518a7751a3531352b528ad17c14aa421b69cb6bbb26ca28a2bf483e58f92ff006cdd3deefe10c37688a4e95e2dd12ee5908cb476f3457f62db4f51be6bab743eb91906be1aff008269f849fc45fb467ed9ff001b6ee0bf92d3c391fc15fd987c1f717fa66910dad9c3e09f09de7c62f884ba06a0909d7a7b7d5bc4ff0019f474d64dd5cae9b713f87b498ed2d8c9a6c92d7e8afed4f6ad77f033c680296fb3ae8d7871d42daebba6cc4fd005e7fd9cf5e95f08ff00c120b5ad535cf00fed9d3eacced3587edfdf1af42b42e9b08d2741f87ff06749d195471945d32d2d555f03781bce4b1272b7ef9bfee5fe774b5f927dcece7ff6151ffa88e5f928a9fe76fc4fd51f16f87ad7c59e18d7fc357a8af6dae6917fa6c9b803b0dd5bc91c532ee0c03c12949a36c1db246ac06457e10de595ce9b7979a6de214bbd3eeee6c6e9181056e2ce67b79810791fbc8db83ce2bf7febf197f68df0bdd784be29fc40bfbbb49f4ff0fcfabb6b69ad5e46d6ba2c716b16f16a32993559c47a7c223b896e50ac970a5163f9b041c4575f0bf97f92fccdf2e9bbd4a7dd29af93e57f7dd7dc7876a9e1ff0010f8dbc2dae783bc1d7769a7fc484bcd0be247c0ad62fa3596d3c3bfb467c27be3e31f837a95d2bdcd9ab691afebb6971f0ebc5d6cf796b6da9f827c6fe23d32fa5fb1dd4ca7f6bbe007c5dd23e3efc10f84ff001ab42b7363a77c50f00785bc6834a927b7b8bad02f75dd22d6f755f0cea4f6b34f026b1e18d564bdf0feb56cb2bb59eada6de5a4844b0b81f85fa27c49f03dd6b5a7db7867e23f80753f1225ec13693a6e87e3af0a6abad4ba8daccb3db2d9e99a7eaf737f717293c4ac90c36ef2165c0435fa41ff0004f6d52db48d27f690f8331dfc6e7e147ed1de2ad7f46d126d6ae753d57c3fe09fda37c3be17fda73c35a65ed96a31c7aae916ba65f7c5cf11786ec2dae8cf6ae7c3b7634ab83630c76d6a506f58bff12f9eff002bd9fab62cc29a528555f6af195bba49c7ef8bf9a47e86d14515d079a14514500145145001451450020e83e83f952d20e83e83f952d001451450014514500145145007e2afedefe17b6fd9d3f696f02fed7d3b5b68ff0006be36f867c3ff00b3a7ed2fe25beba68747f01f8d340d66faf7f660f89bacbbc4d6da3f87b57d5bc5de36f839e32f115ddcd969d637de27f85925f916b6971756fa53de59daeffb4dddadb79633279f730c3b148c866f31d76823904e030e4120d7ebbf8b7c25e16f1ef85fc43e08f1c787344f17f83bc5ba36a5e1cf14f857c4ba5d96b7e1ef11681ac5a4b61ab68bade8fa8c37161a9e97a9594f35a5f58de4135b5cdbcaf14d1ba3107f16fc67ff000488f889e0fd4e0b4fd92ff6b0b7f007c20b5b8b9bad37f67cfda37e0c5a7ed29e0ff07da969e7b5f09fc2bf88ede3cf877f17bc05e0a4b9b8748b43d6fc55f116df42b08ac74df09c7a0e996434f9ff00cd3fa5f7d06333f1ab8ce1e27f8779be5797711e3f0582c0f15e459b39e1686712cb30ff0054c0e7196e614e956a54f34580a583cb31382c7470d82ad430787c5471f42bc2b52c5fedfe1b78a987e17c03c8f3ca389ab9753ab52b60b15858c6b55c2fb697b4ab42a509ce9b9d1751ceb4274e6e709ce50f6528b528f23e32f8d9e11f0d5b4f1e99770788b5801921b3b1977d9c52018125edfa830a42ad8252169659065502f2c3e1df887e388e0d27c49f15fe25f88f49f0c78474281aefc43e35f14ea36be1ff0008f87ac63ff576f26aba8490d9c206e58acb4db679f52d4266486ced2f2f6748e4fb834bff00825dfedb1e25bbb06f16fed4ff00b2f7c1cd262bab88759b2f81dfb27eb9e39f11eada54b1ca915c68be2df8e7f1875fd2fc21afdb39866b6b893c07e2bd322903f9fa75e2045ae0ff00660ff826efc10f8cbfb584bf143c57e20f8a1fb497c36fd8a3e226a1e15d33e227ed25a95978bdfe31fed6fe14b3b2b3f13ebbf0f7c17a42787be13fc34f84dfb38eb125f69ba5d8780be1578726f157c76d4fc5d25e6b57ba47c2ad11354fca3c26fd9e9c7195e229d5e24c7641c2f42aca31cc31d3c77fac9c4b2c3a9c5cf0b81c0e5d86c3e47868558c1dab3cf6aca94a54aa57c3e3bd93a0beeb38f1d387e8519ac9f038fccb15cafd9bc4d3860707193da53939d4c4cd45eae11c3d3e74acaac2ea4bea5ff82407c16f883e18f087c78fda3be21e85e38f048fda7fc63e02d57e197c3df1fda5be91e23f0efc17f86fe00b1f0ff83b5cd67c2caa753f06eb1f103c47abf8e3c6927857c413bf8874ad0b55f0ea6bf67a26b926a5a0e99fb194515feb470d70fe5dc29c3d92f0ce511ab1cb320caf0594e05579c6a622586c061e9e1e954c455842946ae26ac69fb4c4558d3a6aad694ea72479acbf99333cc7139b6638ecd318e2f159862abe2ebb845c69fb5af525526a9c5b938d38b972c23ccf960946eec1451457b6709e23fb454d01f82df126dcc91b4e9e1a699a0debe72c6d796eb1ca63c975469118249b76974600e54e3f1f7f663fdb37f670fd88be127ed8fe29f8f9e318fc198fdb235197c3de13b1d3af356f1afc47f1078ebf678f83fe23f0bf87fe1f7866cd1ef3c4be21f16a7877c40f691c4d6fa75ac9617b7dae6a3a469505c5fc3fa71fb5ff00836e755f87cde32d21ee60d53c2e1adb5536b3bc22ff00c21ab4b0c5abd95e46985bab6b7ba4b1d47cb94ec892dee64505c807f9b9fdb63f675d53c69f0b7e3afc4bf86ff183c73f08fe2043f0a756d7f55b5b49b40f107c2ff1cbfc2bf0beabace8f65e34f0878a7c3dafcba436a7a4e9ff00f089ebbe28f877a8f83fc53a978605be8f7ba85ddbc1118f8b133c442359e1614e789f613587856728d19d6b374955947de8d3752ca7cbef72dda69d99ea61a842b61a0a529722c4f3d5e44bda28a8a8ce30e6ba7271778b7a5f74ed67f6bf8eff00689ff8299fed722e8f85b50d1ffe09bbf05af6695349d3ecec7c2ff167f6c4f10e8ef6f2c715f789f5ed66d75ff83bf062eae9e482f57c37e1ef0efc49f10695241f64bbf187993dcdbd97c09f17bf61ef8716be28d275bf8dda87c40fda77c4d7b65ab49078cbf688f8aff137e315dbdbdf6a125d6a7a23685e36f13df783edf4a82f2e5ae74fd32c7c2d6ba769915dfd9b4e86de04112feb9f853c40be2df0a7857c5892dbcebe29f0c787bc4827b47596d263af68f67aab4b6b2a332496ced765a075665688a10c4735e13fb49e9e24d17c33aa81f3da6a77560cdff4caf6d84cabf8cb6a09eddfad7fcf163be98be3e789bc734303c49c638ac8f26c66271985ff00567853db6419561270a18874283785aab31c63a75e2a9bab99e33175a6a4d4ddad18ff0067e45e1ef07e4b84a0f09936131759d384de3731a50c7e2aa394632f68a78884a145b69351c352c3d35d29a77bfe5869ff00b10fec70d796f6f67fb2bfc04d2ae2f247b18f52f0e7c2bf05f87fc436675346b296e746d7b4bd1ed752d2355559cbd9ea767730dcd9dd2c5730ca9246a47ecf7fc1007f643d07f677fd8cf4ff008b9ff0966ade28f885fb4e59f847c41e3eb4b983c4761a2f8224f859a6ea3f0e74df01e91a7f8b35ef136bf26a5a26a765e27bdf19f886fb5831789bc63abeb3a9693a5e89a0ff00656976df9fd6530b6beb1b939c5b5edadc1c75c433a4871ea70bd38fa8afd9dff8245ea3e3293f63d6f0b78e6dacadb59f871fb43fed5be00b4fb0ee31dc786748fda2be23df7842ea62dd6ee6f0beaba49bb23833873924963fe88fd0878af39cc389f8fb24cd337c76650af9064d9be1a966398e3319528cb2eccb1783c555c2d3c456a91842aff6c61638b9c52939430716dab23f2bf1d72cc261b03c3b8ac2e128619ac5e3f0f51e1f0f468c66aa51c3d4a6aaba708ca528fb1a8e926da4a555ee7e9d514515fe8c9fce014514500145145001451450020e83e83f952d14500145145001451450014514500145145007c8dfb7d7c59f197c08fd89ff006a9f8c7f0f2ead6c7c75f0dfe04fc48f16784afef6d7edb6fa7f8834af0cdfcfa5ea0d6864892e1ec2e847770452b340f3c318b88a783cc864f5cf807f097c1bf023e0b7c31f83ff000fedaf2dbc23f0ff00c1ba2f87f486d4ef1b52d675016f6a92df6b7afea8e91cbabf893c41a94d79aef88f5899167d5f5cd4750d4a75135d3d145007aed14514005145140199ad69765ade8faa68fa9422e34fd534fbcd3ef616e925b5dc1241328383b58a3b6d61ca361872057e036b7e1cd1fc45a0ebbe19d66d16fb45f10e89acf87758b1959bcbbdd1f5ad3aeb49d4eca5c107cbbab0ba9ede4c73b643de8a2b9ea7f1697f5f6a27a997fc15ff00eddfca443fb1e69d6d61fb247ecb9616cac96d63fb3bfc17b2b64672e63b6b4f875e1db7b78f73659bcb863440cc4b10b9624e4d6dfed07690c9e028d983662d7f4c74c36304a5d21fc0ab1cfe1e94515ff293394978c78eb36bfe33fcd568decf3bc526beed3d0fef4caa5279765e9b6d7d4b0dbb7d28523e2636f160f07a1fe23e95fb23ff0004a4d5eef53f82ff001dedee445b345fdb23f689d32d191595dede6d7b4bd6d8dc333b07945deb1748ac823516e902142e8f248515feb7fd0675f15f899bd5af0db37b37baff008caf824fc7fc78ff00926b28ff00b1f53ffd576607e9fd14515fea79fcae1451450014514500145145007fffd9','Male','1981-06-20 00:00:00',5146542334,1,'m.uhcapp.mobile@gmail.com','EN','TESQ00000001','3','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im0udWhjYXBwLm1vYmlsZUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsImlhdCI6MTQ3MzM1MDM4MiwidiI6MCwiZCI6eyJwcm92aWRlciI6InBhc3N3b3JkIiwidWlkIjoiNmUwZjdlNDctY2Q5MC00MzIzLTg5YmMtZTIyYjFmZDA5ZjU4In19.nuI4twJ0A6rLpY3q7I6nXBQtdk7i3VD54_rcHzbKIQs','2016-12-14 18:50:02'),(111,49109,'Opal2','','Test2','QA_Opal','Test123','ffd8ffe000104a46494600010101006000600000ffdb00430001010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffdb00430101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffc000110800b400f003012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00fa32e2e6512c4025b102d34f505acacd8e16cadf92cd012c48eacc4b3724924e6a48ef25da30969dff00e5c6c71d4fadbe2abddf0e8003ff001e763d3febc6dff1a746a0b05c70781fcffc7f957f4bd49c949c55f5bf7eecfeda6dddeaf7643aa5adb6b9a66b3a36b16b6d77a26bda5ea7a0eaf68b6565fe97a4eb96171a4eab6d8fb3100cb617b3a47201ba372ae39519fe64fe1be81ab7eccff117c6bfb15fc5090596afe18d7750f107c12d76eede0b7b4f891f0cb5ebdbbd4744d4346bc99447737eaf13dcfd8f2d3f9d2eada5a6ebcd35a03fd3c2c6ac7057231939ce3f9f5079af99bf6a2fd903e0c7ed71e10b0f0cfc4fd26fed35af0e3dcdcf813e22785ae974bf1cf81750ba28d2c9a3ea263962bfd3ae268e3b8bcd03554974db9b8896e623657fb6f57a325ce731e1ace30b9fe551f698bc242ae1f1181ab3952a199e5d88e4fad602756319fb1a95254e8d6c3d7945c69d7a307ac5ca2ff0ef1efc1dc078cfc195320c457785ccb0957ebb93e379213fab636118fb39bf6928ae57c9ece50daa42a54849c5b8ce3f9197097369218ae22806cc8de96f6e564c36095262c310461f27729041031c56334833b44049c939b7b70783c6008bdf9cfb76cd701f1c7e0c7ed2ff00b1ff008fbe047813c55f1afc0ff1efc15f177c61aaf853c2e751f0f6a9a07c48b0d1b42b2d3ee757d4b589e48e6b73fd996b7e90db5c0d675df3ef210258d629140f40bb845a4f34277707721618778dc02acca1888c8054b2e48049c71803fa6f8538c70bc6186c5e370185ccf013c062a9e171b87c7c692950c44f0f4abaa74ab50a95a8e260e32e6738fb2718ba71953bcae7f8d1e25f8619ff008579ed3e1ae289e5d571d57072c5519606aba9edb08ab4a946b57838a78794d3872d3bd48caf2e5aba34bd5fc15aa5d5c23c323db9f2576c79b5b7e841c8e61e79f5c9ec702bd2239d8a2fc96fd3fe7d6dbff8cd78a781ef560ba313b0fde2e429ce4b0079f423be3209f439af6385832020e4f73efebf8ff9e6bf60caabd4a9848fbf37cadddf369ab5b2bf36fdfb2d8fe5ee23c1d3c2e6d592a31a70ad185482e4566dab3e576b5934bcf6bf776fcf6fee5bff00e02dafff0019a3cf6fee5bff00e02dafff0019a868af47da4ff9e7ff00813ff3f25f71e27243f923ff0080aff2f25f71379edfdcb7ff00c05b5ffe33479edfdcb7ff00c05b5ffe3350d147b49ff3cfff00027fe7e4bee0e487f247ff00015fe5e4bee26f3dbfb96fff0080b6bffc668f3dbfb96fff0080b6bffc66a1a28f693fe79ffe04ff00cfc97dc1c90fe48ffe02bfcbc97dc4de7b7f72dfff00016d7ff8cd1e7b7f72dfff00016d7ff8cd43451ed27fcf3ffc09ff009f92fb83921fc91ffc057f9792fb89bcf6fee5bffe02daff00f19a3cf6fee5bffe02daff00f19a868a3da4ff009e7ff813ff003f25f707243f923ff80aff002f25f7123cce40f92df8233fe8b6bd33ce3f73ee4d7d4ffb395b7c20f8ada078f7f66ff8f5e0ef0f78d7e1efc52b4b486e348d6ed218ade4d42c1849633d8ea102c17ba36b963314bcd0f5bb09a2bcd2afe18eea262a668e4f952ac699a8dd689aad96ad64ed15cd8dd5b5e4324676bab412213b9b83e5ed0491b86467bf35e27116534b88325cc327c4a752963684e1cb2bb8b924dc54aff65ecfaeba1f5bc07c49578378af28e20c2c6319e17111856e58ae7786abfbbadc8f4e59c233752134d38ca17ba3eabfd97bc71f10bf66efda47c7ff00f04e3f8c7e2fbaf1e699e12f04d87c52fd90be27f892c6ce4f14f8cbe063bbc771f0f7c51a8bda18f52f12fc3611cd636f74246964b4d135a823034e8f468a1fd338eea51181b2d0e7273fd9f619e4e483fe8dd79e6bf263f6daf10c4ffb4aff00c11bbe3fd946f1eafab7c61f19fc23d62f6326137da1f8cb4ff0bc32e9572898df099b59d66510b2ec4fb5baaa93935fac088c994c13b5b616200058601e07be4700e4f3debfceac6616a6539a6739349b7fd958f7428de52972e1ead1a35a9d2949eae54673a94937792a70a706da844ffa3ff0038bf15c65e1e65b8ec6579e22be11c30f0af39f3cea61a5461570ce53bb72b45c96af44acbbbbeb7929320f2ed3961ff30fb0f6e9fe8dec3f0a592f65500f9767d4e7fe25f61d31c74b6eb9fe5548c7203f2b0073cf2793d3d0d4c33c0ea6b9a35ea49da29b7eaf63f6fe69777f7b2b3dd4fbb3e55a7de7c7fc4bec7907b9ff0046f7e2abc9713310db6d3a118161619e0ffd7b7bff002a9e58db0c7208dcca00ed8f518e07bfd2aa955006413d791ef8ea0e7d2b7bc9ebae84caed5eef44fa9564b89370dd1d939c104ff67d8fcb8e839b6f73dab365ba9839223b50391ff20fb1c614923fe5dbdcf7ad5644cb12b9209c9f5fa564cdb4bb12a769380be9eb9e47f3f5ae8a52692b77fd7fafc0c6eecd5dea9f565492f6525815b41918cfd82c3b8ffaf6aa705c4e6f6cf0969ff1f30807fb3ec707f7c9827fd1b8ff000ab2c9839db8cae3767a9e7b751c8f4fc2abdb47b6fad096cffa55b063e9ba54248e318ebebf4aef849bb5ff00992fcbfaf9238bde525ab6ae9def7eabf5397b93ba64ff00af7b11d08e3ec56fcf34c0a0723353dd60ce8720e2d2c78cff00d38daf3f874fd290267073e848c75f6eb54e52bb7777febe5f810f77eac76360eb9e47201ee40a94e57013396e0673dbd78e339e73d7b5206007201f73ff00d7153479770dd02ab118c0dc48e39e0638a9eb7f34df9ebafdeb404ecd3ecd1f8b3ff056ab193c25e39fd883e39dfb4f1f857c19f143c65f0fbc493796f25a69d278f349d36e34cd4a770765babc5a7ea822918867164c50830907c5b5fb468ae1669090194dac878dab346cd80af93bd4aa8557c72a109eb81fb57fb42fc07f057ed27f063c75f047c7c2487c3fe34d24430eab6e893dff00867c43a733df786bc55a6efdb9baf0feacb15d7d9c32fdb6c24d434b9a4582f9da3fe768789fc6ff00b2ff00882d7f670fdb12c65f0af8834a8d6c7c07f18665b9bbf873f143c2d6ce60d2356b5f12a42522bc86d8c705c1bc2935bb2a5a6bf0699aa44e927e8fe18716e59c399866d92e7388a581c067d5e863b039962251a583c3e654e9470b53098eaf39461878e22853a5530b524ece546b53be89cbfcd5fa6df837c499c66f937895c3580c566d4f0b838e599e6130d0f6d5e18484a5563568508eb5650aa9ca50d6a4a353dd568cadecb14ef6b2c5346c54c4ea78e776de99c67a6074c7031debd83c31e203a8c6b04c497438cfae4120f3d3d324003f3af14b7b8b6bc852eec6e60bdd3ee904f63796aeb35adddabe3cab9b5b88d9e29ede6f98c7246c55b073b5be51da7822e045a994720f98300670c33bf0413c6077007d0d7f4f6538a953c4d3a6aac6746aa53872494a12527a494a3eecae9e92526bf047f983c4b81a55f2faf2ad4a5f5ac346d16d38d483a6d7346a2b26b95abca2d5d59c4f7153903d46011d70d81919e9c7b714ea8e339dcdea7a7d0019fc7f5c67e9257da3d1b5ae8edeba2d57976dcfc953ba4fba4fef414514521851466a26739017a1e720f50076c7f9c034d2bf54bd7fafeb6ea1fa6ac968a8c4833ce07be7dbdc0a6bcb852c4a80ae57181ce3df239edd0d2e97e9dd6abefefe42baeeba7e36b7e6bef26a2b1aef5ed1f4e865bcd4b53b1d36ce0f2d67bbd46eedec6d2dde799608166b9b996382337170e90c01dc19656d880b2e2b45271921982939e1979e0f41f3673dc700e2b38d6a5394e10a94e73a7153a9084d4a508caea329ade316e324a4d72b69a4db4eda54a55a9d28579d1ad1a35273a70ad2a738d294e9a83a908546b967382a90728a778a9c6f6babd8a866ce0a019672b101c825a43b54023dc8fc3d7a557bcd4f4dd3ad67bfd4b50b1d374fb58e49eef50d46eedec74fb3b7890bc971797d752476f6b1448acd23cac0000019278e0be1e7c54f037c59b3d6b5bf87bafc3e21d2fc39af3e8779aadbaedb46d4ada286566b312b25d4d620cc12df5292de0b2bb756fb1cb709b987357ccb0187c4d0cbaa6370d0cc71f4ab3c1e0655e9c317888d3a7ed2a4a8d17275251843de94b939547de7eeea76e1f27cd71597e273aa19663eb64d97d5a34f1f9953c355fa9e15d79c6108d6c57b3f654e7535f671e672693d158fa13c676b7df1abf6ebff00827bfece5a2c4da9e81fb1ef82f5ff00daa7e365f44af359689aef8bc583f80740d46ea13b2df569bfb23c2af04571e5cfe57897cc8d184130afdad50fb149c74192720e58e58b29e72492c47bf04f53f895f173c29e24b5f0927fc1437f656865d1bf697fd9dbc3b611fed1ff000e61bdbf93c2ff00b52fc07f0d2da5b6ab67e20d1ad647497c51e0df0fd98bfb5d561885ccda1e98b7114675df0f6957737ebbfc1df8a9e0cf8e7f0b7e1f7c60f87d76f79e0df897e17d3bc59a199995ee6ca2be888bed1b5009858f53d075586fb43d4e20a9b6fec2665411ba9aff003c78bf0b8fcbb8c78a286714bd8e3b199955cc68a8ff000ab65f5392961aa53924adece118d3a91b3f7df326a334cffa1cfa2171070ae69e16e5782c82bcea62b0f86c3d7c646a4a0dd44e851a5cd4d464da54aac6a467cdef5e493574d2f44f2d4ee05b7eee491c0ea38e471d3bf6a7800630a063dbf0effe7bfa54ca8173c0393e83a63a7e79fce9d804e0004f5c639c7ae2be7a9c946576ba5bf147f56140c6543601e4b1e71dc9ff003fe78abe4b618e09c1c7518c6d27f3ce2b53661b046431739e70a0ae141edc76155e5428ac09c8ceefbe133c0e3ea319c7a7ae2bb55dd96d7febe6296cfd199520eb9ddbb031c704e093cfd73df81ebdf36e21f988c1c1e4f3c8dc0e718e83d3f3adb660aa5864800f19ce4f1dff00cf5aa2df3e58ae720b608c9c1e7afe95ac6eb76ba5bd76d3b7f5b331b3ecfee39f963618008ebdf9fa74f51cd47671b1beb63b87377003c1e36ca831f503eb5a170992b8c0e39ed9c773ef515a214bdb4063fbd7701e99eb2a03d01f4cf5ef5d74e7aaf5575f7184e37764ac9b8fa6ebfaf5f538bbb8c79eacaa01167a7838e383636ddba75fc69a15b0383d3f0fcfa1a9ae06d911b91bacec4104923fe3cadf9e7bfe9506ecf01b3ed9ae995aef96f6f3feaff79ccf77eaff00324006704724f00f3f2f7f51d33d6ad8c0000e80003e838a8a21bb2ee3e65e8718ebdb1819ea4faf4f4ab0aa1ba93c63a77febf91150e496ff00d7e80b71c15594640ca9eb8e7b7f9f5079041af8ebfe0a04de18b2fd8aff00692d77c57e0ef0c78e2dbc37f0b7c49a8e83a4f8af43b1d76c74cf146a0d6be1ed1bc41630ddc724fa6ea7a35feaf6da9dbea1a74f673ac9671ee99943c6ff0063fdc5207392303bf1d7049c9273f85786fed41f0ba7f8d9fb36fc75f84566affdadf103e17f8ab44d0d1582193c451d90d5fc3502b1006e9f5fd2f4fb760ceaaf14ee1b906bcfc7c6757058b50846752585aea9a714ff0078e8cf93469ebcdaa4efaf4d11cb9ad1a95729cce8d0a6aad6a997e3a34e1249a9549616aaa7a34fde5539396c9b72b595f7fc01f839e188bc27f01be12e93129dd6de0ed1af6e5d9bcc7377afda26b7732f24ec8a49af8aa22ed8d123550b919af42b2b86b2b9b7ba42576b82483fc3900839e99c919e6bc9ff00663f15a78e7e01f83edeed648bc45e11b597e1f78a2cae495bed3f5ff08b9b48edaeadce1e0965d2d2d64f2e401c94989c9422bd34a97041c8da4ab0e48041fd33efff00d7afecce17af84a9c2fc2f8ccb2cf092c8f2c74145f328a586a34e71bbbd9c2bd3a94ea5dde352124ece2d1ff3abc4d86c6d0e29e32cbf3853fed1a3c479dc3171ab094652f6d8daf514d45ef0ad4ea46ac34d615232da48fa1748d5edb50b789e175dc4e1d01c90149dd9eb838208f6e6b7abc63c092917f346ac07c8c429eec570df8e3a707fad7b229c165279dcdd3ea0f739e41c8ff0afd53055e588c3c6737efa4959f6dba7debcbb9f8467580a596e3eae1a949ca09f34538ca3cb1766d5da5cd66eda6db3f27d31a54552c48017b8c96edfc3e9cf5a64c42a9625800ad920138e9d7ffafe95f39fc58f8c5a5780a0b497526d59d6ff00518b44d2ac7c3da45e6bfaeeb3ac4d04972967a6693a6473de5ddc0b78279640230228d039c230cf26719d65b9065d8acd736c5e1f0581c1d19d7c462b155a142850a507152ab56ad46a11847997336d24aedbb236e1ce1dcdb8ab34a193e4b84ad8cc762a70a787a387a53ad56b549735a952a715efd4928cacb995acb5bbb1ef1a86ab6b671969a55501771c37cc14b118f2fbf2be8480093d2bc1fe227c66d37c0be1ed5bc59abdd43a7e89a4db8cc84096eafef1d9869fa5e9569bd5efb51d52e02da5ac102c92248ed2baed89caf05e0e8ff6a0f8df751e9ff02ff63cfda17c6f7774c9b3c41e3ef0c4ff000cbc01689705920bebef13f895ad6d16d13789ca25c4324b1ab88b7b11b7f413f61aff00827b6afe1dfdbc7c0fe19ff828d4fa6defc5cbaf01b7c5efd8e3c05e1adba97ecf7e25d47c35248ff1292d351961b697c4df1a3e11b0d3f568bc2da95adce9f75a74ff00f097417facdbd8d9db8fe13fa41fd3efc1bf09f8578931591f12e5bc63c599470f6699f61785f8731787c7e673c3e5b878d5af88ab0a356755e170b197d6718e961e4f0d85a588c557ab4f0f86ad28ff006f7833f426e39e26ce729c471ae555f86f86abe3f0d87c56659bd3fabc6ac6abe6586a349fc152b38f252ab5ead34eaca34e34673924ff003cbc47fb4678b7e12476361fb47fc1ef8bbf01fc4f75656f7f15878c3e1ef896eb45b9b2d42da2bed3eeb4dd7ec2d9ed6fc5c585c5acb3c45229ecae1a5b59e28e584d73961fb53dc7c45963d1be0f7c37f8d5f18f5cba60b67a4fc3ff00863e2699669e6708864d4a5b6616d0659375c3dacd1a47f780c0907f763e2af04fc3af08dab8f1ef8f3c2de13b49375ecf0f8efc51e1bd0ede4de4b9be6b7f156a56d13873233b4ed195977b12cecdbabf3eff006d9f8b1e18f83be17fd90fe207c14f8ade18f12d86a3fb79fece9f0cfc69e1cf873e3af0aeafa6f8e7c01f136e3c4de0ff0014f85755d27c2da8de43a8d8f97a85b6b10c1245b6cf54d374dbe40935bc457fca4e0cfdb35e2f71b4729e19c0785983ab9e6731c4e1f07c435679bc72aa989a597e2b1d85757fe129d0a2f13530d0c34652aca10756152528c5367f72e69fb3f7c0cca3195f399e7b984b0b879d2aaf26a388c273fb3752109d2a51ad59e2acb9a4e34fdb39495d73deed7e297ecfbff048ef8f3fb4f5dd8f8eff006eeb3d4fe15fc24b48ae2efc39fb34f803c4921f1eeb3aadc5acc9a6f88fe2678bb4f8efecbc3efa3f9bf6cb4d1ed85e6b524d14504d65a25bfda3ed7e952ffc11fbe25ea3aaebda67c0bff828f78ca6f0e78635b9fc27ac689e33f86fe17f89be30f036bf6b6d05ccfe17f13eb9a1f882c1ed359b3b0b8b59d6cf57d2f4cbe7b7962b98ecc43279b5fd72c5a2d868a6e74db6b54823b59a7b5785a358e4cdbb18f0e02ee0ee518be464b863c035f82bfb5c78fbc69fb3c7fc150fe145efec09f0b2d7f69cfdacff00698f83fe28f067ed3ffb1df84efae3418757f0f784ac34cbef80ff00b487c54f14dadadc787be1b5ef852f6e64d0b50f10f89ae34cbdf10f81a18e09af2de0d42cef26fe2af0c7e9e1f4b8f1efc5ae2ca5c1bc6f9de4dc679d70ee678be1aca729a5849f0a55ad923fadc32ecd6a62e8626393e16be09e3f0d84cf2a625d08e73532fc062e74f0b8ba35703fd1b9cf82fe07707f07e479663f80b23cd787f2cc6538ca78fa50ad8db6263084f1b84528cfeb15dd6a746a56a2a49cb0b0a92e79ce09cfc5be077fc102bc0dac789f43f13fed53f1d7e347ed33a568d7d657f2f823c40f69f0efe196a53d9dca5c98efbc2fa25eea5ac6b7013180b6526a7a558b8675bbfb44323c0de13f12bf601f0d7c63d03f6dbd73f659f84fe0bfd9a3f6f1ff0082767c6cf10786aefc33f0534fd4bc27f07ff69df8073680df12fe1c5978cfe1ccf7d7fa269be31f157c3696ff004efed5b06b2b4d53c59a0224e122d7ad64d23f74bc35ff000491ff0082b87ed3f69fdbbfb64ffc14ced3f655d0357b512bfc00fd817e1f59d81d0a2b8984a74bd4be3578a65b6d7754d46d2d9cdadc5dc56daf593dcc665b6bcb987685a51ffc1ae5e0af04eb1e2bf1cfc0dff829ff00fc1477e197c5df1c1b09fc61f11754f88be1bf19c9e339f4fb19b4fb193c69a65b68be12d43c57f62b3b8b8b4b04d475e63616d2bdb5bb2c276d7f577087865f4c8c5e7d5fc47f107c7ecbd71e61a79063b8570b82ab9ee6397e438acb337c3e2b34cbb36a9878e4b83ccf2dcf725fed0cab1d85a796d6f638caf97e6387c62ad96d1ad0f80ceb30f0ae7954785f22e01c361786aa53c761f31c0fd5b074a8e3e8e2f0ca9529fb27eda6aa61ab461561cd3841c63287b35cd75fce7fec9dfb66f86fc3d61f023e22a785d35cf087ed21f12fe19fc16f1378426ba2e34c87e27f8827f0578b618d712c7aacde1c2b7acba7dec71c1a8593c4b33c2657dbf50ffc12874f9bc09e00fdabbf670fb5493e8dfb32fedb3f19be1bf845be731da784efa78b53d36da22e59a30b7b677f37d9d1762c975390d8998b7c85fb597fc1303f6c8ff8228f8bff00671f8abf1c741d2ff6b0fd817f678f8ceff1674af89ff04f427f0ff8a34df1cacb797be08b0f8e3e1cd625babbf0d584de31bfb67b6d44ddeb3e1db97b89f4d8bc4f1ea17361a549ed3fb38f8db50fd88bfe09e7e20fda83c756da378d3e37fed75f15eebe397877c0d69ad34e7c6ff12fe3e6af65a7fc35f87365a8d917b8d467b0d1deef5cf133e95bdb4ab69351b268e1bcb290d7fab7c57c7f438df1d946755aab962f099350c2e6b39d3a74e5f5ca595d08e675fd9c1734a8e2f336aa61a739b72585e654e94a4e27e51f458e04c478679b67585f770fc3f809f12666abfb7ab88a34324c6e6d59e478294a71539e270f8774fdad18424d622baa6aa56518d43f6b4c6c3a1dc7e8063fc7f2ed4edca9fc058e0038049f5ed93c64f63491a5c05805d28b7bafb3dafdb6d95fcd4b5bf10c7fda16c92e7f791dbdd9961490aa33a22b3286255662a3039e411c10323f0cf19c75af1af749ad9a8c97a4926bef4efe5b1fe82a51e58cbdeb49464ae926e324a4ae96da3bdb7efae856ea370071dc918c7b1cff009c0cd40fb37112742011d4fb678efd6b4190ee519fbdc63b67dfd87527151b47186607048c74638e9d0741ff00d735b7b69f97aabdff00325dba7e264c914790022e0839fc7818e7af5ed59ef133498504000f39c0c2f5c0cf391c7ffaeb79c0c1c81963c703b6dcff003cfe35564507000e79e839ec0ff3aaa557def7a4f5b5afaabdd2d5f4dee652934da30ee23000ca8e370e7e6e48c8ea4d54b742d796b8e1d6eadf1d3001910fd2b6268831c752adc7d76f3edc63a1c7a567c71ba5f5910339ba80e40c818993e623a7e07b7b0aefa73f7977be9f87fc1feac64f6f9afcd1c0dd01e6a0c702d2c303d3fd06dc9ff26aaa37ef768f5c1e074ab17ca44a84e00fb2e9e3d49ff41b6e801ff26a38e3e8f9e3a81fe35e89c058a9501033eb8a8f04f4079e38ff003c54b9c28f5c600f71c63f3aca6f5b76fd6c6908f5defb2fbbf1e81b448caa7b32fa8c64f623f518c1a982ece7732ed6122bab6191939560c725769c36464f1de84c82a474ca9638e841e71cf207ae3f0a9950618a924960ca3d8818f4faf63cd476f269fcd3bafc4e84ed17d25f73e9f33f0e7f6cff00d8cfe2d7c2ff008b3e2efdac7f652f0a378f3c3de3c46d53f686fd9f74776b5d6750d6a3769af3e24fc3bd3adcb8bed42ee376d5355d234cb7fed2b0d50deddd9e9daa693a9dc4165f23f80fe2e781be2ed8eaba8f83db55b1d53c3f2c56be2bf0aebfa65c693aff008735099e58e3b5d46da54589d9a5b7b98fcfb77650f0bc52430cc0aaff0052160552fecc943b61b8b5948895f7858e6590a26c206e90aed5ce72cfdd8823f943fd9bb56baf1c689f1dfe2a6bb74da978b3e26fc7df893aef896fa55297724906a917d8ade4464dc218bedb33c51337ee03150a1b7357e8fe12e739de1389f0fc2f84c5c2b6418dc366d9a57cbb114d54fa954c251a3527572fafcd19d0f6f5eb43da61ed528b73a9351536dbff00347e9c1e177036459351f12b0982c4e138a331ccb099629e0dc69e0f12ea4e11ab3c750b38c92a2eab8b8a8d45254ad555351a72fa03c39a81d37538e56c0476547c8c6d2c42b1c9ce718e3a1c935ef30dd452a24c92ab09413c751818f9874c65703a91d4d7cd254e72b90339561c1040ed9faff515ab6facea76f1796b752aa0180b90df280037246327dc74ef5fd5b80cca1848aa7514ea2dbddb5f9acb6bbb6aefa5f43fcb3cf787a59a62235a9558d3aa924e53bca3cab96ea5cb24f5f3dedb1ecdaeebf6fa75b4f99479db3e553ce4919181dce067a7383f8f80fc1ff1ee93a0fedbbf02bc6bafdc6af6fe01fd9534df1afed49f1dbc41a268ba87882e3c1be09d3bc3d71e15d1ae2eb4dd2c35dc8f75ab788acfcc8a23e7c5a7de9bc921686de529e29e2ef899f117c4ff001a3c0dfb357c20f0b5aebdf1abe295d699a7783354f1beb9a4f857c076b73abaccd6b29d4f5abab1b4d426b58a0691e19678e16bc8a3d3e1b7d4eea44d3ee7fab9ff0082637fc1337c39fb217c37f167fc2c3bcb4f8adf18be3139bafda03c75ace9827d2bc58cf05f588f00695a5eaf6cb27fc2bbd1ed751d42dd6cf50b4864d7efae2e2faff4eb5b5fecdd3ec3fcb3fda59f4d2f0efc24f0bb883c3ec5d4ad9af1271750a991e2325c156584c5c329ab5e861f39a943175a9ce8e1abd1c3d5ab468e2674aad1fae4f0f0b4e0b113a1fe80fd087e8c9c498ae24cb3c47c753a785ca726a51ccf2fc4d6f675238cc74e83fa8d474e9c94e3439a73bd1a92855a918a9f2a514a5f7e6adf107e097c28f81979fb447c4ef887e1dd3fe11683e0cb3f1fdefc52bed546afe1a97c29a8dbc171a36a5a0cf6fe74faf4be214bbb2b6f0ce9fa3c535eebb7b776fa758dac970c557f29bc3ffb39fede1ff05ebf107c21f8b7f0eedef3fe09c9fb047c1ef88779f11be027ed0faee977b2fed8bf19352934cbaf0ccfe38f85ba4d8ea5a5bf81bc23ace8b73776b6f71fdafa67876f2ddacef5b50f1f4b64d05878d7c37ff8272685fb42ff00c15a7c35ff0004b7f0ff00c44f897ae7fc1323f664f0ef87bf6edf8c9fb35f89b538756f05f837c77e20796dfc2df027c2de24794f8aee3e19f88b52d7743d653c17ae5fcf0e85a5ea1e375d298ea3226ad5fdf1e8da559e916167a5e9fa6d8e95a569b676ba7e95a669b6d6f67a769ba7594096b65a7d8595ac715bd9d9595aa476b696b0471c36d6b1450448888147f985f445fa2a70370e6130be346699962b8eb33e2bc3d4cd7816ae7583af97d2c8783f3ac2b797ac6e4b2a8f0d5788f1797632a52cceb4de2f01414a7472e7529549e26bff007c788fe236799c56afc3fcb0cb70d84aaa8e67470f52351e271d86a8b9942b593785a35617a0f961525a4eb7bf747e047c32ff008364bfe094de1b8ed359f8c3f0d7e28fed6bf109ede05d6fe25fed2ff1b7e23f8cfc49aedda2219ee6eb4fd135df0d7872de39ae565956dedf4a2c89298a4b9b80035637c6eff835c3fe0907f142d96ffc0df05fc6dfb3478d34e65bbf0ff8eff67ff8a9e34f0f6afa06b16ac2e34cd62cf49f14ea7e2ff0d4979a75ea45776d3368f1dd44f1af9175037ce3fa252caa3682011c05fe94aac5b208c1191d882338c8ebfad7fa134e952a546186a54a9d3c3d3a6a9428429c23461492b2a71a49282825a72a8f2dba1f8f394a52e794a4e77bf3b6dcaebaf33d6fe773f88bfdad21ff0082a3ff00c10bbe1ff8abe24f8d3c5f73ff00053efd8834fd0350d2bc33f13fc67687c3ff00b45fece7e39bfd3a6d37e1b3fc60bd866d564f1b7c2cbbf12c9a569dadebd733eaa2e3cf3682ebc1da8dce9b6dab7ee0ff00c111ff00624f875fb3b7ecb1e1bf8ff77e35d0fe3cfed3bfb69e83e1ff008fbfb487ed3d6da8c3e24bbf897aff008e2cd3c49a57853c2fe2438960f863f0fadb518f41f0e68760b6da7bdeda6a5a9cfa7da5cdc2d8d87ec1f8f3c0de15f88fe11f12f803c73e1cd23c5be06f1a683aaf85bc61e17d76d20bed1bc45e1bd76c66d3b58d1b54b19d1a1bab1d46c679ad2e2371931ca4a9565575fe74ff00e0867aaf8b3f646fda5bfe0a0bff000468f17eadabeb9e10fd8dfc77a57c68fd92754d6eea4bcbf1fb2efc7dba7f13e9fe126b9b999ee6ee2f036b3ace9a24ba7dd1ff006b789759b784adb456d127c3f0c785fe1ef05e7bc47c4dc25c2191f0ee79c5b1c1c78871f9460e9e09e62b032ab2a2a5428a8e1e8fb49d575315f57a54beb95a14abe27dad6a74e71f4b1b9de6d98e130782c763f138bc2e039feab4abd475152f68a2a566fde768a71873393841b8a766effd2dc6851402727b9f5cf3fce9599475ebd40ee71e9f8d32707ca723a8538fc47bd7f1d7ff0005a2f8e1ff000701fc3dff008299fecebe1aff00827e7843e28ea5fb2d6a7a47c367f0cc1f0f7e1ee95e33f86be34f17cdadce7e26d87ed03e21bad0f519fc33656b02ada4916b3ab787b4ab3f082dbebfa2dec7aa3dfdddb7de1e59fd6efc45f02f82fe26f82bc51f0e7e21f86345f19f80fc71a06abe17f17f84fc456116a5a1f88fc35add94d65abe8daad9ce924571657f6934b0ccae0edcac91959638caff009d67c21fd837c1ff00b1c7fc1583e3bfec67f1635df1bfc43d37f662d165f8fdff0004f4d0fc5be279357f87fe16f847f13f5c6d4f52f10d8785aea331cdf11bc2f36ad63a70d59a4fb241ad78775bd6974d96f21d3e7b6ff482b3131b3b66bc8e14bbfb3c46e63b76792dd6e7ca06758247024921597cc113be1d90296018915fc757fc165b49b4d07fe0bcdff04eaf13e870476dac7c44fd8ebe3d783fc6570080fa8f877c2d71e35d5b478c2f56b8b7bdd4272a652b1344a8a1818ce7d1ca65ff000a3848b5cd19d6a709c1fc328f346dccb66a325196abe28a7bea7d3f05e22187e29c91d4a71ab4aa66187a5528ca3cf19aa93e58c9c35e674aa38d785d351a94e33de375e90916720b1dc985cfca4e064af200dd8ce7e619dc58e486a9c4601dd9ce4007207a7e9ce7a714c8727763d1579cf2ca080727ef023682dc6483c559541bb0dc9c03f43fe49afd4d545b5b6e89ecb4b7e1fa1fd972ba9356b6b7b79bd5bf3e6deff693bf529b2b330655c618918cf1fe7af14051b18b6d24373c0ee33dc75e6ad852720638e08f4f4c718fd69922e00dc01e7bfd3da8f69e44946645664047f7b38c8f4c74c77aa9226092a36e33c9039e3d7af1dbdaaf958dbe6da38183807bfebdbb542e012141206415c679c6720fb7d6ad3ba4fe7f35ff00049946fe5f2ff8263488720823927775e4e33e9ffebcd456a8a6fad770c95bab7c8e40e6741c60fafe95a52447214638ead923af3d31cfa75fe5515a43ff00130b3c8247daadfd30479ebdbd3fa576d0a8972b9495f9bacb5edd76324b549aeaaf75e68f25bd4579e2dc0e45ae9e78c74fb05bfa8ff1a8f18000ce0118fa647f93525c12d3ab0c1c5ad829c1c8c7d82d5bf3a67f88fcbbfe3e95edc9b49dbfafbce07ac9af37f9865b6a6370181b88008ceece4ff17206d3ce3a1f5ab2a576aee04903208c6093c9ea0f7f4a66eca8c738c28cfd7ffaf5322919dfc7cad9e9c13ce3bf7cd6176cde117a25bad7faf98f52300f23d4630b8cf4cfbff3fa5588d4e0b0c819c8c8c7000c63ae471c73f80a8d02eddb9c67d467fda1d3b1f5ab480602a9ec0138ee49e71c1e9ffebf57bfe7fd79836deac9e291adee21b9445778268a750c320b2323e0f38dadb363e411b18f1c66bf9774f030fd9b7f6b5fda53f663d599ed746d7fc4f3fc73f8372ccc214d77c17e3bb76d4ef6d34d6e526974557fb0cf12169127d07554c33c0e2bfa8a0080173924e0718ea7ea7b64f35f07fedcdfb10587ed7be1bf09eafe0df11c7f0d7f688f85d7cd7df083e2705912ce1fb44d14b79e0df189b657b86f0a6a778897705ec715ccde1ebd6b9bb5b6bcd3ef752b09f7c9b37c4f0ee7b95f1060e8bc454cb6bd575f0aa7ece58bc062a93a18dc34272f72356749a9d1751f22ad4a1cda36d7e23f484f09bfe22ff8719970d61a6a8e6b4f971f9557925254b1b4274aa527cb2b5ff79084a514ef520a54d72f3732fc9c9e27b595ede4465747db83fc48d929229fe2565c60f539cf43513f0ac704e14e40e0e3ebd7fc7a75c579c7c24f19f8c3e2378675fbff001ad968b65e28f02f8cfc4bf0fbc4175e1dba177e1ef10eade13bb5b1d4b5cf0e4eb98db4db99d892519a0794abd9a0826115b7a48e791c8c6411df04e7f202bfb0b2acd3099d65b97e7797bab2c0e65429e3b073ad074aafb1aa94a31a90e9385dc65d1b4dad1a3fc2fce729cc387b38ccf21cd551866b93e2eae0330850a90ab46388a3cbcdc938ca5eec9352519f2d48ddc651528b45cf137c1af87bf19bc1b3e83afc525add066bad03c47692187c43e11f105bb24b61ace8da84663b9b692d2e5229678639562ba854a1549424a9fadbff0004e0ff0082d87c3af0bfc2ed47f677ff00828378dbc5be18f8edfb3e6bb75e00d67e3149f0fbc69e33f0778fbc1362db7c1be27f1a789fc19a478826d07c5df6189f4abebdf10585bc3e26b6b4d3b57fb73ead77aa05fcb8f045f4915f4d6c59bc8946e2b8520630cdc1c8f99473d0e39048c8afd06ff82195c1ff008786ff00c146f4bb49b7e9375f04be07de6b9a549245716973acdb6b1610add5e69f36e4ba9aca2bcbd88cb25bceb6c2e992575699377f993fb5dfc2af0bf893e8bd8df1338c784eb66b9970163f098ac2e2324c761321cf674f132950aeb0d9d57ca738a149428d4aaeb61f1196e3a8e262e9aad0a52a186c452feb4fa0771c719e5be2be6fe1f61739adfd879be53fda984a58f5531384c0d5a389a187ab185175e9f253af4f114d72c651e59d153859ce7197eaa7fc101fe367c1ff00da0bfe0a99ff0005b3f8b3f0a3c6da7fc41f0cf8be6fd92ee7c0fe27b44d46de3d57c1ba3f84fc57a3deb6936badd9697abc5a4d87882d9acd92e34eb611c8b064482486493faf35e83e83f957f1a3e3cf8a161ff04c9ff82c47c05fdb6fc53058f86bf64dfdb8be1869bfb16fed19e27b6d36d2cb41f867f16341d42df54f845e3ff13c9676d0c563a66a9169fa3595eea93948a0d174ff00175e5ccce6c950ff0063d6577f6a52cad1bc6d1c7245244c24495255de8f0bafc92c2f1b23c72c798dd5b2a48218ff003d7d1d38bb87b8dfc10f0cf3de168d5a593c784f29c9a9e0f138ba38dc765988e1fc252c971996e6388a186c153a98dc1e23052a756ac70583a7884e389a1878d0a903fb338bb2ec6e57c4b9c6133171962d63abd7a95210942956faccdd755a9294e7254aa7b4bc14a72692b36da67f20bff071effc1727f6e8ff00825cfc73f80df09bf666f869f0f34af0678f7e1fcbf11755f8c1f13bc29a8f8d2c3c69acd8f89f52d1f52f867e1cb4835bd1ec3473e1eb1b3d2efbc4d316b9d7ee62f14697269b26950c2b7177fd3b7ec75f197c65fb44fecb3fb3e7c77f887f0eaffe11f8e7e2f7c20f017c43f157c33d4c5dadef82b5df14f87acb55d474375d42387504b7b7b8b9692ca3d46187514b19ada3be8d2e92515eafe30f863f0f7e22c5a641e3ff0004783bc776da26ab06b9a35bf8cbc2da078a60d235ab5da6d757d261d76c2fe2d3b53b7dabe4ea164b05e4780566dcaa476f0c11dbab04cfcecd236e39cb372c7d073c9c715fb49f384afca9fa7f2e7debf9a3d5673a37fc1d5fe13b7f0727927c63ff000496d4a4f8b3f6069251749a4fc61d40784ef3588fe68ede681ec344b3b276da668bc843bc84dbfd285dde2dac724d2490450428f24d3cf22c70c31c68cf24b348582c50c2aad24d2be1238d19dcaa2b30fe143e1ff80351ff0082bb7fc1527fe0a03fb787833f6abfda6bf664fd9bbc0b2f847f633f81df117f657f14daf80fc59f1cbc37f0f61b79be20dbc3e2ebdd36fe787c033ebba72789edefb4c81ee2ea7f1468a63b8824b17b73f25c6dc77c29e1c70e6378b38d339c2e4390602787a389c7e2e6a3055b175e187c3d0a71bf354ad56ace318d382bf2f34dda109ca3df96e598fce3194f0196e16ae3317554a50a3495e4e30b3949bd146314d5db7a23fb41f8e3fb4d7ecf9fb32784afbc69fb457c6df85ff05fc2f656d25e4bac7c4bf1be81e118a685095d9a7c1aa5edb5eea970ee3cb82d34cb5bcb9b897f750412ca421fc39f117fc1cbff00b1d6a3a96ab0fecc9fb357edf7fb69f85f44bbbbb4d5fe25fece1fb31789b5af874bf609e582ea4b2d6fc4d79e1bbcbf822f2a4904cba4c36f731fefade4951b70f30f879ff0496ff8265fc3cf1327c44f187c16f17fed69f17165b7b893e2a7ed99f127c5bfb43f8a2f2eed5bcc82ee7b0f19de0f06968275496153e1794ab2ee6dccaa47e9fe8df1365f0c69b63a2f87bc3fa4f86bc39a5c490699a1f87ada0d0746d2ada108b0db697a4e93059e97636d14602a5b5b5ac508000238cd7f19715fed10f03f25c4d3c1e478baf9fcdd470a98b8c6b470918ab2f69fec54318ed77fcea495fdc6da4bf49cbbc1ce2ec64255311421834a2da84a5194db56f757be95ecfcf53b0fd83ffe0a89fb257fc145bc0be30f177ecedf102fa0d67e18dc3d97c5af859f12f466f007c56f8497886ec2a78f7c21aacecf61a6c86c6f6383c4565757de1d9a5b0d42ce4d462bfb2bab487f96ed7be3de81ff000520ff0082bdfed29fb66fc3ad464f13fecd9fb28fc2cd33f62efd9f7c5d0e66d03c79e2fbbd42f7c43f15fc6de159582a4da689aff5bb4b2d52d0bc3a8787f53f0dea11ed1a8466b8eff82d27c08f87bf11ff006f4fd8235bf843f10bc7ff00b3f78bff00e0a0be39d7bf62efdb4753f821a91f0d78a3e2f7c219f56f8797d7d7fe2082d64874bf11df5be8ba8b683acdeeb76b7906ad6761e1ff00edf5bf5d27cb6f33fd957c19a87fc1327f6a7f8a7ff0495f8b7a84726817de26d5fe377ec2df157544834d8fe357c30f1c5cc8f79e18bcba8441a6cff1034f6d184735844ab3cbe23d1bc53a2d940603e1e8ee3fb0fc20f15f833c4bc3f0be6f93663494f3fcab159ce070536e35654f019856cab16e11ad1a55274b0f8fa33a3cee9c67a37cb78b51e6e15c81645c7385c2f10c65875976379213928aa72c7ce846a60a9549b7cb4d4e3563562db52728c60edce7eb22c6030dbb940200057b7dee39f563f4e2a7f280cb6ee4faf4ff001fd7f0a8fccdc581e0c6719e3ae0672324801b201380415e849517428cf24302380547d7b9f73dabfa56f14dddfbd7b3b5eddb477775f76da1fd48d7349dbf1f927aa6d6fd9edb762895383b14e4f39238c9f704123e9dbf328616d9ba4e719239c77fc4e7ebd3b1abce8708aab9da7a93c804a9efd78071cd31a3255b71da0771ce47a77efc1f5a39e3dc396c9b9796df919c53cbcec049c640c7278c81dbe9f5cfd2a26f99149dc0e3951f7b9f9483c11c77fd0e6ae839665ce323e52474fe5cf5ef51ba7ded8bf3038cfa820127dfd47b71cd5c6a36ed7d15bb6dff00004d24a2d75d7f2b192ca1b7643afcdc1c6dc638db9e413fe14cb34cdfd976ff004bb71c8ea3ce4ff3f8d5d910aa9242b0fbcdc7727d3f41cfe5505a002fad718c8bdb6231cfca6546248ede9db1c56f192728db7bad352396ed69add7e68f18ba5c4917a9b4b124639cfd86d40f53d8e33eb55cabf603b1eb83fcaaf5c80675c8e9656247d458dae2a0afa5946fb3d7b7f5b1e628ddb7da4f408b0c1d76e0861cf5ebcf43d318a9c0246061b1d7903fcfe350a023715e3bb7f2effd2a780e77900f503ea40e71cfae7d2b2358b71d6dba6b5f913468e411851c8e49190001c038e847156a2563ce00da47b67fc7f0a8d7217a1cee031819edf874ab2809dc071f363e848e33d7dbd7a1a527657f4fccb86cb4eafe5a2254fbc49e98efdb9ebfad5c863691d600b879c9850a9018bcdfbb40ac71b58bb800fa91c64d575521978e09c13d8f1d3f306ac06c956462194b152a76957420ab6707043003a1c024e3200ae7928c938cb4524e2fd1e8edf265c95d34dd934d37b5aead7bf96e7f255fb245b9b6f85fe2ef0d5d095359f0bfc5ef8afa2ebf6930cdc586a11eb4ce63bb604b199ba12df2bb238072ac4fb5a020b4641014e320639e871d071dbfc315d0fed9bf0c353fd8abf6a6f197c6496caebfe196ff006aef12aeb5ab788ac2dfcdb6f849f1ae646b9d66cb5cb7b550d69a2f88a79af354d2ae5b61bad2efae618bcfbff0f5c453665dada116b7b61776779a76a76b16a1677b61710dd58ddc17419e39ed2ee06682e6d651968a784ec2a0293bc328fe9af09f3cc0e6bc13956554ea469e6dc33496558fc1549c7eb0a10a93961f170a77e6a987c4509424aa422e3095e1369c4ff043e907e1f67fc09e2ef17ff6a60ebac0f10e6b5b34caf1fc8de1b11ede346556829a5cb1af0a926e3094b9e747966928b2de85a82699a9453cc3f7121712153ca2150338caf0a40cff00b04e319e524f84fac7837c7777fb47feccfe37f14fc2afdaa2cb5c93c63e17f887a6eb972d61aadea58dad9cbf0f7c41a1481f48d43c0de23b5b45b1bcd2afad6ea37b8bb67d41aeac9a4813218a1009c11f89f948393c0e831924f4c5695c7c52d37c05a44173e22d6b42d0f4ede23b6bed7b59b5d29247ebb2de5bc961f3993015bca0ed18c12a3ad7d9f12f0ef0671a70f665c35e20e579466fc378ec262f0989a39c51c2d5c351a78ea1f52ad525f5b71a5ef42b723e792a6d3e5946519cd3fc8329cdf8db8673fcbb883c3fc7e3f079ed3ab8552a38073e6cc69613110c553c256a74bdfad879d4a6955a6d4a9f25e728b71838ff004fdfb317c58f833ff0591ff827dea563f1afc01a70b6f1826bdf07bf68df85b1cf8bbf87df18fc162dc6a1a97856ea469eebc3d7d6f7d73a678ffe1cea6e65bcd27ed71e9f766f16cf50171e7dfb367fc1417f6c3ff8229e8967fb387ede7f0d7e2a7ed75fb03f842e21d07e037edd5f087437f177c40f853f0e625d9a1f807f681f0323fdbe6b6f0a5b2c36167a94da8dbdee9d6101b1f0f5ff008cb495d2acb4af8a3fe0dd4f1459f8935cff008290eb5a06a76577e08d7fe3b7c35d4b443a5dd4779a4dc6b03c2de211acea36890392cb786e2c90dff9690dec9e52a4d3491b04fe9bc2831bafde59a378e54906f49e2972648678e42cb2c3267e68640d193d54d7fc73f1578cd9dfd03be941e32f863c05430dc51e1252e2c78aa3c219862aa50c36168e6183c263e857c8732a7edd65b8ba34ebbc053aea862b0d8dc16130ab1d4b1388a14f190ff7f320e1c8f8bde1e708f12e734ea64fc498bc9b0b5aae215253ab1ab2843db617194e50b5554dd9497b92a555cd4749491d17803fe0bd1ff047df881e18b5f1368fff000502fd9e349b39ad60b87b0f1cf8ae6f877e23b4f376a7937be19f1bd8e87addbcf1ca764b18b49163e64123c5891bc8fe35ff00c1c8bff0475f839624a7ed75a0fc60d7666482c7c21f003c31e2bf8afaf6a93ceb27956d6375a2693178604d2bc7e52ade788ed02492462531ababd78178eff607fd87be296af73e21f889fb1c7eccfe2dd76ea4796eb58d57e0df82a2d56f279102cb717579a7697612dc4ef8e669cbc8c7e7277fcd5db7c30fd94bf662f81f24337c1bfd9bfe067c2eba895562d4bc0ff0bfc1fa2eaf1856dcbff13987487d614a9e8cb7e3f1e6bf73c4fed55e01865eaae17c29e2fad9a7b3ff0071c46759450cbfdaf2fc3fda74a9e22bce119ef2796d3728f48b773c5a7e01e752aea3533ccbe141cff8b1c3d794f9159bb53728c79dea92738a5a6acfcebf8d5fb527fc1487fe0b47a5defc2af86bf0dfc77ff04cbff826f78ba192cfe227c54f88c891fed7bfb467822e9d45d7863c0fe13478e3f87de17f12d9892d355bc1e769f3e9d70c750f176bfa64b7be13d47f4efe05fc0ef861fb387c27f01fc0ff00835e16b5f067c32f871a1c3a0f85b41b6792e668a0df25cdfea7ab6a331fb4eb1e20d7b519ae358f116b5767ed7aaeab753dc3f950f956f17ae10643be566925241691dda490904e332392ff0029242fcdf2e30b81914aaa146146057f9cdf48bfa57788ff0048cc761e8e7ef0b90f08e59889e2728e0ec9ea621e5f87c4ca93a2b30cc3155552c566d9a2a152ad28623110a386a11a93fa9e070cead5954fd9b82fc3dca3832352a619cb198fad0e4ad8fc425ed1c1b4dd3a14d5e3429b695e2a5294adef4a5656f27f88fe33d4bc1b6bfda291db8d3a1866b8bfbfbd78a2b5d3ede08da59ae6eee6e5e2b7b5b5822569679e79228208e3692595235623f1aff68bff0082d8fec69f072d2ff4fb9f8eba77c58f18233db5a7c33fd9fd17e24788f56d4b74b08d3175bd3593c13a74a6e22314a6fbc49e7c085664b0bd32450bfeecea9a5d86af6775a76a96167ab69da85bcf677fa66a7676da869d7d6575134177657d63771cd6b79677503bc173697314b6f71049243346f1bb29f24f01fecd7fb39fc2dbd6d53e197ecf1f02be1e6ac24695756f067c23f00786b5412bb177922d474ad02def6dcb31c9f22e10eef4538af91f0cb8c7c22c82854c47889c13c4fc5398e1aad19e5f85c8b38ca326c163a31e694e966b9863f0199e3b0f094e34e0bea187a557d949dabc66a325f479cd3e20ab0a34723af966139dc9623138fc356c5d5a579594b0d421528d29351776aacdc5c97c0e3a2fc29fd893e017ed59fb73feda5f0ebfe0a4bfb5a7c30d53f66bf831fb3ef86f54b1fd8cbf675f12cf7d278e750d63c4b6f7666f8afe2eb6bcb7d32f6dd243a8cfae5dea1ab693a4378875bb7f0e69fa368f1786fc3e6f2f7f673f6f1fd80be097fc14ebe05e9ff09be266ad7bf0efe2cfc37d45bc53fb3e7c7bf0d46dff0009afc24f19fee7cbb98192eacaeb59f0aeaf2da581f12f867edf6724f258d8eb7a35fe9be20d2ac6eebea720bb798e59a56c9772ecccc4e4b12cdc9249c9279fc8526e680ac9131475742ac09ca92c146dc1049cb00a39cb1518e95fa1613e96dc6f97f8a19171ce4780c0f0be43c359561b86787f82323ad5e394e4dc3185af88c543074ebe3673ad8fc6e23158baf8dccb30c77b5ad98636b55a938c294a9d1a5f3357c3ecbb1393e330198e26b63b1b8ec4cb1f8ccdabf2c7135f1cd423edad051850a74e14e9c28d2a4a30a54e3c9156d5ff0032b73f1f3f6b3ff827149a4fc29ff82b07c35d7ee3c24fae2f85be1dff00c141be14e98fe35f81bf10b4e768edb48ff85a7fd8f670ea9e12f17ba2c4f3dede69163e20beccd71ab7866f26b5b8d7aebf4eb47d574bd7b4ad2bc41a16aba7eb9a0ebba658eafa1eb3a4ddc37fa5eafa3ea300bad3356d36f6ddde0bdd3f51b3921bab2bb819e29e195595c9dcabf767ed3ff147e0c68bfb297ed19ff0d570786aff00f67c8be0d78f13e2543e28b4b4bcd26f74697c3b7f6ba7e9f1595f2c915ef88aff005b9b4bb6f06c56f126aa3c56da4c9a4cd1ea090bc5f80bff000487d2fc6fa57fc139bf665b3f1cc3a8db5fc9a078c351f0ec1aab3b5e2f80756f1f789352f043b0970f14136857104fa744310ff66cd6b2c2890bc59ffa02fa25fd2468fd2338371b9e52c1d6a53c9b194b2bc655951af4e2b17f57a75e587fdfa7cb52952ab4e5569c2b568538ce938ca2a7c91f9ae1da99ee519f55e14cd710b32a10cb3fb43058f9ae6c552a30c4470f0a58aa8bdea8aa5ff7739f2cda4db7256b7e90e18f4e40273dbef01d31e9f9fe151b873940a71c73d00ce7db1d80fc6ada80070393c9faf4fe4052d7f5a1f7dca9b4dea95ae9ecf6bedd1ea6598181c1c36493c73b7a703fc8e314d78c28c819eb904138c0edd00ffeb0e315a1b4976ce7a641c74c9fe67dea09142e703aeecfa9ff00ebd545d9dfc86edd159745d119730ea31c6476e08c7f2cd36c86fd42cb08091756e3a76f393af1c0ff003d8558e40fde0191d8819fbc48ede983d7f5a759a937f68517fe5e6053818c03321c7ff5fa8ae8a4db9c5dbaa6977ebf8ec26bcbac7a79a3c12f0913a6debf64d3f1f5fb0db75a8777f78fcddf193cfb559b8199226eff0064b1e79ff9f1b7148000b9e3819f7e7f0f53ff00ebafa9e7f7af6d2d6fc4f1a3f6bfc4c681f29f7603f91fd39ab71c6bd773617078c8ce3d71d7a74aac9c2f3d0e1bf0c1eb91c1ab69923008e833532776d9abbf246c9bd7a74d5961006207639e7a74fad4e0ed23039e703d4e3bfbd4083629724704f19e7819c8e83f3f7c7b5c8c1201fefe187af3c73f90e99ac66ddedd0b8ae556f31e9cb73c15c37f88fd7ad5889011b8337de63f7b83df3d3e9f8e3bd3530555bdd874f43ebf8d4cac3a1f5f60063e9ee2b292beba697766ae9eda16937b26fe4cc3f11f813c19f13743bff87df113c33a378cbc0be2f48b47f137857c41631df68fade9f3dd478b7bc81b649e6c72b0b8b2bbb596df50b0ba54bbb0b9b7b88d64afe51ff667d2747d37c0df15f47d1ac67d3349d23e3c7c5fd2747f0d4d797d7cde0fd2747d5eded748f0e4171a84935e2c5636512b334cfe75cdc079ee7ccb97773fd6da33c4d148846f132321e410ca729820646187de5208ea18751fcccfed09e0093f64efdb97e267843527363f08ff006b3d4e6f8d1f09b5cba8d6df46b5f88ba8dccb1f8ffc00f74adf65b7bd875a9aee38addcc7249693786ddf71be8e46faff000db32c164dc7791e371ce1470f8ea78ec967899da34e8d6c7528bc0d4c44dca294258ba71c3c3da3e4552bc12717247f197d38383f34e23f08de6193e16588c56439a61731c6468d2e7c5d7c161a70ab3a6aa28caafb3a708e22bf2437926a31e6a894b16f6eadec2cae2fee90bda69f6775a8ddec2037d92c6da4bbb9da4e40dd0c4cb9e76e4311f2f1f6f7fc13a3f660fd8627f815a57edf5ff053ad53e1b6b0ff00181b51b9f83be18f8c5a85fc9f0dfc05e05d3b58d4f4ad1742f0e78334cfb44be33f19eb169a55e6bfac4cda5eac74bd320b69d2dada46d4351af8c3c41a1452db6a1a64fbd2c753b4bfd36e246525ade3d42092d6e72b228c34692c9c10092bb7e5e95f6d7ec5baa6b7fb4b7fc12cfe317eca1e01b9d0f48fdaf3f66ef86ff137e0268ab7bf60d3bc49a7e87e2cd5aeb54f096b9a1eb37c05e787f42f89de196bef00dff896c2eadaca0bc8a08752b848a6b427f9ebf6a16238c68f867c0b95e5bc45c4dc23c119ef1ce5f9471ff13709e3e195e6995e4f8d9428e1b14f32a986c5d2c060238e9e13ebd8a9d0ab0861275972ad671fe68fa08e1386731cfb8db155f0997e63c4b83c82388e1aa18fa0f15ed2518d7ab89a786c2cb96553113f66a9fb28da7294a2ad6dff00633e007c26ff0082675978f3c2ff00b557ec1369f0aa0bbfb26b5e0af12ebbfb3778bb53d23c0fade97a94509b9f0f7c4af00e957d16837baa58ca60d4348b4f1268367ac699791dbeaf64fb523947eb5695a8c5a85a5bdd42731ce81829c7c80fe2d819e064f623b57e267eca9fb3c7c1cf873e1df027c4ef877f031bf678f1e789fe04fc3ff87bf11be1ed8d8b785ffd37c350583c8be38f0ae9f34da46b9e3bd1f5fb3d46dacbc7ae6eb5bd5343be2d26a5736fa9923f533e117885e58e5d12e5cf996a02a290db9c2eedd90c4b2bc6c1a3915b055c32e4edc8ff0096cfa406494b1198d7c46038978c78c28f0da59450cdb8e31b4333e228e070f8bafcf97d4cc3095f1384cc30983c4d5c44f038ac2558d1c461ab4674a9429c5457faff00c3585961729c33c46072ec0e2b12beb588a595d39d0c355a95a14a6ab3c34e14e746b4e2dc6a42a479bda42a6ad1ef65d46327a9c71c9ce09e077e949e621fe21ebd474f53e83dfa7bd7e40ffc14cbfe0a857bfb15b69df0a7e037c0ef137ed41fb556bde107f884bf0dbc31a2f8a75cf0ff00c30f86cd7eda35b7c48f8a11f836cefb5efecfd4b57df67e1fd0ac5b4e975216f25d5f6afa6da4d65f6ef9a3e07ffc1797f640f07f80f49d2ff6aff8bdf1dffe16e5d5c5dea5e2bbed6ff632f13fc2ef0d6817178b04abe10f0a7867c27a878b6e13c35a0b096d34cd53c41a8eade25d5959efb5aba859a3b48be6f24fa2ff008d3c47c0797f88391f07e3b36cb738ab4964d9465b4ebe61c4b99606ac27379cd3c9b0543115b0b93a50b51c66633c17d7dce2f2ba78fa7cf5297878bf10786b039bd4ca3138d8d29d053589c5d4e5860a8578c947eaaebca49d4c45db5285284d5369aa928bdbfa18a2bf13a6ff0083867fe093b0c6d227ed0de2cb875c7fa141f02fe2e3de390d8d9124be1886224f5f9a6400725ab9ed57fe0e1dff00827e794a3c0ba2fed57f15afa45678f4df047ecedafc5391f2ed3e6f88b57d29487cf0d1a301b0e464ae79a87d17fe90d88928af0738ff000f776f699870fe332ca0b58ae69623328e128462b9aedba9a257f8753a6a71f70753577c4396cffbb4ab3ad3ff00c069466f7ee9773f740b2838270704fe03a9a0329ce0838ce477006324fa019efdabf01cff00c168be3efc4ff36dbf659ff8245fedb7f142e1a731d9eadf127491f0bfc353c6d1ef4b99aee3d075e4b74917610926a90c6c996132b9047cbff113fe0a07ff0005b5f11fed0ff0aff6599be05fecabfb10f8dbe347c3df167c51f0add78d6f6e7e316bfa57823c1e6fadb58bdbdb883c47e2dd3ad75c8ae6c2ee2b0d26ebc2f04b77716ec0c76d02338fd07827e84fe3771a71060f86234b82721ceb1dcf2a395677c7fc2bfdb53a5463cf88ad4f21c971f9d6793850a49d4ab2965d4e11b34e6ad63ccc4f897c3d4953585a79ae633ad38d1a3f53cb315ec655aa4e34e942588c4c30d874eaca4953fdefbd7de27f5253cab0dbdcde4cf1c1616513cf797b70c9059da448a5da6bbbc98a5b5a428a32f35c491c6a39675eb5f94ffb4f7fc163bf631fd9eb566f87fe0ef166a9fb58fc7bba925b4d07e03fecbb6e3e2578a2e3544c28b2d7bc53a245a8f84bc371a4ce897c05eeb1acc36e5e58b43b92a12bf37f53ff00826f7c5af8ff002c57dfb7b7fc1413f694fda66de4691eefe16f83755ff853bf06e5126d76b3b8d0744b8ba92eec55b283ec5a4e83733c0c8cb711c9bb6fde9f037f671f815fb34e8a3c3bf00be137827e1669b246b15f5e78674909e22d65962584cbe20f165f3de78a35d98a2b293a8eaf3c0379f2ede341e5d7fa23e13fec97cab0588c2e67e3071d55cf1519c655786b852854cab2faad72de962b38c54a79a57a16bf3ac1e1b29c44a5cd6c4422d259d4ccb8c736728e1b0984e1ac2b4b9b138eaf4334cc1c5a5cdec7038697d5a94d74956c455516ece12b3bfc1da97c19fdb13fe0a55e30f0b7c45ff828cd969bf043f665f066bd6fe2bf869ff04fff0003ea77133788b56b32dfd93e23fda1bc53148d3eb3736fe6befd12e264d49e1924d3acb49f0459cd7ab7dfac515b410471450430da5bdbc305ada5a5ac31db5a59dadac31db5b5a5a5b40120b5b4b6b6862b6b6b6851208208628a3558d1454cb1ac6981c01c80000170301570385ef8c003b600c09bca249258153d171d39eb907ae3f3f6aff5bb813807843c33e1acbf84782321cbb87b20cb292a786c065b87542926d2f6956a3bcaad6af5a6bda57c457a9571188aadd5af56a5594a6fab2bc9f099546bce9cb118ac763270a98eccb1d5235b198ba918b49ca71842346945ca4a9e1a925429ab38ae66e4eb794cccaf90179c0c3671f74e4e400ff4047a53ca01fc581ee326a7906d0a1474ea3d46474cf7ebd4f7a411ef462df29424f6ce0e001e9d73d09cfeb5f607a8528c92a78c7cc7f438cfe38fe94d7405813df8e47403eb52aae4b2afcb819cf624ff00239ea79c8a6ca0f604e370ff000cd005095517e6cfcf8381c1e83e80fa67eb458a017d67f337cd776e00dc7049993f9d3e48a46287705393c1504f1cf5fcbbd36d87fa7590c74bdb6cfe33a01c75cf5ff39ad6136a4aff00ccb5f5b2febfa627b7cd7e68f9f2e642d2c4420406d6c00183ff003e36dcf3de85070d9eebc7bf20ff009cd3ee195a48ff00d9b5b1e0e38c595bfe43dea1849f2c3364e642a339e9c6319eddbb57d6a9349aee796b59fcdfe1a965177139195007ea6ac245804608258e071f87f9cd4299c3632496c803ae320e3a8e07e02ae28224c727386e7b77c77fd3bfe7521a73f6d55addf4d0980ca05619f97041e7b63f3ff3cd595e8a83a85040f63d39fe99e2a0e0f5ce3db83f9f18a9c643eec1c6d518ee07ddfc31827b63a7a9ae7352c851f754700038fe7fae6a6401c82dd8918c71f37233ef91c7eb4c09dd588cfe78f4cd3e0ce23247de7c1f7db839f4e87faf735136d25e772d371b7676fbbfa659dbd011c02081db18eff9d783fed25fb34fc28fdab7e176a3f09fe3068736a5a1dc5d26afa1eb9a65cad878afc0de27b58644b1f16784755f2e5367aac05fcabbb69125d3f57d3cc9a7ea76d2c2d13c5ef48bbc1c9390e71efd303e9d80ab017710a723240ee3a9c73d323d477c62b0a8a9ce9ba55a2a54a76528bbd9fbc9abdbaa92528bde33519c5a9462d678ac36171f86af82c5d1a789c36229ce956a3563cd09d39ae5926b4dd697decf43f9c1f89bfb16fedd9fb32e8be21d6f48d57c05fb5cfc18f05f87b57f105c6a53de8f007c6ad2bc2fa058cbaa6a31dee917c2ead7c417f63a6dbce633a75ef88ee35248480d0990409e01650dcf89af3e1ffed2ff00003e22f8b3e0efc527f0d5b9f0f7c48f87f716ff006ab9d0ef13ccb8f0af8cb45958697e27b0b19e3934ed434ad4219a28e7b48d6785a30121feb002b6f00ed914ab2bc7346b34524254abc32c0e1a39e0951992e20911e39e267827478657afc0cfda07fe09d7f1cff67ff1978b3e23fec4fe1ad2fe2afc19f19eab77e29f11fecbb79a9a689e2bf87daedeb99756bbf839a9dec9f64d47c3f725da48bc38d30d4eca248f4f4d3756820b7bc83eb72ae29c162f2ec5f08f899849f18787f9be15e0b1383cd69acdeb65b3497b2af1a75232c662b07ec954a5569aab52b53f69074d4a9c6a417f03f8e3f45fc56415f0be23fd1f70d3e1be34cbb1957118bc1e5b88ab85a799e1f11ad6a31a116f0919c65fbc84a74946a54e5f6dcbfc58fccdf1c7f6b8ff00829a6bdf093c6f637bfb606936969a7785358bfb95f869f083c2df0ffc5de22b1d36c646bfd3a7f1768b636daae88f73a7453b349a1cb6f23ca3e6217f787faacfd8b3c4fe1cb8f81ffb356b9e0ab7fb078435df81ff000ab50d0ec06a573ab9b3b2d4bc19a3dc4b0cbabde3bddeab769792dd25eea175235ddf5e0b89ee8bcadbabf90cd77e22f8ae1fed3d07c57fb227ed6fa06bf3d9ea1a65c6823e0ceb7a83dc5c5ed94d68f6d15e5b451db5caba4e097488abab1658d738afdfeff823378e2f7c53ff0004f2fd9e97531776dabfc3dbef883f0c2f6daf6378afac63f0578fb578b4cb3bdb7972d05d59693a8595a4904ab1c96e6068bcb545527fca2fda81e0f782bc2de17703e3bc16cab87f25ca23c4b98d2cf72de1fc2bc253ad89cd32fc44f098bcc287b1a2de229fd56ad2a2eac652a1094e945a7566a5fa2fd0ff008c3c61ce73be2fcbfc65c1e734f33583c23c9b119c52a34dca8e1df362e8e1bd83709469559f34e715cd52f17194a3a47f763c23f057e1bf82fe2f7c4ef8fde18f0fb69ff163e34f87fe1f7867e21f8b8ea17d7177acf873e1958dde9fe0ed22ca09666b6d12c6ce0bb69efedf4c481357bd8adef6fc497104257d62f614d478d4ad6cf545e405d52ced75151d790b7d0dc0071dff00ad65787ae7ed7a659ce5b7130280415dac080010070390703df1dab7abfe71b35ce73cc4e61ed330cd730c5e2b0386c0e5387c457c662a7528e5d94612865f96e0e8c9d55ecf0d82c161a861f0d461cb4e8d3a7154e31b58fede865780a51a94e9e128469cebd5c438aa54da956af2f695aacaf17cd3ab52529ce52bb6e4df6393ff00843bc27e6249ff00084f81fcd5c8127fc21be1712202307137f641724ffbc31d339e6ba7b356d3c05b28edec9400156c6dadac7628180abf648a1c019380381e9d2a5a2b8ab66b9a6223c95b31c7d58bd2d531b8ba89adb95fb4ad24d7449a6a3ba45c30383a77f6784c345cbe271c3d15cdaf5b416de567b9f087ede7fb7af87ff630f0778357fe102f1e7c7bf8e7f19755d63c29f01bf67ff87f6f7779e26f88fe21d22ca0bcd6af350d4161bf6f0f782fc3105ed8dc78935b1637d77145731416368c4dc5d5a7e507ec9ff017f696f187ed03f10bf6fdfdbbaf74383f68ef885e138fe1cfc32f83be10b98aebc25fb387c1ff00344ede11b2920b8bed39b5ebd548ecee63b5bed526b65935bd4757d5eff5bf105dc769ef5ff050b0fa27fc1527fe0959e22b895a3d2fc4de05fdb0fe18e9a537948fc57a9f84b49d56ce0e15879ba9d9c896d1903f78cab09c2f23eb2589ed8ba490bc52041bd190af963239656fb8b8e99e84103055c57fd13fecd5f03fc37c83c15e1af18f0397c71fe2071a51cde9e679e629fb7ab80c16133cc7e06965397c2539fd428c6965d86af8b74d2ab8ac5394abcdd38d1a34bf3854259d711636be3ab549e1b86f34587caf2aa5686121556070d5d6658aa718b9e22b3588a90a2e5254e94545463ccd4dbc02cc0e325542fa8202818f9b9c039c679ee79ab8bf2a0cf07a9faff9c0fe75146bb8038c6541ce3d40e33c5583f4ce71c63df15fe989f5b2828c60d7da57d924b44ec92492dfa69e4050b0ce485ea3040ca900f239f7f7ab0aadb4645263e5207385e807d074f6ce6ac8278c71c7f91410561d012393c1f7233fd31ed48dc86521ba761ffd63560a8241f4edefea7d73d29d40143c8db9ce4311d323a0239e3bfe35032959307fbac41ef9c107a5693aab7cc0b7031c71dff1efd7e9ed54651f3700e077cf63d46eedff00d7a00ab29e1093f75b23ebd7ebdaa9dae0ea36bdc7daedcf4ffa794ab12b05eb92a48c6791c8ebcff9fc2a0b5d8fa859904ae2eedf8042e7f7c87078e7dbf4aa8ae6925dc4f6f9afcd1f3e5ca6d911b393f65d3c038c601b2b7c8fd3aff8d1805403db9cf420f53cd172e7cd4254ed3656209047fcf95b9ce0e32476a233f27420eec8c8c1c118fc338afaf4d5b6d75b3fbbfe0fa1e5abf3fcdff5fa9341f7949e49c918e8171fcf39183575158beece140e98eb9ebcf5ed5541d8e06df5f6f5ff00f5fe356e26ceee31c29fcf38edec693f8652db950d6b37d7fe1d7e5ff04b0a033609c6064f19ff0039c54b9d9f3f248c20fc4f2c7af381df3f5a890856e7f8f007e04824fa75fc7156900cbe47f1e391e8076ae7342746f7f971f2f73d063d3bfe953160a9104240001391ea307f97f9046635c631c12003ebd73fcb152a00638c9c7451cfb91cf1e951a4b75b79df7f346b16a4b55b5bfafc09e3dc0118e43063c7b0239fe78fe55641cc88c7b30e9e9ffebc1fd6a251d1b8019d78079c648fc3fcf6ab75cd5eda24ed66b67af4d5efb5ff0022ec96cadf21f18f9b76785dc338ce4f4e39fcaa5da1860e3a9ea323e5008247190091919c1e013d45310100127ef1c6073ea47e80f5c679a734c96f15c5e4d1b4f05859df6a32dbc4c565b88f4fb29efe4b68d9412b2dd476af046c036d791495da4e7994a774d3d55b56b99249ad6de5bf96fa6e27caaf293694549b69a5a25aeaf44acbfe06a71fe39f8e9f0abe06db691adfc60f8c1e03f851a1dc5e410e9f75e39f1969de198f5278678fcd5d2ecaeeed2fb5109feae56b1b29e3872bbd800c07c2ff00f0476f14e81e21f81ffb4847e18d5ec35ef0ce97fb76fed152f8675cd2a68ee34bd63c3fe20bfd0f5fd2354d3ae13fd7595f5a5d43716b26d5492270e07cc73f971f08a5f0efc5bf037883f6eefda35f44f1cfc45f88b2f8afc53fdb7e35b7b5d7bc3ff07fe1778775ad6b4ff0ff0081fc13a46a36f71a4687a7787f4ad3e66bbfb1d8ff00695d4d2a46647966bafb5d5fd883f6acfdaa7e0df85fe327fc33e7ecc7f0b352f851f163e3cf8a3e33f8575df8c5e27f107c3cbed4f41d7f4dd2748b1d23c39e13f0dc896f67a4b5ae8e97f0ea6d6eb664de345049e547cff9e3f4a8ce317e38f00f14f0470d6070987abc3dc4994ca866d9ce6985cab018dc5e0b115a866186a75b175214a3529d1a95254949a7349c64e106a52f765c03c4b817e1cf15ac37f6c62b8af2acdf35c1f0a70c65b8fcef89b01c3f8dc1529e0b39cde1838ce11a188a94e9539d3a31a90c3d7aaa8c71188a90923fb86f014de678774fdc41650ab8f4f4c0edc1e981c0c8ed5de139e4743cfe75f901fb13ff00c1523e087c5af01fc47d3be3e3e87fb21fc5bf80ba7e9dabfc62f027c5af1b68569e1cb4f0ceb4e22d23c7ff000f7c6f7a74b83c6de08d5ee765a46d696c758d3afee2d2d26b7bdb6bdb0beb8fa1be0f7fc151bfe09eff001f7c7b65f0bbe127ed67f0a7c55f10756bbfb0e83e1696e75df0c5e789af7242d8785eefc5fa2683a6788b509d87fa258e957973777a31f638676210ff00cff719781be2ee4d9ff1450c57875c5d3a590d5957ccf30c06498ecd327c3e16a51faed2c6ace32ba38ccaaa60aa60e51c4d3c552c64f0f2a32538d568e69714642ab2c3d6ccb0f82c52ab3c354c16633fa866143154aa3a35b0b89c0e2953c561b1146ba951a94abd384e338b4d6d7fbe690b0046738cf3c76fff005e3debc27e3bfed35f00ff0065ef0bc3e34fda27e2f780be0df862eee26b5d3aff00c73aec1a65deb37901559ecb41d0e14bbf106bb776ece05cc3a3e977c6d0e05e1b72ea2be5cd2bfe0abfff0004f1f16fc30f8b3f167c1bfb517c3ef18f86fe0a783b51f1cf8e747d1ee2ff004bf1ec3a1e9fe5c30ff637823c5965e1dd7b5fbbd6354b8b2d0b498b4bb3bbb79755d42cd6e67b6b7324e9f2192785be24f1265d4339e1fe03e31ce729c4e36865f87cd32de1acdf199656c6e22bd3c352a11cc6861a7804e588a90c3ca52c4c553ad250a8e9c878ce24c83035aa61b179c65d87c453a6ea4f0f53194215e31e4734dd394d49371527156e66a2ec9b4d1f157fc1c25f0de7d47f645f853fb42e9baa78afc3f7dfb2a7ed1be02f1b6bbe23f036b371e1ff0018e89f0dfc7f237803c6d7fe1dd5ec5e3d42cb548ae2f7c3125b4d6afe6c0e4c91c33a3ca57f1d7f64dfda3bf6cef87df11ef3c63f06fe0d7edcff00b587ec2536897f75aa69df184685ac7c50d16e985d3e9be2ef835ae7882e53c47a9e96224b39cf87b50f3935cb092eed8fd9af0d9ea507d09f1ebc63ff00050fff0082bef822d3c2b3e9df07ff00659fd9c359f13f857e2a7c3af83dace8b79f107e28f8ef4ff0c4d73ab7832ffe2ceba6ead2d4586a115d43acdc78474eb4b2b29a596067d12686dedee4fadfec83fb587c79f02fc67d7bf66ef8fbe1fb1f01fc62f06dad8788eeb4ff000bb4e7e1efc58f86f737b1e8d2f8d3c176b7f1bdee953e8f708963aae812c8c34d5f262b486d05acf6907fb7bf461adc7bf47ef07328e19cd330c8b39cf387339cf7179b64994e775b1d84e1cc9336c465f8a8e1b110a10a583cdb1b84cc2a6675f1f5f2ec557a380a75e0a52af09cea50df86bc2ec6f136758ec6677473ae037c45fd8b98f05e3b1b96d1c2e333acce860b172ab829e32afd72ae5184cd301463f53c0e6b96d08e75530bcb4aad097b0557f473e007ed05f0bbf697f869a4fc56f83faf4bae7852fefaf745beb3d4ac27d1bc51e11f12e94c89adf83fc65e1fb967bcd03c4fa24b2c62f6c27f32096de6b5bfb0bbbed3ef2deee5f738c61179278ce4f5e79afca6fd8e757f0b6bbff050aff82a15c7c2cd5745b8f868b75fb3f5df88f46d02fac2e2c47c666d0f5cff0084eb5bb0d32c666d86c6dd24d17c5dac5bc06de7f1246d6f717525cc4f1afeacc61b6e70b8c81d7a123d08c8e9dc0c639aff0054b867379e7fc3f94673530b3c155cc7034315570b3e66e84eac549c2f2d6daab3d2eb5b2bd8f130b56ad4856a55ab50c4d6c0661996515713848f261b17532cc7d7c13c4d1a6a53f64abfb1e6f63ed27c8ef18ce714a6e611b060dbfa76dbd8f51d7b8e2a600b12338e01071ee47f4a41d07f4e9f85488a7ef718e57df8c1e9e9cd7b6ddade6ec746ab75623e9c6777be319fc29ac71d738f638ff0ab0c320e00cf6fafd6a1db27755c0c1c96f43df8e07627269950b39c6fb5fe5b69d575f97720cb282846472c0e790325b9cf5e87078fe955dce1b1c92559bf007fc2ac372ef8232547d39ca900f6201071ee3d7354a473bd97fd865c8e7919ce303dfd7a75a027a4e56496bd3629cacac91900e791f80fcfdb9fc3e95ad78d42cbfebeedc6071ff2da323d719c8fa50e4a9fa1031db955248edd734cb66ff4fb1e381796ec48e48fdfc7d4fb01deb7a11bcd37b5f6fbbfafbc87b7cd7e68f03ba1878c01c0b4b0ff00d22b7a41d07d07f2a7dd026e215c7cbf62b02c7b8ff41b6feb4c5fe1078c9c7f3c7f4ff0afade4b45df7defa9e4dfde6ddedff0005ff00c0b93aae3923241f7e9e9daaf204e4a823206739e9ce3b9e9cd5540154f5fbc3f422ac41f707d17fad6336952a9776d17e654377e9faa2c37dc53dc3000f7e4d5b018672792037fdf433cff5aa8dfeabdc118faeec55d1d8ff00b283f21cd73cbe17f2fcd1b3ff002fc8913ef71fdde7dce47ff5ff003ab7100442b8c0e323279c32fe5f81aa91637f3900e01fa67b707d7fc6ae46bf7141233b973ed9519fc8ff008545a2926dbd57f5d0b8697f48bdba357febfe096074c7fb44fe44e2a7424af3d467f2193fcbf5a857a36e2723383d338efd3f2e9e82be76fda57f68dd13f673f0468fa8c7e1fd47e237c52f883ae5b780fe067c15f0dca0f8c3e2e7c49d55d60d3742d32145967b2d034e9e586f3c5de2168becfa2e9bb551db50bbb481bcec6e230d81a188c7e3abd2c360b0d4675ebe22b54852a3429d38f34ea55a951c610846316e529492495dbb276c7198ca181c2d6c5e226a1468c79a5277d5b7cb084524e529d49b8d38423194a53928a8b7a1a7fb44fed3ff0008bf65ef08e97e29f8a5abea86fbc49a97f607813c01e13d226f13fc49f897e2591a3860f0ff00817c1f66e97dabdd196e2dd2e2fe47b5d22c8c91c5737f15d4d0412f8e691e1dff0082a57c75b64f1669117c06ff00827dfc3bd421fb4f85f42f8b3e1ebcf8f7fb476a368ec4dadf78afc2d60f65e0cf0549776fe4c9268377241aa5a33cb05dc0fe5b79bf40fecbbfb195dfc1bf13cdfb54fed4de20d2be32fedc9e2ed31609bc49691eef87dfb3ce81748cd17c28fd9ff479f7db68fa6787a29a6d3f59f1dc717f6d788aeffb4a5b1bab6b5bbb9bad4ff447c23f0fb54f174dfda9aecf3dbd8b3178f26457bacfce5a304656239fbfc93b881802bfc72fa467ed1bcc70199e3f25f06b15976599065988961ab71ce6183863b179c62b0f39466b86f2ec5c7d94706ab439238fc6c6b2c6c54dd3c361a94615ebe780c8b31cf631c6e715f1b94e5f517361728c1d59e17192a4d2b56cc71949fb5a55aa2d6584c2d451a11f72756aca7eeff283f153fe0929fb7af847f67bf1b7c18f847f1dbe067c72f056a7a86a9af2f83355f0aeabf0a7e20dd8d4b5f8bc59ae787bc1fa93dd5f7846da2d635382436fa36aba95ada42b7d756b6735a2cbb8e8fc14f14c3f14f4fbfd1ac3c2badf827c61f0daf6dbc17f127e17f8974e9b49f167c30f1169768b68da16b7a618d1e1b168acde4d0f56555b4d42da2510fd9e6b77887f649a7780fc2da6a05b7d26da5f282e5e75f35ddb1867f9c1c1c03d0f5e463ad7e677edafff00049df833fb64fc71f839f1aef7c41e28f861ab7866df59f0a7c737f865e22d57c07aff00c73f85e340be1e09f0dea7aee890bb2eade0cf170d24dbdfea76f24d7be0e9f53d13ed8935868ad07f2df027d38b2ce2ec762b87bc5970ca70b5a399e7184e3acb326c3d0ad1ce29e16388ab4336c9b2d953c36652ce6182586c2e2e1470788c2e3ead084ead6c3622b4f0bfab70271c67fe12e6d4333ca28e3b8b72679560f87311c399b66b3957c1e5582ab8aad963c8f37c5d3c66230143018dc756ad89c054f6986c4e1aa5550542bd2a15a1fcc9fc47f05fc29f8fbfb6f9f0c78c6c3c29e35d2ff672f817e1a30e893cb0ea36f79e33f17f8b1ae6e63f14adbc83fb4ed7c236da942e341bd90dbc377751adec0d0bcf6b27e837c46fd9afe127c4cf86f7be02f15f873438ec1b4ece85ae69ba4e93a36afe07d620843e91e23f08eaba7595a5df87aff46b98edee51ac248a092de196dee6131c9e52fd09f1b3fe08a9a3786edfc15e21fd85f49d03f67df8c3f0dadb59d1e1d575a875bf19f81fe37786bc452c2fa9787fe335f5edcea9e27bed464ba863bcd23c58826974b6f3217b0863874f9b4ef32d2ffe09d7ff00055af8c1a75dfc33f88977fb37fecc5e07d46d27d1bc5bf153c29e3af117c5af1d5ee83771883544f86be12820b6b5d2755d46cdae2dadb52f126a1a74fa6adc335b4b14c0491feff3f1c3c2ee21cbb85f37c8fc61ca785f27e1cc361b0f5301c4b99c326cf72f960a7275b1f86c8f0d5b1eb32966693c561f0d94acc314d622382a94e9b8c91f77c33e2af0461725f11e3c7bc00f19c59c639c6799a55a795647473dc1f12e0b38a2e195e532ceeae130cf052caf0ee1975579851c0524e9fd728d4a952a4a07e627ec53e22f117ed85e31b8f8ff00fb5af89e1fda47e2afc36d7b4bf811e05d47c742d757d1bc25e01f85be1bd3a1b5b9d1b4668a5d20f88fc657520d4f57f145c58cfac6ab7bf6ed624bc9b59d56f2f6beddff0082a2fecddfb3a7c6ff00d88be26fc66d3fe1e784be1a7c62f8096165e24d075dd2aded6cc6b36905c5ac7a8787750ba861826d46c354b179a3b5b5d41ae043a8c760f6de434d2f9deb9f107fe08d7f17be026bba578abfe09e975e1dd0618fc2fe19f0cfc43f823f176f7541e14f895adf8534e8f4bb4f89da178ef4d59eebc21f10b5ab5f3ee3c50b3c10687a85eddc92c33d941349a5d7a0780bfe097dfb6cfed313f84fc3dfb706b3f07be0a7ecdfa4f8a740f1378f3e0d7c20f13eadf123e257c6d93c377897fa7784bc57e2f2b17867c1de0cbebfb7b73abb6932dfea973668f0c7626e56dae2dbd7c47d207c22c1e6780f10b26f14b85f26e0fcaa8d2a94385a15a597f10e5f86c2469ba99165dc1f8550c4ce78eab0ab1c3c72da33cb2b7d61e23198849d69afcff039d70160bc089f87d98f06e650e3bf619861d3c06570c5e5f9de658ead89961b3ac671273cf092861a957c2b9d5cc234b32cbaae02186c161654a8539623e78fd9aff6b9f83bf13bc07e07f17d8fc45f0a7837c4da068de1a7f1b784fc41afe9de0ef11f8075cd2ec6ca2d5adafb4af105e69b7d0d979f6b3cba4de5b47269fa8584b0bc531fdeaa751e09f82be05ff82c47edf3aefc45d2fc51f12f41fd987f669f855ab782bc55f16be0b78a6e7c0971f14be2c78f357b764f851e1cf1edbd95d1d43c37a7f8760bbd57c6f75a12cebd2de3b98a2d52d6eaebfa07f8c1fb0afec61fb427892cbc61f1aff658f819f11fc57a6c70416be20f11fc3fd21b57fb35a4305b5a58df6a36096179ac58595b5bdbda5ad96b536a76b0db5bc7146a5012df52fc30f03fc39f0441e1ff0002f877c27e1ff04f8134983fb3b48f0a782b42d23c2de1ed2217c6cfecad1b48b5b2d2ed65560b264db059e544172d26f6c7f25637e985c3b8fc2e2f2cf0a726e27e12e37e329d5ca3059cf1266794cb27e0a9e715961f159860b1582a589c566d8afa955c450c1d7c6607031c055c446bc96327461cd8f1cf881c69c73c2d94e45c5196f0fd0c264984c12cc732c8bebeb36cfe9655f56c4e168430f5d51a194c6ae2f0585ad8af635f1952afb254a8d4c352a9384be02d6bfe0959fb1beb7f08fc01f0afe07f83e1fd8f7e2afc1eb7bf93e007ed3ff04629a2f8a9e0df11ea339babf3f13b51d4eeae2e3e3c784bc55a91ff008af7c21f12aef55b5d6ad26b81a54da0ce968f17cdbf047e267c489bc63f143f66dfda57c3fa37817f6b4fd9da6d1ed7e25e89e1b79cf823e28f823c41197f01fed0ff000864b9559ae7e1d7c4ab5899aeb4973f6df0478a20b9f0eea0900f26d93eedf823f1ab5fd57e217c56fd993e3545a4681fb4efc0096df50f1358e916cfa5f877e2f7c1df12ea13bfc2cfda4be15d95ccd2cd27833c5ba6f91a278d346b79ef25f871f13b4ff107846f9d6cbfb1a5b9f987fe0ad9a745f0dd3f64dff829268d12daea7fb3a7c4dd1ff671fda42ea1013fe124fd94bf68dd6a1f0fdfcbaeceb1c9f69b4f867f14e4f0d78c3479256416175ab5fc91c91ab3ad7f557d0afe913e28f0c78a79a7d1ff00c6ece7139be635ab47159366f8fc5bc64f1f4f1aa389c162f058e697f6865b98529d2af82c5a4beb34710e768d5855a51fc0a5532dc8bfb3b8bf86b9a96438caf0c0e79965e4a9e1f9eac28fd61d094e51c363307524bdbab2e6a7eebbdd4cf4c52090074c8e339e391ce3a1c8ef83819ab20606074ce7f97f855628b15c4b0eedc639360618292043b44b191f79260a2647c05915d658bf76e95614861b94e406233efb7047e5faf7aff64973e9b5b4f5e9f8ff00c1bd9e8bf4b9bbcafbdd45decd5ef15aebdf7eddb4b0bc9e2a3675d9275e8cbf52319c7b524603679e6272abee0f0739c76ee3039e955d89f28003aeefd7a7f2aa2089ce0e0024ed1c2f5048183d47bff85517e18020eff5ef863f30ce73c8c035725ce723b2039e3b0e7ffaf59d317c176233f2e07071bb93900fb71cfad026d2dca13381b7839392781c9000a82d9985dda95273f6cb61c601c1963c81c704e4d3a6ec3fbbc0fc40ebf954368e4df5a0e368bdb620fbf991823df1edf5e95d387f897ae9f87fc1d7e444a5aae8af1fcd795cf10b9389a36ebfe8560bff009256c6980e4a1ff687f2345e3113443b1b4b0edcff00c795bd22f453d31d33f977f6e9dc57d47bdbebe7b9e63ddfabfccba0e50e3d7fc2ad463f76a7b9ce4fe58fcb3546219473e98c7e240abf1ffab4fc7fa5635ff853f4fd5174faf7dbf5fbb6f9961465402323d08cf7ab20f03e83f95565fb9ef838fae6ad0c607cbbbd0e71b7a60fa9ee7f0158bf83e4bf4345baff00147ff4a44b12312483c118c7a104f3f53566224e33d9b1cf6e40ebd871cfff005aa1838ddee14fe1cd4910761b514bb1720003a92c140f5cb121540192c4281920186db4b4bf2f6edfa6c6adde738db78c2de6dc568bef390f899f12bc17f07fe1ef8d3e29fc45d5d340f027c3fd02f7c4be29d5d82c925b69966a88b0d95a921afb55d4ef66b7d2b47d363fdfea1a9dddadb44a44dbc7ce3ff04eef849e2ff8b1e26bcff82a07ed2fa14fa2fc42f897e1ebaf0bfec8bf09752412da7c07fd9e2f9a47b2f12416932b2c5f11fe2b4535c6a175ad2c497116857f757d08b74f10dadad87cf5e23f0e49ff00052bfdb5edff00661822fb77ec65fb18ebba27c41fdac754b6bc74d33e307c688cdcbf827e06417d6cc6daeb4bd12eedeea3f10c092c840b3f18cf335b5c5968320fdedb5b197c65afc1a6594315ae9365f67b6b686cd047676569631c76b0c56f0a0d905a416b1c56b691a90905a430c31aaa22ad7f91ff00b44be927fd9d46af81bc2d99fd5a55b0d1c7f88d98e12779e1f28ab16f0dc3d4eb734e10c4e6d18fb6c6c230bc72d51835cb983679791e123c479bcf32affbcc8f21c54a865f4dbb4330cde9371ad8f946ef9f0d97493a1856d38d4c4cabd5ba54a937b9e06f0ade78c3536d6b5949869f6eff002236099c8236a2eee1635230e06edc32a4e0d7d490dbac112451aa471a00a888a15555721400a30001c607159ba2691168b656f656f1a2c50a05dcbf7cb6003ea36e075cee35b75fe0571367f573bc64a51fdd60683f6783c3c6f1853a5193f7b91b7cb2a8df33bb72bbbb949b937fa4ca4df74aef4eedbbb6fcdee1fcfd693038e0707238ef4c3275039c641e4707e9dfdbd4f18a50e3807393edec4e3ea3a63ae6be693dede8ecd3b5d2693b3eab6bf6645d3f3ff836ff00817fc476d078c7a74e3a74e940500e40e69be6263bf7ee17a75ebfd71e9490b4b70d882de7940e498a1966e32719f2e320642b1e7b727008342f7ad05efaf85417bcacdaba495d72b72d7ecb6ddef7626e31bb7cb1496b7b2d3e761f81e83af5c527ca3d077f4f4ff015e77e32f8c3f083e1da16f889f16fe15fc3e1b7711e39f893e09f09385e72c63f106b9a74b8e0f0232d80781d2be4bf157fc152ff00e09b9e0fb87b3d6ff6e1fd9c0dec270f61e1ef1fdbf8e2f830192896be09b6f113b4a0027626e6dbf31181c7d964be1e7881c47cab87b8238c33c8b8f34259470e67198d3706e29b8cf0b84ab0716dc536a56bb5ccd6879389cf721c17fbde6d96e19a7ca954c661e0d3d3ddb3a8acf6b276d6ddee7df00a8e98fd2a37dfb9658a4dae8e1811c904639fcc71ff00d6c9fcc31ff0584fd83b51f3a3f04f8e7e327c539e22c820f859fb2d7ed17e3337039daf04b67f0ea1b49e095b0229926dafc63208ab10ff00c14d743d5639e6f01fec2fff00053ef889146e8166d1ff0062af1b689049bd0b029278cf54d03e42b865f37ca63b940553c57e8b97fd1a3e90988953ad85f08f8ee84b997b1ab8cc9abe57c93ba74e6e7997d5e319424a33e694b962d5ded75e1d7f10f82e8732abc47953b45c5c69e2e355eaacd4a34154e9b6addd3d34d7eaefdb27f661f16fed27e1bf86bf1dbf674d7347f05fedcffb29dd6b1e20f80daf6b329b5f0bfc4fd0357853fe13bfd9abe2d48b3dbb5efc2ef8c1696e2ce3b99a60fe0ef17ff65f8a6c9e153aab49e61e0af127c3bff82a4fec37fb45fc188b48d57c1badfc56f877f123e00fc58f84fe305fb278f3f67ffda5346d25dffe15f78daceea0592cb5bf08f8fed744d6b40d5ded96dbc4da0c3a7f88b4ccc735c471f9de81fb7f7ed6106a50df782bfe08ebff000510d52ca4ca8b9f16c7f04be1c8bab4da417974fd7bc73766d256c0ff0045ba9a3947ca19949da7e56f8efe37ff0082855c7ed29e16fdb77f635ff8249fed81f0d3e3cdfaf85fc09fb52f80bc5fe34fd9cf56f845fb52fc20f0eaddcfa2ddf8a97c23f11ee3c45e19f8e7f0ecb4169f0e7e2868ba3ddea0ba3cf71a0788e3d5f4d86dec25ff0049fc36f0b3c55e2dca381f36e35e1bff00557c5bf0bb154aaf08e7d89cef87eb7f6de554672ab89e17cd6781ccb1d8a8e033084aa57c0d6ac94f29cea55a552f97e618a8e1bf09cef8ab867078ccd70b9366b4b1f90f10d3f6599e0942affb362a5cbcb98e1d4e8c539d3928fb450e6f6906ddf9e3145bfd81fe2feb7f1aff00645f827e35f174735bf8f34cf0d4bf0bfe265adca14bdb0f89bf07efe7f86de32b1be8387b7bc92f7c3d0ea734727217538e40cf1b231fb062cec6e47fad6f7ec3dc75fa715f923ff04fefda33e1bfc45fdacbfe0a39f0cfe1d697e33f06e91ab7c5fd27f6a7b1f853f133c35a8782be227c28f16fc59d26cb48fda23e16f89bc2da9c48d69ac7807e2f58c569a8dd6926eb46bf8752b6d4b4fb8315e7eeff005b60398892003bc838e84e32d8cf3df9aff6c387731ad9ae4794e6188c356c26231780c356c4e1711caabe1b132a50788c3d6507282a94aab9424a339c6ebdd94a3693fd8783b318e73c399663255d55a91a1f53af5959aab5f056c3ceaabbba8d554d548a6f992935349ef21039f767cfa1c3a0e7f026ab48c177f00904617f16cf1f97e1d3bd593f7875cb17c7b8055f9f4231fe07ad67ccdf2bb8fbe4aaf4f9792473c63b8ea00ebeb5ed1f42ddbef5f8bb114b26060320dc08c92030fa707f976acc9a4f973f291b403c824ed246e1efc1c77e9deadc856403aee07078c004003807df233deb32e1882cbd82e3a73c127ad35baf540d2bf476d994e6963c9f98751f5e9e9ffd6ef54616ff008985a28e86f6d88c76fdea71fafafd318c549374e4004f0ad8e738ee7d3eb542d9cfdb6d73f291776c0739f98ce99c6df6fcbaf6af470a939aba5bdb6f439e72bbe89de3a75dd1c88d06ce792c1de4b9066d3b4a9182bc40067d36d1c85cc24800b1c64920639cf353ff00c23b6419479b758dd8ff0059172307afee39a28afa74972bd175e9e71396caeb45b3e9e68b43c3b6411b125d0e7b3c3edff4c2a787c3d65b47ef6eba29fbf1771cff00cb0a28ae5acbf732d3ac7a7944d2095f65b76f345a6f0ed9083709aec1cf6922fef7fd70abc7c396214112dd738fe387d3feb851456325eead3ac7a7f80d6caeb45f147a7f7912c7e1eb3c0fdf5df43ff2d22ed9ff00a61ed5f30fedb3e31f10fc0ffd917f692f8b3e00be6b1f1afc3ff847afebde14d46e944f1e97addd7d8b4783558e18fece5af34b1ab4ba8e98cd27970ea76b653cd1dc430bdbca515cd896e383c54a378c9616ac94a3a4949536d34d6a9a7aa6b5471674dc32ccd2706e13865d8b94271f7651947093719464ace328b49a69a69abad4f51ff825afc01f87bf07ff00e09bdfb35ddf832db518758f8d1e01d23e37fc4ef10ea1770df6bfe30f88df11edcea7aeeb7abea6f68935d35ac105a68ba3a49b9ac747b48a0692e2ee5bcbdbafd5bf851e09d1edac0dda35d99e5de8eed2404953f31036db2e013efec28a2bfe48fe9258ec6e238c3c58c457c5e2abe22af895c4b46ad7ad5ead4ad52961f33cc68d0a552ace729ce9d0a30852a30949c69d28c69c14631497bdc2d4e14b8272074a10a6de5397b6e115077961e1293bc52d65294a4df56db7ab67b20d06d3e51e65cfddfefc5edff4c7de9b268369b0e25b91efbe2edcff00cf1ef8c1f6cd1457f21ca73e57ef4bff00027dfd4ec552a5d7bf3dd7da7fe67c91ff000502f1f789bf67ff0082ff0000f5cf86b79169de22f8cbfb577c08f825aeebf7f696da9dfe81e11f883afdc27892efc2b05cc6749b4d7e7b2b0fecdb5bfd674dd72daced6eeea586c06a02d2fad7ea7d3f42b596c6099e6bb2c6de49981923219d6369324b445fe6c056c303b47ca55be6a28afe9cf18f27ca72df0cfc10c665d95e5d80c5e6583e2296638ac16070d85c46612a38bca634658daf42942ae29d28d4a91a6ebcaa3829cd46ca52bfcce478ac4d5af9e2ab88af5553c7c214d54ab526a9c7d9d7f761cd27cb1d1691b2d169a1fca17edabff056bfdaff00c35fb69e93fb217c34d57e1ffc29f056b3ab5a69373e3df08f81ed758f8a70c1717314124f65ab7c4abdf1f7846ceec2485a39a1f05288a40af1a291cfeeff00c2bff8242fc33fda1fc0ba678dbf692fdb03fe0a19f1e06a7671bdf7837c53fb50ddf813c0170b716b0cd24327853e05f843e125934049d8d1162b2c5f24fe68272515feb87d1eb82382f2df05b87f8832fe10e17c067d570183a9533bc16419561737a951e1a33739e6543090c6ce6e7273729566dc9b93776d9fcafe22e739bcb37c4d0966b994a8c2a49468cb1d8a74a2adb469babc8b65b25b1ed5e0bff82397fc12f7e1d5d89341fd887e026a9a8a5c89db5df1ff008423f8adafcd711ed88dc4dac7c509fc617d24b26d12480cbe59973208d599b3f5ff0084bf666f803f0fec85af807e0dfc28f02dac4ec61b7f06fc2df871e188a1666c3b44344f0a59346cf9cb95605cf2d9a28af6f1d9be6b88a0fdbe679857b4e505edb1989a9687b69479173d495a3ca947976e5495ac8fcb5b728b949b94b9afcd2d657d35bbd6e7a9daf86ade15c41a8eaf6c146c516976968155495002db4112e3006010547600000365f0c5b4a844da96b339ddb774f7ab2372d927734192c49e49cd145790a739c61cf294fddfb5272fb0fbb67453492d125abd977b148f8334bdc5bcfbf2cce033196d8b1e33cb1b5dc482490c4eef52454b69e18b2b0b986eadae2fa39525539f32dcabae416471f66f991c7caebdc7a100828aeec04a50ab42506e0fdacb58b717bf7566673fb5f3fd4fc0aff8380be09fc3efd97c7ecf7ff0545f82ba4ffc21bfb582fed03f0abf66bf883af580b587c2bf1c7e0ffc5017ba76afe1df8cfe1eb6b5b79bc5d7da2d8e8f696be19f12da6a9a2f8934f863b68eef55d45749d08695f438f0be9c93346925daa179881e64476ec5501549809c7cd93b8b124039ce72515fe89f85b56a55e1ac33ab52751fee95ea4a537f0db7937d125f247f4e781756a54ca73ca53a939d2a589c1ce9539ca52a74e75282f692a706dc612a9cb1e7945273b2e66ec869f0d58ef8ff007d77f7987fac87ba9cff00cb0ef59f2f86ac791e6dde377f7e1ed9c7fcb0a28afd2d2db4edd3fc1fe6fef3f73b2ecbee33bfe119b15593135e7127532444f53dfc8ac8b9f0dd96f7fdf5e74ff9e90fff0018a28a174d3b7fed9fe6c2cbb2fb8ca9fc3763f27efaf3bffcb487fd9ffa615422f0dd8fdbed079d778fb55b1ff590f7b98c7fcf0a28af430bfc45f2fca27134b9f65bae9e87ffd9','Female','1970-01-01 00:00:00',NULL,0,'mu.hcapp.mobile@gmail.com','EN','TESQ00000002','3','eyJhbGciOiJSUzI1NiIsImtpZCI6IjZlZjdlYjVkYWVjYzBmOTQ0MDg2MzMwZTc5ZmI2MjIzMmUwZGJhNzcifQ.eyJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXVkIjoiZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXV0aF90aW1lIjoxNDgxMTQwMzUzLCJ1c2VyX2lkIjoiODJmMjZiNTYtZmRlNC00NDdhLTkxYzQtOGM1YWE1YjA1NDQ1Iiwic3ViIjoiODJmMjZiNTYtZmRlNC00NDdhLTkxYzQtOGM1YWE1YjA1NDQ1IiwiaWF0IjoxNDgxMTQwMzUzLCJleHAiOjE0ODExNDM5NTMsImVtYWlsIjoibXUuaGNhcHAubW9iaWxlQGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwiZmlyZWJhc2UiOnsiaWRlbnRpdGllcyI6eyJlbWFpbCI6WyJtdS5oY2FwcC5tb2JpbGVAZ21haWwuY29tIl19LCJzaWduX2luX3Byb3ZpZGVyIjoicGFzc3dvcmQifX0.Dmubyc6WBNv6BsIhYi6vctLBBiOQiMms2Oh7VHcx8ymgRQ8bsn4ru4nKtZ3NESrReywJ1V7v9opk-ZkeQmLXWnIgAJ8GPEsdqS1O8mm38_GuCDxzPs-HWpT0Hx_w29SHx9UM82qZ8VEkKQQ2JJKEvQOOSxLOaNwQUo-qs9xRWdBehD1FEgbMLu04oOHSH9wi3YaqjLxiJgx59BmpXjVl8l3_Sej8a4yws_GDDYYCviOV4bS_2j4vDFEXpq86KVMwHCYyVEH_RROIlQKbU1nrX4eKcbJMLeG65CxhAbl0nPAajOLSsSUjr3hnVQoWny_ZW4lnX0Rg_FT8OODmaBN2RA','2016-12-14 18:50:02'),(112,49110,'Opal3','','Test3','QA_Opal',NULL,'ffd8ffe000104a46494600010101006000600000ffdb00430001010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffdb00430101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffc0001108014000d603012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00fefd80181c0e83b52e07a0fca81d07d07f2a5a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f9514b450020e83e83f952d20e83e83f952d001451450014514500145145001451450014514500145145001451450014514500145145001451450014514500145145001451450020e83e83f952d20e83e83f952d00145145001451450014514500145145001466bf343c61ff000556fd983e12fedc3ae7ec21f1e753d5be087c4397c33f0cbc59f0bfe22f8f208acfe0c7c56b2f8a6badd8681e1ed3be21a37f637837c77378abc29e2ef0ce95e15f1d4ba1b78baf34229e0dd475dd4ae5f47b4fc98f107ed45fb6ff00867f6f2fdbfbf691f805e34f107c60f833f057f689f067ecefaafec2fe23bcb33e18f8a1e0df853fb3f7c24d67e2c789fe03f88b50647f86dfb45d878dfc6fe206d02da4ba8fc05f14069a3c21e32b4b2d5ce81e24d2be3f8d38fb84bc3dcaa8679c639d61723ca2be779570ff00f68e29cbeab87ccf39ad0a180a58cab08ce383a3395484ebe2b11ecf0d84c3b78ac555a3868ceac7d2cb328ccb39c44f0996612ae2f130c2d7c63a3492752587c32bd69538b69d49455d4610e69ce49c61194b43fa05f87bfb5cfeceff0014be3f7c74fd96fc15f13f40d5be3ffece1ff0884df173e16b492d9f8a3c3da4f8e7c2fa0f8b3c39e22b4b4bc8e15f107872e34ff1269767a86b3a0c9a859e87adce9a2eb8fa76a13d9c375f197c47ff0082b67c11f84fff0005008ff621f1c7863c49a6786e0d23e157877c4ffb4c2cd049f09fe1dfed17f1c1f5fd5be117ecf3f1119add26f0c6bbf113c15a3d8ebbe1df15dcdeb68435cf14783bc23a92d96a1e2ad22e25fe7cbf679f0c789ff6abf0dfc79ff828c7ecf52ea9f0fbf6a6d6bf6dbfda6bf69cfd94fc6fe31d26e7c39e226f0f695e2e6f84fa17ecf1f1874d5923d45fe187c5bf849f09742f869f14bc0d7d733c3e1ed4ee6cfc4ba608bc43e14d36f6beadfd98ff00664d4fe287ece5f18ef7f6d1f01d9defc45fdbcfc41f103e337ed3ff000fb509a591f43d67e30df47a869df0f2dafc4d25e69d37c16f0569de00f87de0ad42d6e1350f0cddfc3fd3b55d367b6d46de2957f90bc53fa79786be1ee078aa961b0f2cd78a782fc56c3787d9cf0bbc5528e371594d1af3af9af1465338350af84fecdc26370d8355bd9468e7b1a585c63583951c562bf49c93c23ceb35ab84756aaa182cc386a19e61318a9cb9162b134a8bc365b5e32d5558cebc675dc5dfeaf1728daa3708fd67fb587fc1533e3ffc18fdbbfe227877e10fc3bd3fe2efec81fb16fc3cf87d17edc7e1ad16c26bbf8c93f89fe31c1278eafbc55f040dac530f12ebbfb34fc24b7f06fc40f1b7c3b92e6de4f1b785fe236b9a469917fc25da3787e487cc3f6bcff828efed1dfb437ed03e0ef08ffc12abe25f807c4de05fd9a3c13e10fda23e2578b2e05b6bbf0e7f6b3f1efc47f09db78c3e177ec71a778955e35d0342f12fc14d5b53f1c78d7c75a7cd35d7833c5fe35f82a5a2b6b9d2f5a8168fec47fb29ebdfb2af80bc5ba678efe2cf887e3b7c57f1efc4ff001efc44f1e7c67f166e6f1778eaeb5cd5a1d3bc29a878a6e6425efbc49a67c39f0f781fc3bacde716d71a8689732d9c6b692c40fa67c0afd96be07fecd967e24d33e0bf826d3c11a3f8a7c7de37f8957fa269b215d22d3c53f10afe1d47c492e8f6411134cd31e5b7b6b6d2b4a80fd8f45d36dadb4bd3920b1b68208ff973c41fda6788c3627c54c9f81f2ec3e263473dca23e13f14bcb9bc3cb26c2c70d4f3d967d95e613a156b3cc2be0ea6232d72a74f130c3e718cc3d754aa60b04dfdee4fe07e165fd8389cd6b558a796e2259fe02353de599d452784586ad4dfbb4f0eaac615f9652854ab84838270af54f32fdab7fe0a5ff127f6e5f077ec9df03ffe0991f15aff00e0c7c5df8dde09d5bf69bf8b3e3ed734a59fc47fb3b68df0975bbff0bf853f67af899e1d95c2699e36f891fb5268137c23f891a0dcccc91fc3cf86df1923306a5a6dfd8bddfa7fc4eff82c17c4af8aff00b28fecada37ec5be0cf084bff050bfdb13c13afdfd9fc34f88925fc9f0ff00f65bd43e11f892e3e1f7ed1de2af8d3f66126a4ba1f813e31689ae7c14f0069770d6977e3cf1f5d58cd6e25d2342f127d9aa7c31fd953e09fc1cf8bff1d3e39fc3df0aa681e3ff00da2fc53a1f8d3e285edbc8a2cf51f13683a08d061d4f4eb4585469f26a5e65feb9ac2248e97fe26d675ad6dc2dd6a339ae4fe07fec67f0a3e047c70fda63e3ff0085219e4f1c7ed39f127fe1627891e68208adbc31bec52e2efc39e1bf2cb3c1a3ea3e38d4bc6bf112fa3cc4b37897c6faa4cd0936d6d2d74e33f69e61aac3c45c665dc3f5b0b5aaf0770747c36ca71d86fac50c3f184a9d48f18cf36c7519296230985c4e632ab8055a74296370bc3f868c69e12be655bda634bc0c4bfb0a956c62928e33349e7b88a724a75708a69e574f0d4e5a4653a74611c438ae6a33c55695e718d23ef3b4ff0082b47c16b6fd81fe127ed9faf7863c52fe35f8bf341f0cbc1ffb2e786a08f55f8d3e2cfdacacb55d63c15e2cfd97bc29a148d04d7be34f0bfc4af0c78b3c3fae6af7b159e8de1ff0df87354f1debf3e9de1bb2b9ba4fa5bfe09eff00b47f8abf6befd8abf66ffda7bc6de1ed13c25e29f8e3f0d74af887ab785bc373de5de8be1b975eb8bb9e0d0ac6f6fe596ef505d26d05bd84da8cac9fda37104d7d1c16d15c25bc5f8bdf0aff0061bf00780ff6c4f8adfb594be23d7fc47aaf8efc41e21f157c31f871aac84f803f67ef17fc4ff0df872c7f685f18fc32d1ccb25ae9be2ffda07c43e15d33c41e3ef11c36f6fa8c9144fa25b4a2cafb54fb77e8ff00fc112183ff00c1253fe09f85401ff18cff000f54ed181b92c1d1cfd4bab163dd8935fdebf47ffa4a70bfd21eb71556e10c162e8e53c2b97f072c56371b4ea61eae233ee20cb71b8fce72fa585ab18d4861b23ab428e591c4c9b58ec5d3c6d6a1cd83585af88fc8b8bf8271dc1b1cba398d6a73c46613cc251a549a9c69e1b0b5e1470f52551692a98984bdbca092f6519d384ad514e31fd4aa28a2bfa48f8a0a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a2800a28a2803f9e3ff82a57c29f073fedfdfb276bbe3cf09e83e35f86bfb60fecc9fb4b7ec67f143c25e2bd26d35af0a789756f86d75e1bfda73e0fe9face9b7d14b677929d3b47f8e7169c5d56eed26ba79f4f9a1ba0b2c7e53fb387ece5f0fbf659f87fab7c32f86173e2bb9f0b6a9f113c67f1215bc6be23bff17f88ad353f1acba73dd6972f8a7576975ed6f4ed1e0d2ecb4dd16efc417ba9eb91e996d6d6d7faa5fc96e2e1fee1ff0082dee98340fd977e0efed1f1aaacbfb20fed99fb2f7c72d4ae00513c3e05d6fe215bfc05f8a8049c3ada8f867f1a7c55777ea088dadec8c928db0ee5f23bfb63657d7966c726d6eae2df3ea2295d01fc4007e86bfc58fda4182ceb20f12f25af84cc31d4385bc43e17cab30cdb2a8d79acb31fc55c138cccb2859956c2dfd8cb1f84c8339c9b054f11caebac34fd8b9fb2e581fd4be05d5c1e3327c646ae1e84b31c9b1d5a9e1b14e9c5e26960733a546b4a8c2adb9d529e270f56728733839a525152526fbed0ad6cad74c805859da594570d35f4d1595b436b14b7d7d349757f7724702468f757b7b2cf777970ea66baba9a6b89de49a5776d7ac6d01b3a5c0339d8644fa6d73fe3c7b75c56cd7f9318de7fade27da4a5397b6a979cdb94a5ef3d65293726ed6bb6dbeecfde924924928a492492b25e4ada69e4145145728c28a28a00d6d062fb46b9a45be01fb46a36b060e7fe5b4ab18e9eed8fa66ba5ff00821b4cb27fc125bf619b75393a4fc1a8bc3b20feecfe18f14788fc3d7087de39f4c910fba9ac2f0a305f14f86589c01e20d1f27d337f00cd5dff00821a6db7ff008264fc08d114927c1fe3afda97c0b206043237823f6b5f8e9e155560790de56951b60e0e08aff66ff651554b29f1929736af32e10928db76f0b9da6efd2ca3b5927dee7f3378fdae2f8734d561b1faebb3ab87bf9745e67eb6d14515febe9fcf014514500145145001451450020e83e83f952d20e83e83f952d00145145001451450014514500145145007c79ff0507f823ff0d23fb0c7ed79f02a2b6373a8fc52fd9cbe30784bc3eaa01787c57a8781f596f085e45c3113e9fe288b48beb72a372cf6f1b29560187e357ecedf1407c6efd9d7f67af8ce5b74ff0015fe067c28f1f5ff003968f59f11782345bcd7e093d25b5d71b51b5981f996585d58060457f4ae464630083c1046411dc11ee38afe527f61bd1cf817e0978c7e05c84f9ffb2c7ed53fb5d7ecceb19ce61d03e1f7c7bf196b1f0fd0024e206f86de2cf0849678f91acde068ff007656bfccff00da6dc30b1be1b7875c610a7cd578778df1791d59a5ad3c1f1564788c554949ff0027d738572fa6baf3d64afcae49fee9e03660e8710e6d96b95a18fcae3888c5b6b9aae07114d45456ce5ecf1555ebf65495f5b3fbebc2f206b39e31ff002cee09c73c07453fa90735d2d71be14930f7b17aac320183c90590f7ec08fc2bb2aff0a33687b3c7e216bef3854f9ce9c24ed6f3f9f73fab028a28cd79e937b26fd105c28a28a405fd2a6fb3eaba55c74f2353d3e6cfa795790be7ff001dad3ff822c486cff657f8c9e0e2573f0eff00e0a0bff0514f0788d0fcb044bfb627c5df11db4200000096be22b7c28c0dac08e0d614315c4f34515ac32dc5cbc8be4430a3492c9203b9551101627e5cf1d00272319a9ffe08f57d6d05a7fc1493c1bf6ab61a8783ff00e0aa1fb51cf77a4acf11bdd2adfe21685f0b3e28599bcb40e67b54bf97c677b716ef3468b72cb70622e63936ff00af7fb28f175639bf8c397ca954542ae5fc2b8da759d39aa52af86c466d87ad46355c7d9bab4e96370f5274e33e750ab09ca36945bfe73f1fa10f63c395538b9fb4cc29495fde5071c34e0f96f74a4d4ed26acda693d1a3f6328a28aff674fe6b0a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a2800a28a2800afe66acb485f87dff000506ff0082a7fc2c8d1adec359f8b3fb357ed4fe1fb671b03d97c78fd9eb43f0078aeeede31d639bc7ff0001fc432ceebc3dccced27ef09cff004cb5fcf7fed85a57fc21dff0576d1f51110b6b2fda1bfe09bba858ee00aaea3e25fd96ff0068eb6d4558f3892eadfc33fb474c036dde96d0a8ddb4807f92be9c9907f6ff00d18fc47e5873e23245c39c4786d1370fec8e28c9eb63aa2ba76e5c9e799464f4f7272bb4aecfd13c2ac6bc171de46ef68e2aa62705535b5e388c2d6e44fa5bdb4693b75695b5b33baf0cc98d4190ff001dbbe3ea8c8dfc89eb9e718aefabcdb41729ab5a6091bcc919fa3c6dfd4038ef835e3ffb587c43f89de1ef0d7c37f841fb3f4da75b7ed2dfb55fc55d03f67bf815aaead63fdaba4781358d7f4cd67c4be3ff008d5ae691ff00315d0be057c29f0d78d3e275ce98ead06afab687a0e8370af16b0637ff009e1c8f8233ef10f8f786781f8630f1c567fc5b9960327cb295494a95055f1559d396231557967ec30783a2aae2f1b5dc651c3e170f5aac95a9b3fb2b37cd30b926598dcd71d370c2e02854c457695e4e34e37e584779549bb4210de53925b3b8ff001ffed53a3e8ff1427fd9f7e09fc2ef8a5fb587ed29696361a9eb5f057e03697a3df4df0eb4ad5c03a3eb7f1cbe26f8b357f0e7c2af819a2eacacb2e99ff0b07c59a7f88b58b622e741f0ceaf0bc6cfd1c7e01ff82bddeda9d7adff00626fd90f49d3f8953c15af7edcfe2d7f882d1633e44baa683fb2e6abf0fa0d44a92ad1c7addf69e9302aba8cb11131fd40fd90ff00652f849fb2afc37d1fe09fc1cd2ef21f0ce81733ebde37f19ebb72756f889f1afe276aeeb73e2ef8b9f177c61328d4fc6bf10fc69ab89f55d6758d4e568ede36b7d1f46b5d3341d3b4dd2ed3edac00318e070076c0e95feeaf875fb393e8f1c2fc3f86c1f1a64f98f88fc473c3c56699de659e67d92e0de2a515eda394651c3f9a6594f07818ceeb0f1c755cc71e92e7a98d939f2c3f9333bf18f8c71d8c9d4cbf154b27c2a9de8e170f87c36226a9ef0fac57c551acea556be3f671a54f5e5e469267f35ba67ed577fe0cf88de18f82bfb5cfc08f89dfb18fc5bf1d6a3fd8df0e61f8a977e18f157c17f8bfaf794265f0e7c20fda33c01a8eabf0cbc51e2e9a1fdf5af803c4b73e06f88d7b163ec3e0fbb70c8bbbf14ff69887c27f122c7f67ef83bf0abe21fed3dfb506aba243e2687e04fc244d16d6e7c1de13bc9becb63e3cf8ddf123c597fa4fc3bf817e01bebadd0699ae78f359b7d5bc44f14c9e11f0d789258648d7f76fe39fc08f845fb4afc2af18fc12f8e7e02f0ffc49f85de3dd2e5d27c4fe12f11d98b8b2bc85b125b5ed9ce8d15f68daee9378906a9e1ff11e8f7561aef87b59b4b2d6345d42c752b3b6ba8bf1ebfe0949f072d3f663f1bfed8dfb02f89cdd6aff0016be0d7c5dd2fe35ea9f1e7c4ba96a1ae7c4efdae3e007ed0716b7a8fc0bf8b9f143c55acc936ade26f883f0e57c21e31fd9cfc55e54b1f8774e9be14e99a8f87f4cd2acfc44b6c7e3719fb30fc1ba9e226073fc267bc4d86f0f6387ab5b31e00fad3af8aad9a42ad1faa50c1f145493ccb0f9056a4ebbc761ebc3159cc6a538470b9d4238ae6c17a14bc70e26593d4c1d4a382966bcd18d2cd951b28d0e57ed1cf06ad4658ae65154ea45c28da5294e85e094f8dd2fe15ffc16467b78bc61a47c07ff00827b6950c6e2787c0337ed6df1deefc7902ec121b397c7ba37eccefe044d55558c0e6db4cb9d31670d8bb9603e657c53e3cfda0758f82dfb6bfec79f10be207eccdf137f621ff8282fc41fda9be04fecf7f1074b68f49f15fc21fdbc3f663f8bfe268be1bf8f34c87e32fc300df0bfe35788be08cfaff867e29f8564f1ed9784be367c3b8bc3ccb1787e1f0deabaa5b9feb82dada0b3822b6b78d638615558d00e800c027d58f52c792735e03fb527ecc3f0a3f6bef829e2ef817f18348b8bcf0cf89a2b7bdd275dd16e9f47f1a7c3cf1be8f28bff00067c51f86fe27b60351f07fc48f87faf4569e23f0778a34c923bcd3355b388b79f672dd5a5c7f58702fd18bc1ef0bb38c0e79e1ae4399f046370938fd669651c55c5189cb338c3b54e35f0d9c6519ce6f9a65b8cf6d4e9a8bc5c70d47318b8c5431aa973d19fe7f9af1af10e7b87a986ceb174733a737cd0962305838d7c3493e68cb0d5f0f428d5a493bde9f33a324f5a775171fa1871c7a515f9e5ff0004d0f8e9f153e2f7c00f10f817f685d46d35afda67f651f8bff103f64efda0fc496164ba5d878f7c71f0967d3cf873e2ed8696bb574db3f8dbf0a75ff877f17ffb3e1860b4d32f7c6d7ba55944b6ba7c607e86d7f401f2814514500145145001451450020e83e83f952d20e83e83f952d001451450014514500145145001451450015f865ff0557b07f0ff00ed87ff0004a6f896079569aafc43fdacff00673d52e3181227c5ff00d9c753f88ba359cafb86566d7be01d89850ab1f38029b4939fdcdafc69ff0082d858cd63f067f63cf89d0c6e47c1eff82947ec67e24bdb88e32cd6da4fc42f1bea5fb3fea8ceea32904f6bf195ada5dcc91b995158b12a87f2ff001bb25ff58fc19f167225173a99af86fc6d83a114aefeb5578733258492dbde8629519c5f49453e87b9c3389783e23c8314bfe5c673965495dd9382c651e78b7da50e68bee9b479569afe5ea164c780b73106c73805b69fd4fd6b81d1c433ff00c157ff006038b582a34dd3bf67afdbf35cf088909dadf12134bfd9eb472f00c15fb743f0f356f19885be5916cae753f2dbcb79d4f7f7113d9ea734320657b6d41d1c60ab298ae4ee0470548c723b578d7ed65e1ff89ba4dbfc17fda6fe03f87ae3c61f1cbf637f8b36bf1c7c21e00b1962b7d47e2efc3fbef0e6b7e00f8fdf0434cb99834316b9f137e0ef8a3c4b6de0cf391e06f88ba2782bcd5d818aff00cf5fd16f8db20f0ffe927e10716f11d7a184c8a9e698fca7198fc54a10c3e5d1e24c8b33e1cc26638aad371850c2e0f179ce1f138ac45492850c3d3ab55c9285cfecbf11f2dc5671c159e60f074e757132c3d1c4d3a30bcaa56583c4d0c64e9412f8e7569d1942097c72924aeda4ff0073bc517df12b48f819f16b5af829a0e85e2bf8cf61e05f1cea3f0afc33e28be6d33c37e23f89561e19d42e7c09a0f8875249ad9ac343d5fc4f1e9b61aade0b9b616d63733cbf68b709e6a7c49ff0477f89bff052ff008aff00b2a6a5e26ff82a7fc28f0cfc24fda062f8a7e2dd33c31a3e8561a4e89a8eb1f0bedad3459346d6bc4fe1bd0758d7747d13521e219bc4fa469c2d6f629754f0ee95a3eaf7364b2de1d4352faa7f669fda23e147c7df85fe0df8e7f057c576be35f835f14b4d4d63c37e20b4124575a6dd24b25aea9e1bf13e93304d43c31e32f0aea915df877c63e15d660b3d67c39e20d3750d2754b3b7bcb39625fac239639916489d244700aba3065607b861c1affa61859251edf95f47db5d1af53f872aa6a6deb696a9bf2d1af54f46b74d6c3ebf28b5adb6ff00f05c3f87474724dc6abff04aff008b8be3f8d59827d8341fdac7e0ebfc2d9ae40c2b4d1df788be2b45a6798599229f5930aa83393fa5de3ff1f7827e15f827c57f123e2478af40f02f807c0da06a9e28f1878cbc55aa5ae89e1bf0cf87b45b496fb55d675bd5afa586cf4fd3ac2d21927b8b9b89511117192c541fe6d7e05fc6aff82807c53fda47e3effc144fe0b7ecff00fb31eb9f0eff0069af0b7c39f85dfb36697fb4efed07f13be09fc55f017ecaff0006f50f18dff83b5ad6be1e7877e02fc458b497f8f7e33f1b78b3e379d3b51d66c3c4961e19d7fc11a2eb5a6db5d68fba4f0388f8bf85f846861711c519fe539051c6d6961f075334c6d1c22c557a74fdad4a7875564a55a54e9ae7a9ecd4bd9c5c5cedcd1bf460b2fc766339d3c0e12be2a74e2a55151a6e7c9193e54e6f68a6ee95dabd9daf667da7fb5efc18ff0082bcf8bffe0a6ffb177c4bfd967e3ff803c11ff04f7f0759e929fb4efc2fd68d843abf8865b7f136b775f1016ff48b8f0f6a1a9f8b6e3c65e059f40f0f7c3cbad335cd3e1f04f8974fbcd66ee2d3d1e5bed4ff0069abf0eef7e34ffc16135fdf0cbe30ff008255fc128650cad716da77ed53fb44eb362bbbe568207d4be006917338424ffa432c024500aba1e3c97e277edf9ff0524fd91b42d234af89fe06fd9a3f6ca7f8dfe2cf09fc05f801f19be07d8f8a3f678d1fe1bfed33f15b5ab5f0bfc26d1ff6aaf873f11be20fc448f4cf827e21d76ff127c53f86de39bed5ad6ff4cb6f06dd7819756f16687ab27c7e4de36f849c439fe1b85b23f10786334e21c6b9c70b9560f31a753155e708b94a9d38d94655396329461cdcd349f2293b23d0c4f0d71060f0b3c762b27c7e1f094b97da622ae1e71a50e66945ca76b59b695ef6be973ec4fd811935bfdb0ffe0b09e34d0b0de0ad43f6cef84fe0ab2b88486b3bbf1dfc34fd8aff00671f0ffc4c9a0653b5e7b3d6a6b1d0b5220663d4747b9b6918cd6f22a7ead57c8dfb0e7ecb30fec79fb38783fe0f5f78baebe24fc419f53f167c49f8dff16751b54b3d57e2f7c7bf8afe27d53e207c63f8997f6aa5cd9a78a7c75afeb171a2e92f34e3c3fe198b43f0ec33cb6da442e7eb9afd48f0c28a28a0028a28a0028a28a0041d07d07f2a5a41d07d07f2a5a0028a28a0028a28a0028a28a0028a28a002bf890ff828f7ed17e13f8ffe38fdb03e327ed7fe18fda9fe36fec89fb327ed15e3cfd9cbe0cfecb5fb37f887c61e0ff04e8b37ecc10f8666f8c7fb517c72d57c29e37f86fa76afe27ff85b97d75a6fc33bcf18f8b562f0cdaf877c3f69e00f0dea9e2dd4ef35287fb6fafe4d3f6bff00865a5fec3ff173f6c3d2bf699f849f143c65fb02fed3df1a9ff6cdf047c71f86df0d7c6ff177c2ff0009be265fdcf837c65f19be15fed03a07c37d2b5df16781f434f8a5f0fecfe2f780fc7177a15d780f59d23c47aff86f5cd5ec2f348b9b69bf9f7e9332e398785f52af02e5dc599bd7a5c49c3d3e27caf80f1b8ecbf8d71dc16f173867b4386715954a19ac730539e06b56a59654a38dc4659471f86a75e8d3ab566bec7817fb21e7f08e71570187a53c1e32182c466b4a15b2da1993a6bea9571b4eafee5d24d548a9d652a54aa4a1525094a10b27ec11affc33f187c058fc65f077e23fed21e3af87de21f176b834af0dfed53ae6b7e20f8b3f05b52d1a3b1d2b5df841a85ff8b6d078d458f87f5181b54b24f15eb9e2ebd7835986e34ef12df68b369ea9fa6109cc50b0ce4c51367a104a2b0391d0827231d0f4af8bbe04fed03a6fed21a06b9e3ff0e7c37f8c9e0bf044f7fa73f827c63f187c0537c3693e30e87a9e930ea11f8f7c13e17d66f4f8d13c2277c56561abf8bf42f0edceb51f917da45a5ce98d1ce3eceb6e2dadbfebde1fd62535ff00377e2d4f19538b736ab986031995632ae6588ab5b2cccb1f86ccf32cbd56a386ab4f0799e3b0983cba962f32c3d19d3a59857ad97e0f1b3c5c6b4732c350cc238aa50fedbc9550595606387ab47114634231857c3d1ab430f5943dc752852ad3a9385194e329534ea548a8b4e95495174d2f93755fd9b7c73f0efe26f8afe3b7ec5bf1d759fd947e2bf8fefd359f8b1e198fc25a67c4ff00d9a7e3d6bb143140be22f8c3f01757d4343b41e3b9e0b786d2f3e2afc31f13fc3bf889a85aa04d7b59f1005445f50b7fdab7fe0b0da6c074787e1d7fc13235593e751e3687c63fb53f866cda4c9097cff0c7fe10df12dc43c624934c83e274aa4e628f5555c4b5ed3457ec3e1d7d377e91fe1964186e18c8b8e6399e4780c3c30b95e0f8a728cb388aae5786a7054e961f0598661879e671c2d0a71853c360ab636be0b0b4e10a787c353a6b95fc8671e16f05e758ba98ec4e592a189ad294ebcf0389ad848d79ca4a529d4a54a4a93a927772a8a119c9ca4e526d9f1e7897f67ef8d3fb4d789b42f17ffc1417e3fdafed0fa4785b5db1f14f83ff00661f86de046f843fb1e7867c4fa55ca5ee8be22f11fc3cbcf10f8cbc6ff1dfc41e1fbd8e2bdf0fde7c68f1b6bbe18d1b5089352d2fc0963a82c7731fd86d991cb38f32476c962016663f875ecbe8381c0a28048e47047208ed5f8a789be2ef88fe31e791e22f1278af31e28cca8d2961f04b15ec30d97e59869ce339e1f2aca301470b95e594aa4a309568e0b0745e22718d5c4cab555ed0fa7c8787325e1ac2cb0792e06960e94e5cf565173a95abcfa4ebd7a929d6ad24b48fb49c946368c5462925f1cf8fbfe0a03fb16fc31d4f5dd1bc6ffb4c7c10f0feabe19d46fb46f1069fa8fc54f87b657da46b1a5ccf6da96917da7de7896df528353d3eea296d2f2c5acfed305d4525bb47e6a94af8dbc67fb5be93fb69fc63fd87fe0efc27f877f122f3f644f891fb7afecf1a57c52fda975ff0d5ef83fe1f78b3c57f09ef3c49fb467c36f84bf07d3c4f1691aff8fdbc57e30f831a63f8c3e2178774cb8f08f86f42b31a3d8eb1a86ade2680d97e9dc3f023e05dbf886ebc5d07c11f839178b6fafa7d4efbc549f0b3c04be25bdd4eea66b9b9d4eef5f1e1e1abdcea37170ef3cf7d3de497534cef2cb2bc8cce7c67f6fad4e7f097ecb2bf1ded2196eafbf62efda4bf655fdb35e2881797fe108f82bf183c3d6ff171624009105bfc1bf1278fa79d170bf668e45202e6bfa63e88b99783947e901e12d1a5c33c53533979dd1961f39cff008a3090cb3059cd3c1569d0af87c9729c9f0b5b1553eb51ff006378ccee38587bb4f1580c6ba8b93e2fc47a7c433e0ecf5fb6cbe1423423ed28e170f88a989ad8696229c671788ad5e34a8af6724eaa8612a4e4b9a34eb52bf33fe948741fd3a7e14557b4bbb6beb6b7bdb2b886eed2ee086ead6eade459adee6dae2359adee20950b24b0cd13ac91488c55d195949041ab15ff004707f17851451400514514005145140083a0fa0fe54b483a0fa0fe54b4005145140051451400514514005145140057c1dff054af1345e0eff82697fc140bc492cab07f66fec63fb4b9865725556eeebe0ef8c2cac464720b5e5cc08bd7e6615f78d7e497fc1743539adbfe096dfb4df86ad26316a1f15cfc1df815a7c6adb65bb9fe3afc79f861f0926b48860ef79ac3c617994c10d1ac9b86cdd58e27174b0187af8eaf250a182a357175a6ed68d2c3c255aa49df4b46106ddf4b2d4ba74e55aa53a51579559c69c52d2f29c9452bf4bb7b9f24fc3cd1a5f0ff00c2ff00841e1894159fc39f07fe15f87648db968a6d27c0da169ef137439478361e7248c9c577ff00127e317c22f825a169baf7c63f8a5f0efe14687a8dec1a2697ab7c45f19f877c1963aaeb0f1a797a56933f88b51d3d354d4594890d9587da2e5232b23c6b19dd576eada397c4b359c0a05b5bea1f65895170a96b604431a803002ac302818e001815c2fc63fd96ff0066efda1e5b79be3cfc0bf85df1925b2d06f3c33a6bfc49f09697e2c6d1344d46fe1d5350b5f0f1d5a1b83e1db8bed46d6ceeeef54d08e9dabdc3d8d82cb7ed1595ac717fc95bc4f0f6373ccb6af1856cf28e4d8896271d9a4b87b0f81c4e72feb69d5853c253cd71584c1c64f1126e73c4546a34d4dc69ba8e2d7fa175a18da196c296570c34f154e851a74562e752950b429c6379ba51954d2295a29277d2ead63dd209a2b982daeade549adaeeda0bcb59e26124373697512cf6d7504ab949adee21749609a32639627591199194d4b5f9df6dff0004f1d33e1f208ff664fdac3f6c1fd99ac206dda7f82b43f8af07c6ff0084ba7281f2db58fc36fda4f44f8af6da569e9f2aa69fe1ed6f43b68a1458add2150bb6e0f84bff00053af0eb795e13fdb23f665f8ab6f161963f8cbfb1cf883c33ac4b18ce12ef5af82df1bf44d34bbf47b883c27028da592d816c2f64f83b8331b39d4c8fc53e1ea542726f0f82e2ec938bf20ceb9652f729e26394643c57c394aac636539c389ead293529274e2940e059b669422963787f19292b7b4a99762b038bc3ad93941d7af83c54937aa8fd579d46d74ddcfd04cd15f957f09be2d7fc1513e33fed33e25fd967e14e8bff0004dbf8b3e28f86ff000fe6f1dfc67f88da0f8a3f69bd0fe1bfc18bcbdd62cb48f057c35f1f6a167a478e1acbe2bfc438e4d6bc43e1ff0002d84f7fab699e15f0b6afaff8922d2eceeb44fed4fb947ecc5ff05a2ba6f2dadbfe0963a1237fcbda78cbf6bcf153c39fe2fecf6f02783d2e88ebb4ea36bbb18dcb9dc3fa0786be815f48ee30c972be23e1ce1ee1fccb21ceb0d1c6e579affadb92e06863307394a34f154f0d996230799c70f5945cf0f52ae5f0588a4e15e8a9d0a90a92f91c678bbc1980c4d6c262f138ea589c3cdd3af47fb3ebd4953a8926e0e54b9e93946f69253f7649c5da5168f6faf3df8d7ad7c20f0afecf7fb44789bf688d7f48f0afc01ff8523f13bc29f1475ad7a78e0b1bcd0bc67e0fd5fc372785f4c864fde6bbe2af12b6a434af0c78634c8eef5ad775bb9b1d374ab1b9bdba82368b4aff00827bff00c14f3c6636fc47fdbdbf66bf83b63215f3acff00673fd8db52f146bea84af98965e30f8fdf1afc65a55acc177f957571f0eaf955f616b460307e97f82dff000494fd9d3e1ef8fbc25f18fe3478c7e34feda3f1a7c07a845ad780fc79fb57f8e20f1ce83f0d35f8d187f6ff00c29f829e1ad17c1df01fe1d6bf14ade769fe28d03e1ac7e32d31923367e25460cedfd45e02fecddf15322e3de1ae30f12389b86b8732be1cccf099cc32ee1dc762b39e20c5e33055615f0d87f6ab0586ca7098775229d7c42c7e326e3174a185946a3a91f85e2ef1a323c6e518dcb325c0e2f195b1d42786957c653586c2d2a55528d49f239cabd5a9cbcca11e4a714da93a9a72bf6dff00826ad8fc5bd2bfe09eff00b13691f1e749d4f42f8c9a47ecb3f02f4af88da46b91c9178834ff0013699f0e3c3d637b6fe24825fdedaf89956de23e24b39732d9eb86fed6526489abedba071c7a7ae4fea793f53457fb567f34051451400514514005145140083a0fa0fe54b483a0fa0fe54b4005145140051451400514514005145140057f13dff056cff828afc7afda33f68ed77e0b7c24d0bc3973fb2cfec39fb5c7c30bbf1c7c34905adb7c53fdb03e2cfecdde31f0a7c42f885a168be2ed6265d1be1f7863e1bf89e0b1d1bc0da4456ef71f103c75e1cd466f126bda6683269fa7a7ebaffc1607fe0ac3adfeca3358fecaff00b2c5df87b51fdae7c71e178bc55e26f1b6bda7c5e23f05fecb1f0b755b8b8d3f4df88fe2dd04ba41e2af899e2ebcb4beb2f831f0b6ee686db5bbab0d43c63e3030782f44fb2f88bf8d8d3ff677f06f8efc5be21d6f5ff17fc7dd7fc7be31f127893e20f8dbe2b6a1f1dfc7565e34d6fc6fe29bf6d53c4de36bdb1d16eac3c0f1eb9afeb330bdbbb68fc2ada6bca5217b796de055afc5fc5fe33cb32cc931fc3bf5f9d1c5e3f0ce9e6ea8e0563a14725c551ab4f1584c4cff00b432c9e16a6654671a3cf83c57f6950c256957c32c357ad82c49fd93f459fa36e7be28e2b15c659c70c4b1fc0d84c366597e5b5ebf114f862b63f88d4614a8e3327ad1cab37fafac866ea5792c5e169e4d5734a786c1e32be269d1cc302ffac5fd957f69ef817fb5768fa8f8c7e0b78eecfc4975a49f23c5be07d4e097c3bf14fe1c6b53b16bdd0be24fc38d5bc8f13f84757b270d6d235ed93e937ad9b8d1b55d52c1e2ba93eb5cfaf15fc527fc2b5baf0ef8f7c0da6fc6bbcd43c41ac6b97b7fe19f803fb5ef80f52d57e12fc67b2f1368fa749ad1f841f10fc63f0f2f741d474df1ccfe1cb5baf10f82358b3b95f09fc47d2f49d7acce8567e24d0af6ceefee5f0dfc70fdbe3e1e451d9f81ff006def1278a74ab7016d748fda2be0ff00c35f8d13c71a0c2c52f8cf48b7f86de3bbe0385f3f54d7b52bb61967b9773babfc4df11fe86dff000a6b1bc13c7795d1cb71d4218bcb307c5d4335f652c0ca53a54e197f10f0fe55994b3370ad4ebd2c43ccb8778731384c452ab83c4e1dd7a356a3fea75e0af88d898e635387b0183e2986539857ca732c0471d97f0ff14e519961e951c44f059e64f9b661fd8f4eacf0b8ac262f0d8cc9f89b37c066581c5e1730c154961b130e5fe9debe43f849fb327c3aff008285fedc9fb567c3ff00da464f1c78c3f677fd91fe18fecdbe17f0a7c13d1fe2378ffe1ffc3cf16fc60f8dda5f8fbe25f8fbc6ff001134ff0087be22f0b5d7c40b9d13c156bf0d3c39e1cd0bc51a86a9e19d2adeef5cb95d11ef3527b81f8f92feda7ff05339e1fb37fc2ebfd936c060aff69d87ecb7e34b8d4f9c8122d9ea3f1ee6d29645cee01e3961caa86465dc0fd3dff0498f8e5f14be077fc143edfc57f1f3e3c3fc46d1ff00e0a2f6baa7c1cf1f6a1aa786bc27f0b7c13e17fda37e04f8174af14fece4be18f0e68931d2b4dff84ffe10e9bf167c091c5757f7daf789bc51e1ff000dd99bebeba7b0b4afdb7e83ff00476c470178e387e20e3bccb81b3aa74f86f3bc1f0e603052cdf33c553e22af532fab4731a51ccf8730597e1fd86514338a30aef190c6c6ae2a9d3c3d093a93953fc3bc7af0bbc5ac8bc38cc73dccf83734c8725cb31d96cf37c7d5ce786eb5b0b88aeb094e1f57ca73cc7e36a539636be13da4950fabc60a4ead45eec65fd6c7c0efd9f3e06feccfe02b1f85bfb3dfc24f87bf063e1e69f713dedbf83fe1bf85347f09686da8ddac4b7bab5e59e8f696aba96b57e2084ea1ad6a26eb55bf68d1ef2f276506bd8690107a1cf383ec47507d08ee296bfdad3f80028a28a0028a28a0028a28a0028a28a0028a28a0041d07d07f2a5a41d07d07f2a5a0028a28a0028a28a0028a28a002bc83f681f8d3e0dfd9c3e067c60f8fff0010ee0daf817e0b7c35f1afc4ff0016488f1c7712687e09f0eea1e21beb4b2f359524d46fa2b0365a7419dd737d716f6f1ab4922a9f5fafc24ff83897c7371a37fc13eb4ef8476ee107ed47fb4e7ece5f00b530afb659bc2575e3a5f8a9e3eb5007ccd06a3e06f859e21d2ef507cb258dfdc44f9590a9e5c7632965f81c6e615f4a180c26271b59a766a8e1284f1155a6f44d429cacd9eae47946278833bc9f21c15bebb9de6b9765183babafad6678ca382c3dd76f6b5e17f23f904bbf157c42f893af78c3e357c649daefe38fed07e2bbcf8e1f1967669641a778a7c676b6f2f87fe1f5834cf2490786fe0ef80a3f0cfc30f0ae9c184161a7f87677863592fae99fda7e1969eb169b75a995fde5edc9851b1cfd9ed415c74e8d3339e0ff0000f4af35bfb5d5b5ed6354beb3d2f50bb37ba8de4e0db59cf32e1ee24280324654a84daa9838da063815ed3e0b8ef6d745b2d36ef46d5ec6eadfce5733e9b74b049be69240eb3088a0c87018394208e98e6bfce9e33cdb1599e0abe33135e35f30ccb191c5e63cb522e709d593c4558aa7cce71a146aaa746942cd53a10a74e368247fd32f0670ae55c19c3d9170a64584586c9b8772bc265396d3842ca547074a9d155e728a4aa627156962b1759de55f1356b56a92955a9293eab56f86a7e38782fc75f0362bafeced6be25e83ff0016e35a042cbe14f8e7e0994f8d3e0778c2ce42375bdd693f10348d3b4cba963647b8d035ed734d918db5fcf1bf9ffc23f880bf153e17f80be22fd8ff00b36ebc5de18d3753d5f492086d17c4688d63e29d0e4538292e89e24b3d574992360191eccab0078af64f0d6a573e1cf14f8635d5df6d2e89e23d0f57491c347e5ff676ab6976d2124021556262e7fbb9af10f03d8e97e19f893fb59fc39d0ee2cee7c3fe01fdae7e32b785e4d3e786e6c17c25f13ee747f8d7e1f8aca5b66780dac517c47ba82158db6a2c2530854a2fcdd197d7f833174db53a99067386c561eed7352c067546a61b1f18adf9279860f2c9a56508cea5596b3aadbf92c429645e3565d569c254f07e2070566184c75a0d42be7fc158dc2e2b28ad276e4588790e739dd09cafed2b61f03868b4e184baf4fae13e3af83e1f1efecb5fb43e94742b2f146a9f0db40f09fed3de16f0f6a1049736dabeb3fb3878aacfc6de21d1fcb85e3b90de20f8692f8eb412f672c3791adff996b343322489ddd7acfc0bb9d2e2f8b1e0fb1d7624b8f0f789eeaf7c13e23b4940686f340f1ae9979e19d52d2746f95e19edf5264746f9581e78cd70f0be652c9f88f24cca2dafaa665849cf964e9b74655a10ad1e78b8ca2a54a538cad2578b69ee7b1e2d70eae2cf0d38df20e48ce78fe1dcc5e194e11ab158cc2d178cc149d39c6719b8e2b0f4a4a2e12bb4972b6ec7f41fff00047bfdb3354f8b3e129ff676f883e31d4fc77ad7853c01a07c5afd9fbe267896fe4d4fc57f157f66cf105d43a3db5978c35790c92ebff127e06789a6b3f00f8cfc4574ff00da3e2cf0aeb7f0b3c75acbdc7883c5baedc1fdb8aff3f4ff008270fc62d4ff00664f117ecc7a25f6a17179f147f659fdadb52fd9663f08d84379aaf8c7e24f81352f88379fb35fc55f04f877c37a725c6ade22bd1f0fa4f879f1d52ced6d278ed66f86567ac5c35b5adbc972bfe8140e7fcfb91fd3f1eb5fe8c787b9f6233cc8651c74a75331c9b31c7e438eaf28ca2b17572caee8d3c645c97bff005bc3fb0c4ca716e2e755f2a48ff9fbf1bf83703c1fc6909e4f0a74721e2cc8f25e34c8f090a8aa3cbf01c4b80a5983cb26936e0f2cc5d4c4e01466dcb970c9b6f76b451457dd1f8f05145140051451400514514005145140083a0fa0fe54b483a0fa0fe54b40051451400514514005145140057e0a7fc1c09fb37fc7af8ddfb35fc18f89df0334087c6adfb257c6cd43f682f885e06b6bcd1ecfc55abf80acfe0e7c50f026b3e23f04c5e25d5345f0cebbe23f879178cdbc629e11d6b57d257c4f61a75f5969b7e7578ecb4ed4bf7aebf21ffe0b5dfb5ae8bfb367ec47f10fe1ee996f07883e397ed79a178bbf65ff00d9ff00c13f6c36b3ea7e2df893e0ed6b49f1478eb54780497963e09f83de08bad6be21f8c3578e1f2922d2b4cd012e20d4fc47a5eff3f36a597d7cab33a19b4e14f2aad97e36966752a5454a9d3cbea61aa431b52a5497bb4e10c33ab294e578c629b69a563e8384f159ee078a786b1bc2f46a62789b07c419362b8770f4683c4d5af9e61f31c356ca68d2c3af7abd4ab8f861e9c2945a94e52518ca326a4bf8f5fd82bf67df85dfb67f817e337c48f8bfe38fda7fc5f0786fe3569fe1ef04e81abfc5bf117c25d217e19789fe0d7c32f897e13bcd4fc15f07eefc2da7ade6a9378b357be8266bf7ce912e99035b5bcd6f3eff00d2eb1ff8238fec9ba8da41770fc0ad508b9856587519fe3cfc755d4658e540e92fdacfc5169fcc7043ef3f30396e1b22be61ff00826169b63e0fd6ff006c4f863a75e7db74ff000aea9fb21df69f7223f216f2d53f67fd53e1bcd7e20cb797f6bb8f8641a4c33042ab1ef6da18ff00465e0a97cff09787a4ceece976c39ede5a98bd7a7ca71ed5fe04fd24bc62f1238138e336c2709f16711651947d6323781cb70d9b66d9760b0585ccb84b23cee953a183cb7179750a57a98dace5cd42fccf549b67fa119e60315fdb9c4187e219cf36cd703c53c5382c4e2732ab5331adcf82e21cc70d0842be2ebe2eaaa54a9d1a74e8c238894614e108c24d2527fcb67fc141ff0060dbafd917c23f023e217ecdd7ff0015fc29a8f8b7f68af0cfc27f10f86b5bfda73e276a7e07d634ff001af827c7d7ba15bdd8f173fc4bfec2687c4be1bd3a54bbb7d13508af133a4ddda3dbde349179c7c0df871e38f05cdf133c5bf12758f0cdff008dfe2d78bf49f16ebba5f82adefe3f0a7878e87e0fd13c19a7d8e9d7baac569a86b17d7761a2437fae6ab269ba45adc6a3332e9fa5da5a468a7f71bfe0b05a4f9ffb0deb9e2e085a4f853fb43fec8df13049deded2c3f684f05784b55989c7dc5d2bc697cb27cc81637259c2820fe5bca9e5cb2c7c7c923a71fecb15fe9c57edde107887c4bc6de0a70d66dc418b86659a6699a714e539ae6d898d6c5e698aa393e7380cc30b84ab98632be27132a54e38acbaafbd52555ba14e1ed561e30a11fea0fa27e5f86ccf1bc6388c663333ad5f84b3ccab1592e592cc2b4b29c0c73ce17af964b1f47057ff0078a94e39d61546a55a984a71c4d6ab4f0b0c5c9e25c756f4fbf934ad434ed5216292e99a858ea3132e432c9637515d210460e7310c60835529180652a7a3020fd0f15f51792d63f12d63fe25b6f657bf7d3be87f6ed4846a539d39c79e3384a128ff0034649a71f3ba76b6cefa9f72fec4565e0cf829ff0005bdf13f899740d0b4ed47e3a78a3c0fe25b5f15b6996875abcf057ed53fb3178d6ceef468754743711e9b6ffb41fec8b6122456f2205d57e20dd798ceda9ba3ff006795fc07fc57f8ada9f803c6bfb20fedb765a50d4748f813e29f837f027f68236b72b05c783b47d33f692f84df137e0cfc5dd5a39ca2cfe0759f45f1f7c32f14ddacc9368727c514bb224d3d6fb6ff007dc1b3eff4f439c1cf7c8c1aff00453c19cc2b667c2d88c5cabcabe16b6634a797b956f6dec684725c9e862f0eef394e8cd6734334c43a15145c29e2294a31e49c59ff0038df484e11c570478a7c4b9162f0d2c2d4a38dc4d58a953f671af47118bc4e230d89a0ac94f0d5309568428d58de138d26949b8cacea28a2bf5c3f130a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a28010920640fcf8fd707f957f011fb6e7ed5f37eda7fb5cfc66fda92df503a87c24f86b71e25fd96bf639b5598c9a73fc3ff09f8864b1f8b9f19b4d50c2196efe37fc4ed16f24d3b585862ba9be18f817c0f66ecd0cd299bfaacff82cf7ed25e22fd98ffe09d7f1ebc4be00d44e97f16fe2959787bf672f83377148f15ed9fc4dfda0bc41a7fc2fd175ed3e442196f3c15a7788359f1ea30ced4f0b48e410a41fe2635ad0f45f03e8fe0df863e168becde18f877e16d1bc37a45b83f76d74bb0834eb3693a97b87b3b58e7b895be796e2e2695c979189fc17c74e21a987cbb28e11c25471abc455eae2f35e5ba6b22caa546a54a12b3568e638ea986c3c9ddc67428e2a8ca328d4925fe847ecfbf0b70dc4bc779ef8999b61e35f03e1fe1e861723a75629d3a9c539dd2c4429e3173294653c9b2aa58aaaa2e2a74b1798e5f8ba328d4c3a947e94ff008270ea1f67fda6ff0069ed0189c788bf669f805e308c31c9927f037c5df8afe12bb90e4fde4b5f1ae9c92360e77a6768da6bfa47f85f71f68f0468c7bc22e6d8f427f75712601c74c0618cf38eb5fcc07ec27a80d33f6f0f0f69edf227c47fd923f683f09af0544fa8f80bc69f083e2669f0e780ce9656faf4d1f57dbe6ed0156435fd317c17b9f37c2b716f9cfd9755b918eb8134714a3df9c93d79eb8aff0013be9a38054f8af138b51b3c660784b338b6acdd1a79556e1872d574af904e0d26d292946f74d2fd4bc4fc2bc17899e2260dae5f65c512c5c56bb67994e539fca497f7a79a4dbebcd79349c99f3bff00c14f3c392f8abfe09bdfb74e976c85ef34ff00d9b7c69e37b10aa59c5f7c2dbed13e285a488abc96865f07098761b32dc66bf156d2fa3d52cecb5585d648754b2b3d4e17539578b51b68af2370c38219275208e08e4706bfa4af8c9e0e5f88df053e3a7c3878c4cbf113e03fc6df01f92577f9afe2ef85be2cd0a18f667e72f3df4400ee78ef5fcac7ece7aebf89ff00679f80fe2295b7cfac7c1bf8697772c4ee26ecf83b478aef2783b85d45286c8fbc0f51cd7e87f457c62c6781d88c3292e6c87c4ccf69b8dd734619ff000df0c6269e9bf2cea64d886bbca33fe567eb5f447c5ac3f1e789796b6ffe14b85b83735a69bd39b2bcd38970188696fa4733c1f37f8a2b68c4f64a28a2bf783fbd0ed7c0fab786adaf759f0c78ff004987c49f0afe25f86f59f86df163c2d760b59ebfe00f17d9cba46b9048bfc3756705d1bfd3ee50acf6975009adde394071fd3a7fc11abe3d78b7e207ecdbe26fd9cbe2df89a6f16fc73fd86bc7d27ecd9e32f155fcde6eabf12be1be9da0e93e2afd9c7e345e392f2dd4df13be066bde0eb9d6f549a4964d4bc75a278d2477f3639157f95520104119046083dc1ea2bf52ff00e0969f16ae3c03ff00050bf8371cf752a689fb627ecb1f117e0378aa146db0dffc66fd8bf5eb3f8a5f09756bcce44ba95e7c08f8a3f1574757244d358f852ce3c98ac42d7f4c7d1b78aaae0f88719c2b5ea49e1338c2cf1584a729371a78fc145d497227a47db615d55251b393a70deceffe6efed10f0c30d99f06e49e28e0b0d08e67c3b9850c9738af08a53c465399be4c24aadbde9cb0b8e8c29aa8dcb929d751b463b7f5ad451457f6c1fe3c85145140051451400514514005145140083a0fa0fe54b483a0fa0fe54b400514514005145140051451401fccaffc1c57e2cbcbcd7ffe09bdf0744ac747d7fe3bfc68f8dfab5a03f25c4df02fe096a7a2787de6453965b3f107c61b2bd8598158eeade1705644465fe6a7c4370d75af6b33b924b6a1708b9ea1216f2507fdf283f3f4afeafbfe0bdbfb187c59f8cff097c2bfb667c13f19786edfc75fb04fc29fda63c7973f08bc5de11d5f5ed27e33782fc55e15f087883c5da0695afe81e20d2357f0878cf4eb0f8591ff00c22b78ba6f8834dd4aef5296cb52b2822d938fe3be3f167c72f11e9da66bb6ff00b3c7c34d423f11697a6f882c35bf0afed26965a46a565ae5941a9d8df4765e22f866b75125d5b5d4536d2b211bfef363737e27c61e0e78ade22f1a56ce781f83733e2dcb72fe1ecb32baeb27af819e232fab5731cc714de2b098bc6616b2a55ddfd9d6a31ad09ba5cb3709c544ff00433e8c3f4c5fa307d1bfc2d9f0d78d5e27e4fe1a67dc43c659ee7584abc4181ce3ea39b61a8e57c3f838c7099865f97e3b0eebe1a9d18fb4c357a946b2f6aa54a9d68b9548fbc7ecf5ada7857f6d7fd887c49337976b7ff1b7c4bf09751909d886c7e37fc1bf889e0cb68a47e9b66f1241e1b54424079fc90033ec5afea1fe08ccd10f1269afc3c1716b2153d43289addf8ec41419f73ed5fc60f8e35afda1fc17a7f867e29eb1e01f853e11d2be117c59f829f169ee2cfe2ceb5e30f14da7fc2bef8b5e0ed758e9d6361e01d0b469e696de2b8b3bc373aac118d32e6f1a3cca111bfb48f06c49a67c56f1be9712ed86e65bf9ad95790f19bc4bb83675cfee6e3e5c7057a57f973fb44bc20e3af0d315c253e3de1ac6f0d663c43c0f8e5976131f3c2cab57c0f0a715431b3c5db0d88c4420a55b8bab50e4954f6dfecd79c15374dbeac67d20bc12fa45f1c719f17f815c7d967887c3587a7c3d84ceb36cab0b99e170b84e24a196cb095f05ff000ab81c056ad259460726c42ad46954c34956e585675615a14fe8af0e085bc45a125c286b79b57d3edae548c86b6bab98edae108feebc32bab0ee0e0e7a57f1cbfb33e953f86be0ee8fe08ba564b9f86be2ff008b7f0ae68d8e5a26f865f17fc77e078a26c7431db6876eb80028180a0015fd367c74fdb27f67cfd98f55d1345f893e39375f12f599a19fc1bf03be1e691a8fc4bf8f7e3a9e295248adfc23f087c1f0ea7e30bd8e5650adacea361a5786ac81f3f53d72c6d95e74fe687e257c27fda2bc09a4fc40f8f567f177e12fc11f0ffed05fb6cfc5a6f067ecdbe3df8691fc59f89df0cefbe3d78c7c73f153c15e1bf883e32f865f1317c23a6f8a353d22db59d4f5cf0f437b736be11b95fec59759bdbd530a747ecf4f00bc5ff153813c46c270bf086611cb715c4fc159a6519ee7919641c399a56860b88f28c5e172fcf734861f2cc562956cc72b82a54f112529cd53e65514213f3725fa577839f45ef11571478a7c455f0994e67c0fc419356c1f0fe5d5f8973ea55f0d996479ec7192c872af6d9ad4c06172fcb334ab8bc550c3568e16318cab2a74e529af6ba2be515d17f6bf90e25f8e1f02add3905ecff67ef134b2fa6e5177f17da2c8ea0323024636e382e6f879fb42ea795d7ff6afd5ac6175f9e1f879f057e1af85a504f07c9bff00120f1bde42d8ced75059787c6e0057fa5f97fecd9fa54e36bc6956e1ae18cb212693af8fe31c9dd34b4bb6b053c656b24dbd2949d95d26d72bf4739fdbc7fb3bf2ac34ebe138dfc43cfeb463cd1c1e51e19710d3ab51f482a99b2cb28424fbcea462bbdb53eaf11c8c18ac6e422966214ed5519cb3b630aa3072c7000049e95aff0003ff0068bf03e8bfb51fec3de0ef026bb378fbe35782bf6fcf823e36b7f047c31b3bbf885e24d23e1878d7c39e35f80df1cf57f12e9de0f4d5eebc35a0693e06f89835cd5b52d722b1d3a08345f32eae21f2e357f8b25fd983c09e21757f89be2ff8c3f18b043cb6ff00137e2bf89a6f0d617e67927f08f8567f09f8485ba805e48ee34996dd630de60280d7f4bfff0006d3feca3e11f087c29f8f3fb6de85e07f0cf82f41fda6bc53a5fc3af805a6787741d2f43b75fd9bbe035eebde1fd3fc5d0dbe9f0dbb237c5cf8aba8fc41f17349751bdd6a3e19d37c0f792cf2a791e5fe92fe829c51e02be1de39e3be3fe1fa99ccf3374b2ae16e19a18bc5d4c4c69d09cb1f5b1799e614f071860f0f4e74a8d7fabe06a4a55317429c2ad272f6b1fe71cebf6c17879f4c5cbf8dfc20f093c17e32a1c2eb2586273cf1078f71d96e594f035a78dc3ac9a86579064b5f34ab3ccf175e96271543ebb9ad0a50c3e5d8a9ce8d6e454a7fd3c8e83e94b4515f647f3c05145140051451400514514005145140083a0fa0fe54b483a0fa0fe54b40051451400514514005145140152fec6cf53b1bcd3751b4b6bfd3f50b5b8b1bfb1bc863b9b3bdb2bb89e0bab4bab7995e29edae60924867865468e589d91d4ab115fe7d9fb55fec83e22ff827a7ed29af7ecaba9dadec9f073c409e27f88ffb1578d6eccf35af897e08c5a9c779e20f82ba85fc81d0fc40fd9c6ff59b6f0ec96724df6bd77e17ddf82bc5514481b53b7b4ff41eafe56ffe0e369c3fc73ff8263e9f9e41fdb6b57f602cfe1a7c2ad341f4c93ad6df5c123a135fb0f811c439a645e26f0cd0cbeaa8d0e21cc709c3d9a61ea733a389c0e6389a54dca514d5ab612b7b2c6616a2d63568aa73e6a15abd2a9fce7f4ade0dc878bbc0de3aad9d5072c470964b98f186458ca768d7c167192607115e972c9a69e1f1f41d7cb71b4a49c6786c5cea454713470f5697f3a9f173c0bacfc4df857f103e1e787aef41d3f56f1a7872ff00c3b16a3e236d53fb3b4e835084c6fa9c49a3dbdc5d4fa8584a21b9b2b76548249230259a31835ed33fc5cfdb1be286a8f79f1b3f6b1f1469d6b369d6da5dd782bf662f0f597ecefa1ea1676b69159bdb6a9e3db09bc43f186fc5d450afdace95e33f0b4b3e5f6c9180b8e747ca411952a72a41208e7839f7e0e2a5795a421d80de3197190cc4742dce377fb40027bd7f6ef1f780be0bf8afc43c33c53e277871c2fc7d9c707e1b30c170e4f8ab2ea59be132ac36695f0988c7c70b82c4f361154c4d7c060ea549d6a35eef0b45c792504cff001fb803e905e327853c339ef09786bc759a705e51c4d8ba58fcea791c30b4331c6626851861e8f3665528d5c650a50a305151c156c33d65cce6a5a76fe104d23e1ce9dace97f0d3c37a0fc3ab7f13333f8baffc296b750f8b7c6d2b1264b8f1f7c46d5af756f891e3d9e624bcc7c63e2ed6a2323129120c01e65f183c44bff08f7c14f8729216bcf14fed73f093c636164a719b4f865f0efe366b5e22bf118ff96569a7df5b4370e0011b5e5bab1fde28aeb2cafeedf117946e36e017fba5460e373f4fcf838eddf9a97e1e45aa7c5ad0fe296a7abdc5e1f0b782354f09783bc2ad6914761e1fd53c51a8c371e2ff00169bc595e5bfd5f5ad274ed1fc3d6eaf0c2ba56996b7cb049336a93f97faa62f8732b9f0d60f87b86b24c0e55974335e1f9fd530384c2e5b81c060f2acdf2fcc3135a1468c68529b8e1b013c361e1878ceacb13528f3c69d0556b52f87c8f8e73b8718e61c61c5fc4d99e739aae1ce2ec353c5e6f8dcc338cc332c7e77c359a64597617db5596227083c5e6b4b11899e2aad0a14f0386c5ba739e27eaf86afe8552246f210aa0b139000e7a024fd00009663855504920035f285bfed4977e34b54baf81ff023e2b7c52b2bb92e61d3bc61aedb68ff00097e1a5cbdaddcfa7cf711f8a7c677bfda9a858c1776d711492e89e17d4246682548834830216f85ff0016be3296b6f8f1e3dd2f4cf064e375d7c13f82936b5a2f86353b6c86fb0f8ffe26ea26d3c6fe34b33854bad1b4487c1fe1fbd50f1dd437d0318dba25c6f82c6a8d2e1ac163388f115945e1eb61e955c1e48d4edc95ea67f8aa2b035b0ff6a4f2859c63142d52181ab0d4e38f863986533955e3acd72ae0bc2e1e7258ac1e2f1586cd78aaf4ddab61697086598aa99b6131da72d38f11be1ccb9d48ba55b34c3cd34a6f13f89ef7f696d5f50f82ff0009b56bbb5f8590ea6340f8e9f1bb4797658cf62d2a45ac7c1ff84babc64c3adf8b35f859f4bf18f8bb4d79b46f056912dd5adbdcddf882ee286dbfb67ff8200c634cff008251fecd9e0b6919ae3e166bdfb43fc22ba819a476b16f863fb4d7c62f06da69e6494b4b28b2d3349b1b7595d99e44456725c927f93ef0be8da27856cb42d1742d374bf0ef85fc351d8dbd9e99a559dae93a1e85a4d8ca8c6382d2d521b3b1b2b78d5e573b5171be59199cbb9feabbfe0835aee9de24fd8abe226b7e1ad461d6fc03aa7edc5fb746a9f0d3c4563be4d13c49e04d67f695f1eeb3a56bbe1bbd28b0ea9a05fddea1a8b586a568d2da5d0491a199c29c7f1dfd2d729c4e1705c1599e6b8b86373bc7e2f39a78aab4a12a383c3616961f2f961f2dcaf0f394ea52c161672ad5255aace78ac762abd7c5626508bc3e1307fe917ecfce20c1e618ef12f25c832fab9570be5182e189e030d889c2be618fc7e22b6710c7e799de329d3a74f139a63a1470b46187a10a780caf0186c3e07054a53862f1d987ed3d14515fc527fa5e1451450014514500145145001451450020e83e83f952d20e83e83f952d0014514500145145001451450015f8afff000580ff00826dfc66fdba5bf673f895fb3e7c42f867e13f8b3fb355c7c5e8b4bf08fc61d37c52de01f891e1ff008c7a1f84b49d7f47bef15f831eef5ff03eafa649e0cd32e745d721f0cf8aac9a4bab98750d2bc90b21fda8a2bbf2bccf1f92e6581cdf2bc4d4c1e6396e2a8637038ba4a0ea61f1586a91ab46ac63561529c9c2714f96a4274e56e59c2516e2fcbcef25cab89327cd387f3cc152ccb26ceb018acb334c0577523471981c6519d0c4e1ea4a94e9d58c6ad29ca2e54aa53a91bf3427092525fe7fbf113f620ff828d7c1d9e483e257fc13efe386b36f13303e28fd9abc45f0fbf696f0ade84628d7563a578775ff000d7c54861931e6476da8fc36b7bed840f23770df0ca7c7af87021bb9f52d3fe31f87134fd6b5ef0e6a0de25fd9e7e38e9b1587887c2dac5e787fc51a0ddddc5e03bad3e2d63c35afe9f7fa1ebda77db1ae749d5ac6f34fbd48aeade58d7fd3a2bf908fd9aef2fadfe15fc478a1bbba8123fdba3fe0a2b16c86e664461ff0da1f191b242381d656639ce4b16ce4934fc79fda17e2a7801c1590f137faadc23c7b5733e2cc0f0d56a39bc71b9154a74b139267d9acb191af91ce9e1e5554f26851f65f5050947113973c1d3827fcf5c31fb373c07f12b887179761334e3ae09a70c06233084324ce30799e1a9ba788c25154634f88f2dcdb17c96c4369cf30935cb6d6ee4bf05f4ffda77e03db6f8878d357b8964618820f863f16e5b8c818da2de3f0234a58f40a14935d7e8df1d740d7fc2fe30f885e06f867fb4afc4cf04fc32b7f12eabf10bc5bf0f7f663f8ddadf853c1561e04b19358f1b49e2bf14de78374bf0df879fc29a55bcda8788a1d6757b29f48b4469afa2850135fd6af857c0ba428d26e835f5c5e5c8b1646b8beb8911269cc4036cdfb4e1db23d2be6cf8696a34aff00824a7fc17fb4e134b2a69bf1bbfe0b11629248497658fe1d6ba873e81999b81c618939c927e33e8b9fb5cfc4bfa4967fc5990e03c28e0ce0ecbf84b2ac162a79b7f68e759cd4c4e3b1d983c353c1d2c1d7c4e11420a953c4e21e21d49d9c29d39516aaf3478b8f7f654f81dc0b1cb7155fc40f13f3ead98e22bc5e12ad7e18cb69c2961e9d394a6eae1f876b55779d4853718ca9bd64e32d0fe74bf668fd88ff006f483e097c39b2d3f45fd99bc2565af59eb5e2af0ef87fe286aff1565f88da4786bc6fe2df10f8bfc329e29d37c0da56a9e168b54b8d075cb0d4534ed1f5d9e3b6b2bab5b4bc960d463bb861fa53c4dff04f1fdb6fc3df0b7e2afc46f12fed15f043c1107c3df85df117c7f1e95f0e3f67cf11f88b50d52e7c0fe0dd6bc516fa68d5fe247c46fb3db437f369496f71743c357125ac12cd34314b2471e3f617e11218ac3e07290415f859f0841eabff0034f3c39f291ec3d47f2afa63e3ac4927ece5fb5517f98c3fb28fed3332f1fc6bf053c6a8ac33c065dd953d88e95fe5962bf6a3fd35b3be39e08e01c9fc51c2f0a64fc459f64f9352fec1e16e1dc3d5c9f28c4e7387cb6960b078bad97627319d3c1e5f18e1a8623138cad8f9469c6557152af29e225fdd58cfa0bfd1472bcbb35e2cc7784193e7f9e57c06373cc7e333ecc33acd163f36ad41e33118daf82c5663532c8cf138ca93af56952c1430b17371861e34e2a078a7ec2dff00042efd8bbe297c00fd9cff00683fda7b54f8ddfb58f89fe2afc19f84df17754f03fc67f890f69f04b49d7bc77e0ad03c6575a7587c1af85fa4fc3cf04eb9a1e9b77ab3d9d95878fec7c6825b58505fbddbb48cdfd1bf85fc2de19f04787745f087833c3da1f84bc27e1ad32cf45f0e785fc33a469fa0787740d1b4e856db4fd2744d1349b7b4d334ad32c6d912decec2c2d6ded6da0448a1891140af97bfe09edff260bfb0f7fd9a07ecd3ff00aa63c155f5fd7fb019a66999e718ca98dcdb31cc334c64bdd962b32c66271d89714df2c655f1352ad46a3d1735936dadddff0026c9722c93873034f2ce1fc9b2ac8b2ea4f9a180c9b2ec1e578284da4a538e1703468508ce565cd254d4a5d5b0a28a2bcf3d50a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a42c075207d4e28b80b45466688759231f575ff001a6fda2dff00e7bc3ff7f13ff8aa575dd01357f201fb3991ff000ad3e298073b3f6f7ff828cc5c0c0017f6ccf8b64003db9f4afeab3e337c6ef84dfb3cfc2ef1afc6af8d5e3cd03e1dfc2df877a349af78c7c63afdcba69da469c93436b0810da45757fa8ea5a8dfdcda695a2689a559df6b5af6b37d61a2e8b617faadfd9d9cdfc907ec6df107c2df12be14fc53b8f0bdedd8d55ff6e0fdb9bc51a9f81bc43a65ef85be27f83b4af883fb4cfc41f1d783e1f885f0cb5e86c7c6fe00d5f58f0a6bfa4eb50e8be2bd134ad452d6fa2692dc1dc17f86bf680529d5f07785aa42139430fe28e4b56b4e1172851a52e14e35a1ed2b4926a9d3f6b5a952e79b51f69569c2fcd38a7fb4781338c38d711194a3175321c74209b51736b179754e5827f14b96129d926f963295ac9b5fb1fe0fc95f0c67bc9a37eb25bff4af8ebc2cfb3fe0945ff070a3703fe3207fe0b16bd481f37817594ebcf5cfe35f5f6873c1a269ba0eabae5c5be87a669e9a5dcdfea9ac4d1699a7595bdb98649e7bcbdbe682d6da18a35679649a5448d14b31001afc85f869fb4ef843e3ff00c09fdba3fe09cdfb2c7c58f825e32f8fff00f0510ff82a4fedf9f03a0d4ae7c516be2cd1fe14fecf5f143c0ff103c71e3bfda325f0ef86355b7d53c6da37fc2b3f879e30d27e18cba26a50f873c41f11750d1ed2f35cfb3693acd8b7f2b7ecade68f1078cb2b3e4fabe43efdb4bc31d8dd39bbdaef75a297676fa9f1fda93e1cb4a2da96629a4d37be1ba2f47bf6b1f557c345f222f82c840f97e19fc255206481b7c01e1f4e09efc727be01afa4be391c7ecdff00b57e07fcda57ed33d7affc916f1a1fe82be10f0f699ff0512f873acf853c31e34ff8276f89be2cea9e09d0fc3de1c7f187ecaff1ff00e08f89fc1de25b4f0869367a1c3addb7867e36f8a3e0cf8fbc1cfa9c16915dcfa17886c6fa4d32e276b21abea1b229e5fa1b5df0cffc14f3e3f7823e227c2ff017fc13c74df81361f163e1cf8f7e16dffc4bfdadff0069cf84b69a3785347f881e17d47c27aaebc9f0ebf6759fe3778cfc477ba6d8eaf717565a3c9a978620bfb9856de6d62ce290dc27e4393fd133e90f86f187c3bcca7e1a664f2ee1ee24ca3179ae6b1ce3865e5d87c36033da78ac45758a96791a75d2a1075a34a8b9d7941a87b2f6b7a6bec73ff11f82f17c238bc1d0cee94b1589c8aa6169e1de1f191adedeae13d9469ca0f0e9a7cedc5cb5a69a72e7e4b49fec97fc13dbfe4c1bf61eff00b340fd9a7ff54c782abebfafc30ff827b5f7c4bfd903f6b0b4ff008258f8cff6a493f6acf09f813f610f853f19fc29abf8ca2f0269ff00143e09f893c1be24d3be0d789fe16dc2f8374bd06e757f861e31b24d37c6ff0009adbc61a65ff8efc23a6687e25d1f5cf14f89f4e9747bf8bf7381c8c8e457fbdcdddc9f76ff0033f8f45a28a29005145140051451400514514005145140083a0fa0fe54c9668a08da59e58e1897ef492bac68bf566200fcea8eab7e34bd2ef75168da55b2b596e0c6a096711c64ed00027938ce39c6715f9e7e31f8a5e29f16dd4ccf7d3d869c5dc41676eed1911e4ffac75c32b11f7a38b605390ed2b65ab1ab5952b2b36dad3f2d7afe06f4684ab376768a6b99f6bdf65d763ed9d73e2a782740dc2ef598249141cc5010cf9071b7e6642493fdc0fc7ad7956a9fb4ae850332e97a55d5e60e03b86546e4e4fcff0066c7e05bf1af8c49258b125998e599892cc4f392c7249cfa9a2b95e26a3dacbe5b6bfd7fc13ba183a51f8b9a6fd525f758fa4aff00f692f11cbb8586956d6c32769924463c93d479529181d3f799f7cf35c8ddfc76f883744ecbeb7b51cf1124ddfdd65881e3fd9fc2bc6e8ac9d4a8f79cbefb7e46ca8515ff002ee3a797a7f923d12e7e2b78f6eb3e66bd30c93f7100c67b0dece7f327f3ac897c79e319beff00882f8f249c7923affdb2e07b77ae4a8a5cd2fe697deffae8bee2d420b6843ff008ff0091d1378bbc52ff007b5ed44fae250a7f02aa08ff0022a3ff0084a7c4bc7fc4fb54e0ff00cfcb0fcf0067dfbd60d14b56d5dc9f4ddff9a5fd21f2c7f961ff008047ff00913f2cff00e0ae1fb48cdf083c25fb185ddc69975f19bc5b6bfb74fc1af8b3e1dfd9b27d76c7448fe38e8bf01f4cf1578d7c4f0ea7e20d5a3b8d27c2fa37c38bfbaf09fc4083c43aed95ee89178cb48f07e97776e5f528a583c9bc61fb717fc1057f6b6f15c1e39fdbfbe0578ebe0c7c4e366d6d77ac7c7ffd9a3e2a687e3d8f646ccba7da7ed3ff00b31c9e26d3fc49a2d8bb4aba6dd4fe39811ad923f2f4db00cd1a7e767edadf12a7f8e5ff00050afda0fc4335c3dd7857f662d2fc3bfb24fc34b53217b3b2d721d3748f89dfb406bd6d1fdc1a8eb3e34f11f873c1d7774a04a74ff02c36458c5b96bce34296689ae764d2ac41553cb491c464be49f97705230304107fc7fb0bc2ff00a33e5dc79c09956739b6779865399679571789c2c28e1f0d8ec0472853f6387fad60ab3c3d6ad57112c3d4c4a71c6d3a4e857a0a54bda41dff00ce0f1d7e9bf9a7851e2ce7dc2191709e4fc479170ce1b2ec0e673af8ec66579a55cfaa5158bc7470998d1863b09470b84a58cc2e0a546a65356bc71f85c64d6295374a31fd5fb5f8a5ff000694f859a3d7b4ab5f87bf1cf53d38c53e9fe14d4bc1dfb67fed593dcdd22b3db5ada781bc79a7fc47d15e73b0a886f74f8618c8559da30571f999ff000567fdab7e027ed9be2efd8efe21fc21fd907e39fecb5fb247ec6573f122cdff0068fb6f025afc07f12e91ab78df4bf0ed97807c35a6786fe0e6be3e2cfc0bf82da56a3617faf1f1b6bfa7786ec6efc48f63a541a6e871c9a95dea3416e6e103049a48c37de11b140dfef042377e39aeabc15e37d73c0bae0d6f4692095a6b79f4fd5b4cd4a18efb45f10e8b78be56a5a16bfa65cac967aae93a95b17b6bbb3bc8a589d24276e457ea7927d0eb26e1e8d3c5d1e24a79b633015e96372fcab15c3b85c1f0ee271187ad0ab1c3e6f82a78faf5b1782c4283a388a54713837253bce72a6a54e5f87e77fb45739e2673c9df0354e14c9735c3d7cbb35ceb29e2cc4e3b8b72ec2e3284f0f3c7f0ee3a793e030780ccf0729c71386c4623058fe49525eca9c2bf2558fa47c2eb1ff8382f4bfd99ec3f6bbf811f1abf6c3b8fd9626f07a7c47f0c693f10fe30fecf7f1cff0068df16fc1c1a636b169f133c21f09be32fc12f15788f57d0b50f0d27fc24de1af097887e2fc3f1235ed09ada7d3fc3b36a3a9da58cde2fabfed17fb63fed05e15d2f56f1dffc14b7f6c6f891e05f18e8d61add85af80bc65e0bfd9f7c3fe22d035bb58afacfed07e03780fe1f7894d8de584f109ec9fc4103aee78665493cc41f52787bf6daf8e1ff04c4f805f12fe207ec91a0e93f11bf63df88b657de1abef80ff00117c4da941a17fc13bfe3d7c40bd8b4ad23e30783b515d3f5cd5ae3f646d535ad5e4d57c7df08ed6286dbc35adb69faaf83354f0c693adf88e14f843e18f83b49f861f0e3c0df0dec75db7d5ed7c0fe14d17c36baccf7363136ab2e9b688979aabc70dc490dbaea578d717b1dbc523c56f1ce9046ec91a93e6782de1ad0c7f18f1c65be23f02709d7592cb055b055a19761f0987a389c5d7ad1861b0997519d3c362b2dab87a1566aa62b0952542ad0f64ebd5957aa97d37d27bc67c7649e1bf85b9c7837e29f1f50971453cc30f8c83cdb1598e2f1f9765f83c17b7cc31f9d626857c760f3ac3e331586a33a380c7e0e9d6a58c9d6583a5ec69b57be1dfc1afd9efc1a62b79be14c76538f100f14dafc53f02f89bc4fe0afda47c21e3164f2ffe1607833f688d33565f8a369e2d5001b8fed6f12ea9a46b0a0db6afa75d5bbb237f401fb25ffc1553e2bfecbe7c23e16fdb67e22afed0dfb2078ab55d2fc25e06fdbe1f47d37c3bf11be066bfa94f0e9da07823f6e6f08e836d6ba1c7a2dfdccf69a4d87ed27e17d3f4ad0e3d45e093e27e89a626a5378b20fc0ad5fc67e0af0ec6f3f887c6de0cd0208806964d73c5be1ed22344cfde77d4752b6555ef96233ce338a97e0ffed8df092d3c597de09f055dcffb4ee9fe37b2bbf09f8f7e077c17f02f8aff00687b9f1ef86759864d3b56d06f7c33f0df41f14d8caf7565713429fda33db40c8ef0cd32c2eecbfa378cde12f85d9ce4b5b17f5be17e0ace70385b607171c4e5592d0ab0a30b53c357c3ceb6130f3872a51a71706e49284392725563f8efd197e905e3a643c4b85caf1196f1ef899c2f9a63e2f32c3cb039f713e3f055715517b6c751c6aa18ec4d34e5275710e736d28fb4b558c2787aff00e8d104f0dcc10dcdb4d15c5bdc451cf04f04892c33c32a09229a1963668e48a446578e446647460cac4106a5afe7affe0883f1cbe33782dbc6ff00f04f6f8f5f07fe3d7c28b0f857e103f1b3f62593f6898bc3f6ff0015757fd89b57f1749e07d1bc0bf10b4ad23c59e2fd5fc3fe22f81fe3a8a6f04786a1f18ded8f89356f85b7fe04fb6e936377a2dff9dfd0a57f9a55a9fb1ab5697b4a557d9549d3f6b425ed28d5e4938fb4a53b2e7a73b735395bde8352eb63fda7a157dbd0a35bd955a3eda953abec6bc1d3af4bda4233f675a9ddf25587372d485df2cd4a3776b851451599a85145140051451400514514011bc69344d148aaf1c8851d1865591976b291dc32920fb1af9a7c6ffb3dd96ab713ea3e1abafb05c4acd23da480181dd8e588070a4b1eadbe16279732b1663f4c8e83e83f952d44e11a8ad25f3ebf79a53ab3a52e683b796e9faafe99f9b9adfc2af1be84ec2e34796e6352dfbdb5f98103b849021391fdc2e0ff00096ae0ee2caf2cd8addda5cdb30ea278258b1ed9750b9fa1fd2bf57880c0860083c104641fa83c1ac5bbf0e6857e08bbd2aca6ddd7302213dce4c6109c9f526b9a585fe5959766b65f2ff23ae18e6adcf0bf77176bfcacd1f962181e841fa114b5fa377ff083c05a896336890a337f146b1e47d0bc6edff8f572177fb3c7822725a0fb55b1c1c047936fe51cd10ebe8b59fd5aaafe57e8edf9ff0099b2c6d17ba9afb9fe88f84e8afb2ee3f668d11bfe3df58bc8fae3258fe7bbccff001f7ac997f6654e7caf10b8f4de14fe9f6427d7f8fd2a3d855fe47f87f5ff000c5fd6a8ff003fe0ff00e1cf92aad58a2497b671ca408a4bab7490920011b4c8ae493c00149c93c0ef5f4e3feccb7dcecf10275eac8a7d7d215f6fcaa3ff008667d497fe63b1b76c7971fe7ca0fd0934bd95456f71fdde9fe6914b1147fe7e47d1dcfe35bf66eff8267fed81fb55fc35f8a1fb497ecd9f1ebf657f897e3df1a7ed2bfb52f88fe3dfeca1f16d7c4bf0e3e24fc14f8a7ff0d03f10b41d63c11ff0b37c1773e3f6f3ae74bd0747d6346b7f88ff000cf422fa6df5a4f63a9cda3dcd8dd3f27e2efd953fe0a31f04e6b8b4f8abff0004d8fda76e625219f5ff0080179f0d3f69cf0e4f1c48c4dcda47f0e3c5f078d440cb9658b50f05595fa81b65b3490843fd517c5dff00823f7eccff001b7c66bf143c5de09b5f0cfc648c4620f8f1f06bc4be2ef80df1da0302ed81e4f8b3f0775af05f8c75730fcbe5c3e23d475bb401551ad9a31b2a1d2bfe09e7fb6a7c3a81e0f81fff000575fdabb43b15c7d9bc3dfb467803e00fed6da5c2aa7e5864f1278cfe1df83be2a5cc606332ddfc47b9be7da3ccbd72ce5bf6ee10f1dfc4be0ec2e0f03966711af97e028d3c3e0f039ae03098fc3d0a14e1c94e852abc94f1d1a34e1ee53a71c546308a51828a8c52fe61f117e8a3e07f893996619de7bc353c3e779ae26ae2f30ce721cdf33ca31b8cc55797b4ad89c561a9d6a99556c4d5ab2752ad7ab97d4ab52a734ea4db6cfe39354f89773e1db8167e31f817fb5f780f502242da7f8d3f638fda5346b98fc991a2977793f0defa16d92232178a7922ddc7999c8156d3e3068fa94b05ae8ff0d3f699d7afaea610dad8685fb24fed35a95f5cca7a470db2fc2b4f31ce38557dc79c29c57f67717c28ff0082d8f865963d2bf6cdff00827d7c4eb7898ed93e21fec4bf19fc19a8dca646c17371f0ff00f6b79ec237da3e77b6d2910b1252245c28b67c39ff0005c5bb0f6f27c5eff825668cae481a8d9fecf9fb59eb37102f674d3af3f692d32de661ff003ce4bd553fdf15fa843e97fe262a6e32ca382e736bf8af2dce94968acd421c411a775e7169def24d9f884ff676f81d2aaaa473ff001369c17fcb98e7bc34e0fc9ca7c1d2a8d5b4779b6f5d6eee7f2ebf003c25fb6478db50d6bc39e06ff8263fed9bf17be1cfc50f0aeb3e06f1e7877e2afc3dd07f668f00f8b3c25e26d367d3eee0bef13fed05e20f06adac420bc6923bf87c3fa85c5ac6f2b416f24e0455a3fb16fecb1fb3df803e1cebff00b3afed05ff000408f067ed59fb457ec95e3cbcf819f177e33fc34f8a9fb373f873c51aec7a07873e25783acf5dbdf887f11be195ef89fc73a17c2ef885e06d13e23f886cbc2b368dad78bac356d4629926bdb8d2f4ff00e9d64fd92bfe0a75f12e36b2f8cdff00054ad27e1df876fd0c1ac687fb1a7ec79f0ffe15f891ece4541341a57c4ef8f3e3afda6757d1679009047a9e93e1cd3f53b5dcb25b5dc52a861f5f7ecd9fb1cfc0cfd95be1c45f0dbe18e87ae5f5adcf893c49e3af1778c7e2078ab5cf881f12be257c47f1a6a1fdabe34f895f12bc7be25bbbcd7fc63e38f15ea223b8d5b58d4ae0aac56f65a6e9b6ba7e93a7d8585b7e27e217895c4de256614333e208e554b1386a4e8d18e5b80784a6a94a4a4d5494ab57c4d792515183ad899f2a492d158fe9ff0008bc1ce0ef05b24c470ff0854cfaa65f8aaeb1359e739b3cc6b7b649abd251a186c261a1294a75270c3612946552739b4db3f9f9f87df067c0fa2df2ddfc1fff00835e7f66cd135079a37b7d5fe2afc56fd87f46101f2de11335e697e1bf8c3a95b18d246fdd595acce5599d4799b73fa33e08f0e7fc15df5cd1a2f0dfc3ef851ff04cff00d803c137216374d10fc5afda8fc5da1c2e066eb47f09f863c35fb2a7c359352b7064307dbb57d534ddccbe65bce85c57ec35b58d9d98db696b6f6e3fe98c31c7db1c94504f1dc926add7e7e93bde5672ee95afeb76defe67eb129a694529596dcd3726b6d9691eeb48fdc7c31fb2b7ec4c9f00bc75e3df8f3f153e38fc4bfda9bf6a5f8abe18f0df823c73f1c7e27daf85fc3b0699e01f096a9adebbe1ff0085ff0008fe18f81349d13c11f09be1969daf788b59d7e5f0fe916daa6b9afebd7cfac78bfc57e24bfb7b19ed3ee7a28aa330a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2800a28a2803ffd9','Unknown','1970-01-01 00:00:00',1234345678,0,'muh.capp.mobile@gmail.com','EN','TESQ00000003','3','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im11aC5jYXBwLm1vYmlsZUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsImlhdCI6MTQ2NzQ2NzEyMCwidiI6MCwiZCI6eyJwcm92aWRlciI6InBhc3N3b3JkIiwidWlkIjoiNjIyZTU0OGEtMzU0Ny00OWQzLWE3MWUtMGExMmVhYzRhNmQ2In19.2OCdnzeNCPQYV4IDWdHRh1CM8BuOUozSuDErb6-yMZw','2016-12-14 18:50:02'),(113,49111,'Opal4','','Test4','QA_Opal','Briana','ffd8ffe000104a46494600010101006000600000ffdb00430001010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffdb00430101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffc000110800b600f003012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00fe63f4f2469fa6e4292ba65828c47131ff008f487ab6cc9ed92493dc93574c8463e48f907a4517cbdf3829dcf1f4e954b4e3bb4fb0c038feceb1009ce31f6588eee70470380473574e0633c8c63f4033f4fe59e39c9aff00412326e9d3d95a106fdd8e8f9636ff00b753db5e96f33e861756b6ba26d79597e3b7afa122b33b13fbbe028da628f2405c000ece800fcb150e0ab12163c330eb145dba7f074e31ebc9a692e08504eecee0077030067bf27dba0c77ab11b8ced917b67af2083923d79270011919f7af3aad59c5b9d371e5babc79526d5927d34fcfaf4d37bb4baf4ba5f2bdbee1ca8cd1b315872a377faa8b0727201f93924018c1ed9e0669412026dd997387510c5c367e527e4c1ce7af40719a706f94c60aaa16043364003a65b193819c91c9e460629a738238dbb9b91d180e8173f4ebdfd302b8a152a4e72e6dafeeaf713dd6ed47bf72a1afa5bf0d3ba1779dbc247c0e498a204e7e53d107bf1ed4d05b04848f681863e545d0f6c6ce73edfd68607710471d0e78c06048fa76fd7d68e76e00cf2738e73fe7fa55b954ba4a574b57a42ddb5f77bad7d1bf5d15d28d9f9feba3efdbfe0023039188c05c71e547f8754e78edfe34e0720b6c8f3c818862f4c631b3b64f3d7df8a66c38dc013bbf4c7b633d0e7dbf0a940e981edc7aff008f1fa7d69fb4e9cdbb576a31badbad9ddef65d96b62f4b356ed7fcf6b6f6b6aefa0e66254fc91803904451738181fc1c6318f7a6e703858f3c0398a2c67af74cf3edcd3b8c6385006463b91f97b9c7e62875c1c6491c61b00063d71d7ffd78f5ae7957941d9dd5b4574add2fae9e577e6bba2e2ef25cab5bd9ad3cb5d12fbf6bfcd0cdcc33948b24e32618cf61e89c1fa7a1a911ce3858f279ff005316719ebf73e84fb9a6aaf27907f87be47076f5eddcf7ef53c61c1283233c1ec0f4241ce3818cfe1e958bc4be57796fad9f225eb7b6cd7adf4d7577d1a77ed7df457b597a74ef7d4f65fd9cac16ff00e37f8458a211a2e95e29d776fd9e12a4ae969a544cea622a4799a9f19c6587735d37fc142b5795a2f84da19110596ffc57adb94b78533f65b0d3b4e8725235380d7b2f1839f9b90401583fb3b78834df0bf8f7c53af5ec57b7f7d6fe13d33c39a068ba55a5c6a3abeb7af78abc408961a5693a7db4535cdf6a37eda6456f67676914d75732cab1c50b7247ed27863fe08b7e33fda97474f891fb547c42f12fc1af103786750b1f869f0bbc051e85a9dff828eaaeb796fac7c51d4351b4d42d2ff557b98ece4bff0007681320b3b6492d2efc406f434307f1b7d247e91be1478219754c5788dc574728ab9be6984a196e5985a15b33ceb31a78258358aad86ca7030ab8ca985c3ba7275f15ecd50a2aca7514dc62ff004be14c9f31ccf2f5472fc2cb11253956aad4a14e9c23ed21cbcf526a308d497b2b422ddddb456d4fe5449ff653a9c9f2e3e4e3ae428e3fd93d0e33484b061c20e00388e3ce39e73b7a8ec0ff00f5ebd67e3f7c08f89bfb2cfc6bf187c01f8c9676f6be33f095c87b6d52c9651a378af40bb2f2e8be26d11a5546974cd62d02dcdb9e5a093ceb29f1736d2ad794e189f46c91c8f4e4e303bf71df079ed5d3c3fc4b93f1564d96f1070fe6185cd327ce30786cc72ecc307523570f8bc1e2a9c2be1ebd39c55a51a94e719ad134a566ae899c2ad29ce9d5a6e9d4a33952ab46a4792a53a94da5384e3656941ef7dee9c5dacde558e5ae3539595726f4429fba53f2c3044abfc3c64b364763c72735a6073c2a724ff046703e9b78c7ff00aaa9694a5adde56eb3dd5dcb91819ccee8adf5c28ebfa1ad00b9dc73839c103a63dbf4fc2bd2a126e95392bfbdcd26dad3df939bbadefef6fa5fa6e63453f6716f572bcefdf99b9df6eae4fcfbeb71e06cde3e47e17e658d3078f74cfa83c673c0e306bd57e18218ec757ba010fdab5568f1e5c6d85b6b582323053707dc58607ca7b7535e5c170bc7cc46339e464fe20f3d73d38f515ec1f0f5047e19b7940e6eeeb50b9cf39f9ee5d14e71dd631c8edd3be7ee38029fb5e228ceef968e0315593b27ef39e1a826e3aa6dc2bce2b4d137aa6cf138826fea94e17579622ed5d2f869593b6dadfb77dcf4212b60e161e9963e5439ee33f747bf6e3f3a72c8de88181e861848e7e89d30793d78c8e2a00327278ce07e7c8ffeb76e6a453f2ed1eaac7f027f3c0efcd7ee7ede6ea269e96b5ed1d2cbb38ebd3aaf3f3f8e6b45a2e9f2d57dda6db77270ec3e60b1e54b1ff55160e7d7e4ea7ae4f3e873534728da094889206008a2cf20ff00b03e9edf4cd545652482090460e3dfa1c9c63a74e39ef4f8b24e49c609c7fb27b0e9d3be7a7d6b69569fc2ac969bf2db549b69f2ead5fbbbabee44d5acf5df5febbe9f81a6b2391d2324e33fb987a1c83d23c77f4ff1a724cfb7eec6793ff2c6218e9d33183ce793dbe95003df1d7a8e991d0fff00ac71d29ea7a0e0119e703bfb71c9f5ebc7b9ac6ace514dd934bca2f5baf26d5b57e49984a374dc7477baf3f3f97cbad8d1866e47cb167d3c88791dff00e59fd3f5a6ebb70c744d55156300e9f745888a1dd8f22407042738faf5e7ad560cab839ce3a8ef939f41fe4e7b5645e5e5deb9697965e1dd2eeb52325adcdb497c1d2d74b81bca7494b5f5c604ec8c08315ac539561b1883cd7e7fc699e64991e4b8cc667d9a6072bc1ce8d5a7f58c757a58784e7529ca2a941cecea4a7b2853bcdbbdb6d3d6e1ee1be20e28cd28e4fc3592e679ee6b5fdea797e5381c463b1538c5ae69fb1c3d3a9354a2dae7a924a9c6eb9a4ae8e36c10ae9da7939e34fb2183e82d6119e3d31df91ebeb67a824f5c8efc60f24f1e9e9f5fc63b23bf4dd35d37076d3ec04809cef6fb2443ccce3ab701b18e80f3934fc6482339e4918eb91cf7f4eddff9fec146b73d3a774e12e48a941fd9b24b47d62e56b35a3bf73c6a6acb4fe5d34b748f4dff0001763120af50061b19604f2003fe7dcf7a5dec392a77647cd9eadd5b39e33c74c11d48ec2850cbf2e4e492141e70d81d3079ce718e3f2cd2670c39ea0f6040e70dea3a74e3d2b9a57d9ead6cb6764d2e8fbecf64faf5368df4f457dfcaf7eabcff003273216279da0f2cb8e38c139eff007be607d73f8202d81c82013c0073d011c7e3dfde906d52540c96e08fa82011ea3dff000a781f789f97ef019192580f94631d0e7a9e073584972ded1b7bb74adbbd37d7af95bb58b8efd12db4d3b68bae9fd5f714312db8804af62323046067f06e3d38ee33522ae00c77c7e3e9c76fc00e7afbc4a0678cf2307f01c63a0c7038fd7d1e01dbc1c1c8e39f539ce7a633d3904d472be48b7bc924f5f779b9af7f2ef6be8ef7d77d7b6965f7b5fe761fc8c633ce471c601182338fc7fce2a4518f53c67e51820f3eff005269a0741df079fc011edcff005f6a952276ce012abf2f1c724642fa64024e0fe06b094ecafeb16dad5a5f3dddacafbab3d77292beefaa5e7d2fbdf4b5d1c8f8b2faf2deca0b2d2a369b57d5eeedb4cd36dd18ac93de5eceb6d6d6f115e7cdb89e586de3c73ba65e7b57f55ff0b3fe083ffb38c5f07bc1765f107c59f19a3f8c975e11d2e4f1ff008c342f1e86d29bc5f7b68b7baac5a3f847c41a3eb9a0e9ba7e917739d2ec92da18a7960b049e69bcc9e5cff3a7fb2df8122f8abfb6b7ec9ff0de7b76b8b5d5be33783f54d460f2c3a3695e19bc7f16ea4d286232a2df41da46197cb77cf615fe82f1cacc371f999d8bb67a96624f3f9f4e2bfc29fda9bf495f13b803c45f0d7807c33e3ce22e06784c8f1dc579ed7e1cc67d4b139857c663a197e4f4719271a91ad83c32c16695561a71a94aa549d19d48feea07edde16f0d65599e0333cc335cbe863af88a581c347111e785250a3ed6bd4859a6a553dad15cc9de2a164df333f927fda23fe087ffb4bfc2bb7bff11fecff00e31d0ff68ff0dd924d707c1bae5bd97c3ef8b915ba0690c5a74a2697c11e2fbb551e5c70a5c7862f2e9f1e5db3bb6c1f8b1e27d4b53f096b37fe0af117857c53e1cf89763a847a2cdf0dfc45e1fd4f47f1b43ad5c4a96f6ba7b7876f2de1d46796e6e248e2b4fb3453477ef346b6724cb2abd7fa42928dc1032dd07600f5e9eabd7d0fd703c7fc69f003e097c45f1df813e28f8ebe177823c57f123e195ec97fe00f1ceb1a159dd78a3c2b752412405b4cd58aadd08e2495a482dee5ee2dad27c5d5a450dd2accbf82783bfb577c65e0ecbb139478ad94e07c51a30cbb150ca73fc2ac3641c4943308517f51866ea846395e6584ad5e346189c552c3e131b86a752a578d3c5ce0a8cbe8339f09f29c5d5857ca315572c93a90f6f86aa9e270d2a5ccbda3a0e4fdad3ab185dc20e72a72b28c9c7593fc9bff0082517fc130a0fd9cb40d3be3f7c7dd3e0d67f68cf17dbc3abe97a05f84bad37e09e9b7b68638b4bb081b7c127c40b9b39da2f116ba809d1d659742d19e24fb7dd5dfee4a471a26081d074c8c1278f7f5e3a71d6aba90b851cf5f5f7ea7be475efd726a40e5bbff0074903a67b771f89ed5fe75f895e26f17f8bfc6f9cf881c7f9acf38e23ceb1129cf7582caf07cee787ca328c349b860f2dc0a9b852a316a5527cd89c4ceae2aad4a8ff4acaf2bc1e4b81c3e5d80a7ecf0f87828abaf7eacb4e6ad5a4be3a951ae694ac92f860a314a2bf22bfe0afdff0004f8d3ff006cdf80f77e32f03e950c7fb467c17d3350f107c35d4e0882ddf8b744814df6bdf0cb519150bdcdb6b30c6f75e1df3377d83c450dbf93b61bfbc57fe1a2d6e564b4925bb57b3b8b5696dafedaf01b59ecaf2d4b45776d710ca11e09e199248a689c078a4478c80ca457fa8a973b400c377041079071c73d723ffaf5f9883fe08e9fb005efc6cf1d7c78f137c178bc6de2df883e21bbf156a9e1ef17eb7a86a7f0e34cd6b517136a575a1f8120363a341f6fbd336a1326a2ba9469777370f0471ab841fd8ff44cfa64d1f02b86b88b8338e707c41c45c33421fda9c1187c969d1c56332fcc313592ccf23e6c662b0d470f96621ce398e12a54ace9e13111cc29b83862f0f4e9fc9714709d4cdf19431d97cf0f471156f473055e4e9d3a908c2d4b12b929ce53ad0b2a328a49d484a12949469bbff041e11b5d43c466cb48f09689e22f1aeafe5c6874ff0006f8735df165e34f2e64319b7d074fbe915897200755c93d78aed7c6de06f885f0caef44b4f8a5f0e3e217c31bcf13d9cfa8f872d3e20f8375ff0007cdaed85bcb1c17379a547ae58d99bbb7b69a4862b8784130bc88b201bd73fe967e08f86df0fbe196956fe1ff0086fe08f08fc3fd16da258edb49f05f86b47f0c5822a0c7fa8d1acecd1db6e32f26f918f258935f82ff00f071bfc2eb2f127ec89f0ffe2a0b54975cf855f18f42b78f506567ba8741f1ed85f68ba9da89b692b6d26a36ba34f2239f2ccd0230f9f19fe9ef0ebf68d62b8ebc56e07e058f8634321e1ae2acf30fc3cf37c6f11bc6e7385af8ca73a39556781c365f4b02957cc3ea986c452589ad2853c44a54e53704a5f398ee06a995e4b8bc77f68c3115f038555feaf0c338529d3a2a0eb47daceaba9cca92a9383f671bb8a5caaed3fe42e47091bb9380a1df767b052704ff00757191d7bfb1af72f06dbfd9fc31a1a0e0ff0067c4eeb907994994f3df25fafbf7af9fe5767d25dc677cb69b54f5399502e7db24f1dce09f7afa6b4cb75b4d36c61007ee6d2de2c67a1485548ebd7827a7538e95fec9786b4d56cc730c426daa382c34135b3789af3a9f735854eeba7a69f8ff00104935844edaaa93dfca11d53db776ff00825f271ea7be3d073f8ff918a0966e9c0247d48ebd40ce7ebd4d27f1648dc3939c71ea73c74ee3a7f3a72138ec3f23c750071c71fd3d6bf62d6525a3b5edeeebd975b2bbef74ad6ec7cc35177b595badf57a6cbafe7aec2e429039e73bba75efe9ea73f5e01a50d87519cf6f4208f5c6412327fc7d427e51fcfd7d80ebc7ea695368c9e3af3c753c80477078ff0011c5689eb6d62ef6bef6764d35d3cacaf7e9a9935e6df5b5f4fc7f5f3b166363d598e49e33d157b67b0fc7d7156324b819030572c4f2a338c9c7240cf2074c55001bef124631c63a9c9c0ff0cff2c566eb9aaae93a5df6a2572b696d2c9b33cbb2a8c27be58ede3d739c915cf89ab0c350af56ac9429d183a939ceda421172949b7a3518df5dd3d3d62d272b6ede892577ba4979796bf75cc3f1eeb66df48bbd3b4db877d5afd4dbda5b5a2bcf7b2c47fd7cd1430ab4a8ab087c4acb80e40192715eb9a2c7603c276fa5e9334b6b649a38b4826b67315d41fe8982fbc8f322ba572cce5c6f12eedeb9e2bce7c171e99e15d3ad75ff00165ca2f8bbc56df69589926babe8ed5944b6ba469b6d124b388ada22b25cf9318469dff784ed415bb73aae9f2cb36a0343f1669cde5caad791695730c772bb080d3c103bbc91b7f7a6b5f302f20a8e9fe5478f3e23cfc54e25a34f2cc163a8e45c3d5ab61b2cc5fbd5286655e15a30a98cfabcdc62939c254e94a97b56a1772a779b4bfd9dfa26783d1f02f85719c49c4d9be45578b3c42c932f799f0cceb3c2e6fc3191d5a53c6e130d571d0e7961f198ca18ca38accb075e1868c2a43091857955a29be5b4ecae9da703c03a7d80c9e09ff4587e61db1db3fd49ab0caa4e47181cf3d3be739f7c556b276fecfd3558640d36c0a939c853691e39e8467f0c9f5cd4eec36118e0edce3f8bdbdb3c723ebcf35fec328cbd9d3beca9c3aedeec6cd3b27d5bf96da1fe2bd3bab3beafbeb6bf5fbba5c7930796dbd646939dacaca17248fbe30491b78c0c1ce39c55768c1c91cf6c039e339ec3fc7eb4e390a571b73c60f5c67d71819fa8ca9c1e28c85c6cc8528aadec470474f618fafbd727b351936d36db495ddd3da5a35aa4df4bebbe9d37567d5dbcacfb69f9eabfc8452c361c6481d78c673cfe278c67f119a97e6e011ce3eeff003c74a674ce07dd39231907a7a74f5c7e3d69464b124e7e551d727a77fc862b0a8fde92d9df5b76b2db4b6faa5d2c6d15b593be9decd69f7bfb95fa0e1cf43ce718c74e9eddbfcfb5807214e3839e9ce36f073f51c8e6a155cfb7f3e73fd7f3a9a456582464273e5be0f753b1bd7be403f5fc6b9eac9c22e56da29ee9eadae8baa5aad1dbb3eba456a93efaf4bf7f4b956f354d334e8da4bed42cec93bbdd5cc36ebcfa195d3819ed93ec4e019edb56b6bb845d5ac3ab5dda00585d59681afdf5990c301c4f69a64f03871c8657618e738afe843fe081ff00b3a7ecf7e3ef843f12fe34f8d7e18f837c77f19744f8d5adf8523f1678d748b2f14ea1e1af0fd97873c39a968da77876cf598af2c343491efef2ee7bdb3b38afef2690f9976f1c31c69fd2bc1a5585bc22deded2d60823c08e182da18608c6dc054862448c051800050380719c1aff0018fc7bfdac51f0bbc4ce2ff0d786fc19c5e7f5b8333ac5e45996719df14d1c96862b1b8270556797e0f0b80c7d69615ddba35b11568ceac2d374631945bfd7b22f0aaa66b95e0f33c46734b0cb1d87a789a542961655e54e9d4578aa939548479ad66d454acdb57d19fc4eff00c11fbc1571e3eff82917c38d6e0b4be934cf867e03f88be34bcb8b8d3b50b58edae64f0f9f0c695b9ef2da00b2c979af9f28121be4902062840feda221800739c11eb8c739c75e73c67e9dcd322d2ec6d99a68ad2d639b66d69a2b68229dd40fbad2c68b232eef98a1620900e320624008e98c92c08f71c9f4c75ec7a8fcbfc6cfa51fd20b32fa4c78ab2f13b1fc3b0e13e6e1eca787a86454b33966f0c2d1cb6b63b133aab1af0b82e7fac62b1f5a6a3f578da2a09b6ee7ecbc2bc3d4f867295964312f18feb35b135312e97b1f692aaa118af67cd3b7253a708def67d12d4940e87b0c76fcc73f4efe9cd05b03af19eff8fd7f4a5079c753b7bfa74ce7a7b7e94a70703af2003d3b93efdfbfe9ebfcf6949eda6aaefcefa7fc1eabccfa3be89abbbe96f9ad7e5f927b90b1f981c903f3c7b77f6f5f5a729c7de3c673d7d4f4eddc73f9d398751904e00e99e9f8743ce3ae31c7bb31b41e79f5c7e03d47ff0058d4b6d764bfcecde9f7f9edd4bfcb4be9d15b66ff00af55a93ab3649047dd38c838071d48047e20107838c55f590b11d31b7071fdecfe19e075f71592a4a907d4639edc647f3231ff00eaab092e796e99c803201f7fae7dba7b57550c472a49f4d5475e8d3bf6578efad9dbd49924f45aa565f75b7d57afe3d8d091c1fe2e738f4078039271d4e4fa6319f6fc94ff0082dce849af7fc136bf6810d1195b444f04788a12155bcb9348f1ae8b29973d808e49158f042938c64e7f5809dc0e40edd0e3a0f4ebdc67d47e75f347ed79fb3ec7fb54fece9f153f67f6f144de091f12fc39fd889e29834b8b5b6d1a64bdb5d421bafecb9aeac92f2132d9a43345f6b81c4523bc52798ab5f67c039fe1b877c41f0ff8931b5de1703c3fc73c219e63b12a356afb0cbf2ccff2dc6e3710e950854ad51d1c1d0ab5254e953a956718b54a9ce568be2cc684f1396e6185a71e69e23018ca108b695e7570d529c23ab4af29ca2bb2dfa1fe7036a8f2d9e8f0af26eafb4a8082d8ca3dec0ac3031fc20f1ce3a57d4e06d04633b72067b741b71c0e471c703d2bef1f8cff00f0434fdb8fe0d3d96ade08d3bc13fb46786345d422d4263f0eb547f0f78e24b4b27794aa781fc60f65f6eba3c1f2346d76fe466cac70b9c67e0ad57fb47c3be21d43c1be2dd0fc41e07f1b69326cd57c1be34d1751f0c789f4f7e57fd2347d5eded6e8c4cca7cbb98525b59bef4533af27feaffe8e5e337855e26e5b9857e03e3fe18e27ad2fa9b9e0f2bcd70d3cce853a543e2c46595274f31a0954ad24d56c3439657e6b6c7f29713e5b98e07154238ec162b0aa146304ebd29c2939a94f9946ab5ece4de8ef1a8ef656d9126f23827ab76feee3d401e98fe5d69e8d9cf0091ea3b7b7a74ef557710b9603fbdeb8c606073f8e78e719a746ff002923386393ea38e077e99afea68c92495ddd59bd1a77b2ef6baefb6faebb7cb72ddb6fcadbedd7b7f5f796c03b97b0f6fe67d3fa8cf15232e181e38ea0756ebc8fe7ee077e86089ceff9b91b48e41e33d3047f2e7dc53d8ee19cf232303248046467be703903d876ae98c968ac9a7cb676e968d96bd7995d6c66d4e4d72592f5f44fa7dfd5fc870e58ee395193e9cf618c727f84123ebd6b8af196a70fd90787a1b59755d63c409259e9da55b9c492b3801ee679307ecd696a3324d3b80a154e03356ceb5ad59e83a65cea776c7c98554aa202d2cf339d905bc2a3e669a695951000492d9adcf87de109ecbed1e27d75049e24d6915e5f330cba2e9ed87b7d1ad73f70440ab5dbaf371719c80a8a2bf02f1f7c60cbbc30e199d28428e3388739855c3e5997d47cd15094546ae331114eeb0f494f95276f6b39282d2e7f44fd1afe8fd9e78ffc794b21c14eae5dc3994aa19871767f0a77596e0255396187c373da15334cc9d2ab470145de3170af89acbd8e1e69e8f84bc24fa543637bac4a9a978823d2ecf4d96ff69d96d6d6d12aad9d82b96686dc11999c1f36ea50659491b553b990aa472b1c7cb0cdcf18188dc649f4c64fd31daa62a31c02303db0727b8fd73e879c62b8ef1cdcdcc3e1cbcb7b27f2af353316956b264e639350716ed2ae31f3471349203c74eb818aff0024557c567399d1556a454f115e9d38ab2851a109d44ad4e9c57252a54f99cb92092493eb76ff00e84259664be1bf06e672c160eb4f0d93e5589c5579b94b1399e715f0b848af6b8bc5d4e6ab8ccc318e9c28caad79b729b8c22a34e318c7ceecb73e9da796c13fd9b63c0181816b0e147a0f4cf4c7b66a760369cf18031e9c7bf1f4a6e9e00d334ccff169d61cf391fe8b103d382493dfa8e7a75924f9fae7393903e503d3dfafe3eb9eff00f4229f352a49a4d2a705d2d7e58b7a2decddbeef43fe55693d23657d2dae9b2fc36216e07de040c123f0c8009feee727a5293f424fcb8e4739fe7f91e4714aab8000c03b8e411b870a029f6047ae738e99a72862391ca8c923923939c9f4231c9fe95cf3df6b6b6d1eef4b6cbb3d55be4ce952b2d52bbeff0095bafde21272148c1ce48ce7a751f4fd0fd73537539ee703000f603b0e7031efd49e6a2e3249ebf78f72074fae38ff0026a55c2a863f285fae7938e7f1e07e99ea78e4f979a575d6f27f2b28ff00c1ebb9a26d6fa5de9fa2f25e5f912aae0f1919e33ce78e2ac852e42f63d463af1d33f9fd6a0400b7d391c7f9c7e1f9e6ae2a8ce4fd4609e7db3fa7a63df35e7d76945eaadd5256972bb3b276b26d6b7e9a6c6ba25d55b5f47a6fb69a79f9f67fb6bff06f9fc4f6f0d7c5dfda73e015f5cf950f8ab45f09fc5ff0edab9e64bad02797c27e2516ebdd859ea5a14f3ed032a80b676823faaf43f2a923209cfd30704f6c9f4e6bf849ff008275789be29685fb7afc1ed77e02f8035cf8afe27d320d6f46f893e18d1278b4cd32c7e19f8a2c24d375ad57c51e2bbd56d1bc3367a7dda69faa594faa92f7b7ba6c565a75bdd5d4be5d7f763016f2d03ed2e5177633f7bbedc8c91bb8048f9800c40ce07fca6fed2ce06c3707fd2af8a736cbb1580ad81e3bca728e259d1c2e3b0b5b1382cce8e1a19363e8e37054aacb158475a196e171b4aae2a9c29e32589aff00569d4786c4aa7fd3fe19639e33857094a6a6a79757c4609b9c64a33a6aa7b6a3ecdb4a3350a75bd8c945be59526a566ec587c639c673f903ce71f4fe75598f20f72739c7a641fc0f1c7f92f932dce33f9f03a71f8ff5fa54614f1df6fdecf18cf1919f73ef9e9debf80e6f99d9df5b6ba795ede97fcfb1fa32564b7e9d5b7e97eb6fb89371c8001e700e3a1f4fa9f6f5f53d5d9c1e30a5723a81c81c820f4c1e3fd93f5c5676a36b6fa8e9f7ba7dd7986d751b4bab0b858a46867305dc325bcc619a32af14be5c8de5ca84189f6ba90ca08f9bbf66cd27f69ef8dfa2de7c39f855a37864787fe1a6bda97c3dd63f6b1f8853dcea3f0e3c476fe1d956d6ce4f879e12d1aeedbc43f13bc6d6562d6fa678bf3a9787fc13a3789f4fbfb79fc4fa9ca5ac53ef3c3cf0d38dfc56cebfd59e01c9311c459e2742ad4c061e50a4b0b81adcf0ab9a633175a50c361b0584ad0a34f1152a4d54be2687b0a755b92879d9a66d96e4782963f35c652c0e129b71f6d59fbb2a9ba8422af2a9566949c694136f57b2d7d73c63e3e8bc21af7c3ad1ae2c4dcc5f103c5575e144bd5b8119d2efa3f0feadaf594ad6fe53fdaa2bb3a44d6720f362303491cb990652bbe12a3e0e49e0703a827a1fcf8ff22bbdbaff008262daf8aa7f0ceabf10ff006aefda275fd7bc2baa7f6f68f79e15b5f845f0fb46d3f5e934dbcd2a4bdb3d12c7e1d6b72f909697f791db5a6a5ab6a9e52cb99269e5512d79bfc55fd8aff6bbf869a0eb3e20fd9efe3a683f1eaf2c34ed42e6cbe157c7ff0006e81e14f10eaf3456d23dbdb7873e2cfc34b7d034b4d65a503ec565e2ef0449a5ea53886d2ef5ad21257bd8ff00aa73afd9e3f48ecb326a5986172ee0fcd7174b0b52a6372bcb78a154cc2751623113a71c3471382a38594e385fab4269d7a09548546f9a4b9a7f0986f18381b13898e1febd8cc3a94a31a788af80ad4f0f2949415e5513972479db49c9256b3bc7621d275ed1bc410dcdce8ba9d8ead6f67a85f691757361711dd456faa6953b5a6a5a7cb2465916f2c2ea37b7bb8490d04ead1b80c3145f788745d3353d1f45d4354b2b3d5fc44d7c9a0e9f7132c775abc9a65b0bcd4134f46c7da24b2b43f6ab88d4ef58034bb595188f0cfd9824f0dafc1bf0dd97876e757b89ec6e35bb6f1943e25b0fec8f1769df121b59bcbaf885a678cb44dcefa2789ac7c5575a947a9694cce96c041f669ae2cdadae2593c7df0efc6dfb4bfc48f06fc04f8437b0786bc6de14bff0ff00c5bf14fc64bad3d756d2fe05f872c750b8b2b1bd4d29a5861f1278d7e2046bac786bc33e0f9ee2deceef4c3aceb7aec8ba369cc975fca7c21e1d713f1cf88985f0d38672bcc31bc438fce71f92e1f0b570d2c2e230b3c0bc4c3118bcd286223096030997fb0962f3675946a61b0b4eb4211962152a73fbccc335c0e5996d6cdb1b5e9d2c0e1e82c4cebb9a941d39469ca3ec9ad2acaaa9c634943f8b26ada5edee7abea874bd2b52d48209174fb0bdbe6de1fc961676b2dc324aea32aade5ed2dd70c08e831c9fc27f1ec9f11be1e7837c7171a6b68d73e2bf0fe9dae3e96ced2fd93edf02ce2159245492440aebb19d43118c806bec1d1ffe0993fb34ddd814f8ad07c46f8f5adcf6ef16a7af7c54f89be33963be79d0c7702dbc27e0ed5bc27e0ad12d1c3388ec349f0fc315ba1d9e64a72cdc678b7fe09bda5f823489eeff0064bf889e25f855ab69d605347f861e3ed7f5ef899f02f5696de3c59e952d87892eb53f1bf806da4545b54d4bc15e235b7d39192697c39a94507d99ff00b6334fd9b1e3765bc2f5735c167dc179e7125374b10f86f078cc5d0942851a189f6f83c2e678aa31c2633175aad4a2e973d1c3c25f56508ca9baf267e6186f1af842b635616a51cd70986949456635b0f095252e68a4e74a9d4956a74ad77cdcb271bb6d3b58f3a92689b00ed1b881d3240039f42704f7e39c8c66bf133fe0ba5a4fc018bf636d7fc4bf12bc3ba0dffc571ad685e1ef80de22fb3db43e34d33c6b797f0cf709a46acbb7507d060f0f41ab5cf8934b9649f4d92c514c96c2e5ada44fb57e0f789ae7c16bf1d7c67fb43f880f843e2f781f53b9d3fe397873c4532e95e1bf841a5f85ec24d4b4cd2f40964b99ecf52f06dc68b723c49a4f8d9242fe30b5d4e3bfdb6d26cd32d3f912fdbd7f6c4d67f6f5fda16e3c5f6ad7f63f03fe1cfdafc3ff0007fc3b746483ed5606553a8f8cf53b46f9575df184f14573b597ced3b41874fd389129b927e53e85df478f113c41fa4de5396e05e71c2b1f09b8872fce78f788f04b1d95d7cbebe555a9575c2f0c4c561eb622ae798ca157012a7512a388c8e38dcc395d3961a9d5fa4e39cff2ac1f0b5594de1f1cf38c2ca965b4a5eceb46a2af08b58c4aeed1c3d39aabcd1f7bdab8524d4e528af90349372747b037659ae1a053231077f3f7377392fb71bce79c67ae4d6bc60850b9c676e4fd39e3dfb73c7b53dd7087838055463181d085039381f97d29832001d7839e3a71c0edf9e7f1affab6a50718421394a52a70a70e66eedca2a2b99b6b776bc9f7d76dbf96b7d7bbe6bfdcede9a6c4ca483eb9ca8fa7f8e0ff009e9522a90a4f2793e99ef9cfa9edefd29aab9519fe151e9d4f5ebd7079e33ef81cd45a8de45a6e997b7f39022b2b59ee246da0710c6d2119eb92571c6735ad7a90a34655aab51851a72a8e52ba518c23cd394af65ca92bdefd343387327d5eb6b79dd68d7eaedd13f3e634ed29fc67e35b749173e1ef06490de5cc655bcbd43c412a16b3b704fc9247a645fe9532f389de153df1f4301b46074cfb1e7b74f6e3d3ad79d7c28d265d33c23a7dc5d2b7f686b8f71afea0ce3e7373abb9b9da7392be4c060800fe111ed1c0af490b919c93c838e7a1241fa73fe7bd7f8bde3471e62fc40e3ecef37ad55cb0387c555cbf2aa4dde34701849ba34b97b3aae0eacdf594b7b247fd1b7d107c24c1f843e08f0d539e1953e24e2dc26138ab89eb4e295778dcd30b4eb60f012f754a34f2ccbe787c1aa3f02c42c4d64b9ebcdbc0d53569ec8882d34cbdd4e570582db18218a318c0f36e2e648e35cf2422f98e40276e3af03aedef8a2fc69c7fe10eb8f22c6ffedf3b43ace9b3486382de754291168773ac8eac54b0501490dd2bd49e2009791951143333390151141396627000009624e029e4f5c720d01f15c33dd4d2cd65e15b68ae64c296b7975cf2a393fd22571b2483480c0186352b2df6ddec560281be2b28af86a2e156782a0e345a55b175aae29b8caaa94630a54a8d5a719d69ae6546928c9c9c5ca528c6329c7ef38f2966d9853c460e9711e328d4c77ef303906032fca14274b03530f88ad89c7e3730a388787cbb0d529c258dc5ca74a9421385184255ea5284b81b06ff00897e9a4a90069d6007a605ac58c0e41cfbe30300d4aca4927a17cfe80639e9df8e9f9744d391ce9960fb1b09a6e9fb8e3eee6d601ce339c9f7a9df8f61d73dfa64633dcf7fc715ff0041114a34a9c52b3f654d5d7f822df4f25bf75f3ff979a5b47ae8baedb6fe9b7e9d08064707a8cf38c723b7e7dfa5260ff09e48ea0639ce4afd393db8a907209e83e5cff2eff89f5071e9526d5ee30338efec3271f5e3fa66b96a7bcb47ae8add76d5b76dd5f75d3a58e9b5f4f3fbada9110077c8e40c76c0e3a63dbfcf1526d1c71db04763fe3f8e7f4a4da32413f7781e8413c1fc7a1c64922a58f93d4719c0fa8f7ef8033fcb9ae39eb64d7d949df7d93d7d13e96d6fad8da3af9db44dab7677d7f3f99222639009da08f973923bf1cf4c7a1feb5dbfc1ef839f14ff006a3f8c1e1afd9efe06588baf1af8915efb5ff115d43237877e1df83a09a18b58f19f8a2e2318834ed3a39d63b6b447179ac6a72db69560af713b345c34cd30827f217f7a2373103fc5204e31ee5b1818c1ed5fd417fc101fe1e7c2bd13f65cf157c4dd0b52d3b5af8c7f107e21eb967f1866fdd9d77c2717862fae2cbc0fe04ba84b35cd9e99168ee7c516a488edf57bad72e2f62f33ece043fc37f4f3fa47667f46bf0431bc55c3f96cb1dc51c439961f85386b115b0d52b65995e699a51af5166d9a5487b91c3e070d86af5a951ad3a6b1d8c586c0c671789e68fd9f0470e438973ba783ad5553c2d0a32c5e2b95b55aa52a53a5174686a9aa9527522b9d5dd3a4aad4516e1a7e95fec6bfb17fc23fd89fe11e9ff0c7e17d849737d76d0eabe3df1f6ad1c5278bbe2478b1e255bcf11788ef513708d58343a2e8d0b0d3740d3845636516e13cf3fd8023c63193c70727bfd48fafe47bd37730650b8c06f9c3673b71ce074c9e3af1ef53e719ea3fba391819ed9c024718e0fa7b9ff937ceb39ce78a33dcdb89f89736c7e7dc459e636a6619be759956788c76618cad69d5af56a4b48abbe4a5420a1468528c68d18429c125fd5387c251c150a385c2d1a587c350a71a5468524a30a54e2ad1828a49596b793bca526e526e5293719dc323d78e33d371c75fc3383cf1d6a95f2dd9b5bafb0b411de9b7996cdeed2592d12efcb7fb335dc70324f25b09b619d217499a20e236590a9179c1ea0f61c9ec4703dfd3b537976031c773f4e41f7eb8e83f015e6c95d24af77a3d2fbb5a6ba376b2b3bf9a6b53b237df7564badf4eff0089f236b5e29f8a1f11b56f86bfb3b78a3c0de28f85de31f8dff10f46f861aafc41f0a5dda6bde054f094f6f7fadf8f35ff000478b2daea3d4f49d56efc19a1eb1068761e28d1749d634abfbe89ff00d325b449e6fe847c1de0ef0c7807c2de1ef04f82f44d3fc33e10f0968da7f87fc33e1fd2e04b6d3b45d134c816dec6c6d22455502289774b260cb7170d25ccef24f2c8edf8abe35d4a2f097c4ffd987e235fb08b45f01fed13e0c93c4575b7f77a7e89e37d335ff86f2ea570e182c369637fe2cd3a6bb9dc148adfcc77daaa48fdcd898edc31f983149071f2b29c11c1e70c3008e31d0e2bfdc6fd9b583e17ff008841c5f9f65595e0701c439871fe372ccfe742b56af516172cc9b23c4e5386a753153ad8bc3e5ea198e2f1543093af5e946bd7c4cd549b93e5fe63f1bebe3659de5384ab566f014f2b8e270d4edcb4beb157115a9e22a4945a8cab5a9518f3db9b95b5a5cf33f8c1f1b3e0f7ecfbe0bb8f88df1cbe28781be107802d351d3749baf1a7c44f12e9be14f0d43a9eaf702d74bd3a4d53559a0b7fb66a1704c56b6e19a490abb0511a3b0ee349d6746f1368fa67883c3baae9baf683ae69f6baae8dae68b7f6baa691ac6957f025cd8ea5a5ea56324f677fa7dedb4b1cf6b796b3cb04f0ba491395209f95ff006e0fd847f678ff008288fc1197f67dfda6742f10ebbf0f5bc4ba378c2d4f84fc4b79e12f11693e23d07ed0b63a8699ac5a4770232d6b7b7b65776f7369756d736b752a344b288e54f75f847f0a3c0bf01be167c3bf827f0bf46ff8473e1bfc27f07683e01f03e826eee2fdb4bf0cf86ac22d3f4ab59750bd925bbbeb85822f32e6f2e2469eeae2496790e5c81fe84aa95dd49b928aa69c5d092779caea1cdcd16ef1b3e74934928c6124db9b50fc61c28fb2ba94fdab9353a6e36a6a2dc95f9bbe90b5af7f692d3dcbbfca3fda0fc291fc25fdb76eaef4b8c58785ff006a0f86571e35beb385156cdfe2efc27bdd37c3fe25d591542ac57fe25f016b1e1b9f537001beb9f0f0b990b4e5d8fd4fff0004ddf0ddb8f81fad7c60bb858f897e3f7c46f19f8f755b8930f2c5a1e87ad5ef80fc03a3452633f61d1fc2be1ab696da3c855bad4f51b80a1ae1f3e17fb6f6afa66bff00b54fece3e19b6b859752f86ff0e7e30fc4af13c718ddfd9da278affe11ef04f8796e9d72d036ab7d65adcd6b09f9a68f47b97553e52b1f12fd94ff00e0a0fe0df837f0a6cbe055bfc10fdaa7e2bea9f0ebc4fe36d2740f12fc3af83574fe02d7fc1ba8f8ab56f11f86352b4f1ff8e35af05f872568b4ed686957b6b05c4b3c17ba54e0a18e48647ff3db822a7863c0df4e2fa4067999e6592e4356af873c2d8f86619a6270b96e5d80cd73ac4602af1161e9e2f132a184a58ec6d1c1e5189f63cff58af4678e9c54a11c437fb36631e20ce3c27e13c2e170f8ac6c966d8aa152961e9d5ad5ab613092c5470329429a7274a97bd18ca4b96f0a76d923f7c51ba600ce3a6739249efeb8c7538fd2be0ff827ff00051bfd9abe3ffed83fb46fec39f0f751f17cdf1d3f65db2d3eff00e25c1ab785ae34cf0a4f0ddbd941789e19d79ee5ceab2e8d75a969f6ba81b8b2d3e29a4b912e972ea10452c8337e0d7fc141fe17fc4ef895e1df847e29f007c64f813f10bc6b1ea87c03a57c64f0b68961a2f8fee746b27d4f51d17c29e34f06f89fc65e14b9f14db69b05d6a63c29a9ea9a5ebb7961677975a6da5fa5b4fb3ed0d2fc11e01d17c4baf78e745f05783b48f1b78b6dac6d3c57e33d2fc33a269de2bf14db698a46996be23f1259d8c3aceb96da7292b6306a779771da83b61098c0fef7c833fc9f8a30185ce786f3aca33fc93132af1866593e370f9960ebd4a7250e4a58bc24ab5072a159b8d6a7cfcf16e3cdcbac5fe3f8bc06232dad530b9960b1783c5aa7070a55e9ca8548b9aba9ca9d5516e2ed64d2b5eeaf7b5bf8f4ff0083b47c0facf86343fd99fe23f82fc4bab784ec3e3578835ff843f1b749d108b7b3f887a778174aff0084dbe1d4be242b817cde1d9e7d6ad6de29f7c535acf6b14e268eca08d3f935d17488748d3ace182492481608d50c84b37203167624b3cb2162d2cacc59c9eca028fe9d3fe0ec6fda8bc25e24f8adfb2d7ec85a5dfc32ebff000dacbc49f1c7c7ecb3ab45a4cfe35b383c29e09d22e9103325f4fa3db6b7af3a48432595cd94a176dc2b57f301ff00094e809042a9a824aa912a29b7b6bdb9cec000e20b772338246719e081dabf68f0732fe1fcaa5c5599d2c3e5796e2334cf6589c5e269ac3616be61898e0b0785c46271734e9cb1388f6b879e1dd7adcd5152a10a5197b28c51ee61658bad83c0c6acabd7f638654e8464ea555430cead4a94a9d35ef7245c2a2a8a31495a5776bb3724c6f0324fe1e9ce3dfd41ebf43cd382f186e0fb719fc39e9f4fc4d7383c49a6bb03147aa4c01e4c7a16b4c391d07fa0a838e3247cbdb3d45557f1b690977258456dadde6a3122b496567a26a335c46adf74cc3c848a0df91813491e41cf22bf6c9677925277a99ae5eb99ad1e32936ef676f764db7e4afbfa1d1f57c4593f615ecda4bf7335aed65eeebae9d7ee3af380471d7d383d47ff00a8fe95cbfc41cffc21baeeddcb1fd9e357d870fe449730a4c79e3fd5330248c1efeb5622bed76e42c96fe1a9e1567caff69ea76364e108c8692180df4a87a610aefe30c14d3eff004ff126aba75e69f7165e1f8edafede6b39d5efb519e411ca8d1b1fdd594232a1b2aca47cc07a57cde7dc4391e3b2bcdb0147318bad8ccb71784c3ca9d3c455e5a988c354a74e4a4a959a5392d53b24eeb43d0c264f9b7b6c3d6596e26a53855a552509537155146709ca2f99aba94572bbbb59bd99ee1a6a22d9d9a424797f668563c7f752250bd88ced03db9e2ae82391c025867271c83c73e80fb76e9ebe3de0bf155c6871d97847c672c56fa85a431da695af10d1693e20b58902c456e66c25aea912288ee2cae5d249b689e02e1c81eb7b81f9970cae03071c8c10082a7b823b8cff0087f8b1c4bc3f9b70e6718dcaf37c356c3e22857a91bd48c946bc54df2d7a536ad384d5a49c6eeeda7697323fe9f3c37f10b857c49e09ca389385333c3e33075b03858e27051928e372ac642943eb39763f0bfc6c1e2707513a53a7522a32e453a529d394272e5fc66d236950d92b3a1d6354d3749919080561bcb854b8c7231ba012c79c93f30e326beb5fd96be18e97f13fe3c7c34f06ea710974086ef51f126ad6040f26f74df0868f75aac3a7ca98c35acf7d0584771191b5e10c8c0ab30af943c516371ab68ecba7b28d42c2eacf55b0563b565bad3a74b8481c91b556e115e02d9f94c81ba57d11fb30fc70d3fe147c5bf037c559ede7baf0f595b6bfa1f8aeca1899f55b0d23c43a44b637b3416adb5daff49ba5b7b87b53b5ee218e510962c9bbe5f8ae9e755382b365c3bcdfdb50cbb3f8e0234a4a15a39b56cb23fd992a6db8f2d6a9521c9839392b578497345ea7e4fe32c733a7c31e2c53cb30f8bc467199f056494b2858653789c464f1cc3174b886965ea16a956b6128e2ea56c652a37a92a38ac0b719fb9cbf7da7fc1293e1b6b5e17f0ede7877e2df8e740bcd43c37e1fbe11eb7e1bd075fd384b77a3595c323369b77a2de084492901824922a6ddc18f07e74f1c7fc12bbe3be82b3cde0df137c3df1f5b465da1820d52f3c2dacdcaa81b512c3c43670d89908e0226ad2293c07c026bedef0aff00c141be1f5a7867c2f6d79f0f3e2b46d65e19f0edab1b5d2bc317a92b5be8d630968fcbf15a3ed7319914ba83b594300d90bd29ff008284fc239626693c27f17a07e46c6f0658ccc090704791e22994aa93b1b0dd73db06bfdef5c47530f185b32e78f2c3ddc4e167256b456ea9425fddeb75addecbfe6470f97f88d41275384f38ab0d34796e2fe16d5ace14deb6b5b46ad6ee7e1df8c7f65afda1bc04b2bf8a3e0e78f6c2de262ad7b6da0dd6b1a70c72cc350d106a368531c87f3b69009cfa7855cda5dd94c6def6de6b4981dad6f7313dbceac09189219d639158104805411824e2bfa311ff000508f83f682393fe116f8d0ae78df67e0150c303b04d7d64209e0150c49ce78c5636b5fb5efecbbe3d8cc5e33f0278a35889f1b97c5df069357241561f3cb247a8cea4e5b949370c8fbac335ac38ca3b55a787aba2f7e8d4ab45abd946d0a94e49f77ef2d37b1ee51a1c59149e2382f8863de54f2dc5cd2db5b3a374d6bd3fc8fe78cab0e083804f3fcfdf8ee053940053001cf04f5e4e0719e067b67b8ed9afd71f1d7c40ff008272decd24775fb3dfc492e5a406ebc0bf0dbe2178703ee7cee8dad357b7b2c9e083f6211818c215c0af9db5f7ff008278ea1e6a5869ff00b5c7816e5d7f72ebe07d47c49688e4f07ecba868525c32a8e0c62fd49c0025cf352f8cb297fc5f6b46564df2a8558eb6df926e7d6fcca2b7f247ab43039d545172e1fcf28c5d95eae59888a8bd346f92ebcef15a9f0f22e0faf031dfe98fc31c73d7e95ed5fb3f7c7ef8bdfb2cfc49b7f8b5f0435c8348d71e282c7c55e17d5d6e2e3c13f11b418a60e742f17e9903a3b853b9f4bd72c5a3d6343b966b8b19f634d04dd16a3e0afd98644dfa2fed09f107472cbbe28bc79fb367c46b38c21ce04d7be1c3abec63951b96c4a8e7e5e2b87bdf087816152da37c7ff847adc48b951711fc47f0d5d3640c2b5b788bc03651c4ea783e6dc04ce46fc0247c671d65de1b7897c319bf06f1ae1b27e20e1dcf30b2c1e699366f414b0f88a32e56acabc3f775e94d46a50af49aab46a463569ca3285d7a982867595e2a8e37094332c162f0d353a75961b114e50778b69de9af764aca5197bb3578caf13faf6fd8a3fe0a43f053f6c6d3a3d12c253f0e7e3569f65f68f137c1cf145f4035a1e580b3eabe09d49960b6f1bf8699b2d1df698a352b28c88f58d32c6401a4fd1686649172b83900f1cf704e0f51cf61f8700d7f9db78c605f096951f8becbc53e189af3419c6a1a26bde0ff001de9635dd1b50b752e9a96953e9ba959788b4dbb8029749ed628240404c95241fed4ff00e09a167f1da1fd8dfe0e6b9fb4678efc4bf103e26f8db44ff84e6eafbc5af6f26b7a0f867c4e63bdf07785efae61b5b596f6eb4cf0e1d3ee2eeeef966be96fafae05c5c485063fe69be9c9f436e19fa33e3f2ce28e08e3ec266bc1dc5b9c54c0651c1d9a4aa6278a327aab0f5b195e14330a1cf4335c9f054e9460abe365431b43dbe1235eae36a5773a7fd1fc11c618ae25857c26619755c3e3f0746352b62a11e4c2d7539c6105ece494a9569caef922e54df2c9c7963168fbf090d8ce4751c738f4278e9fd71eb4836fd4707dfa1ebdfafe78cd2e3d4e33dbd47624fafebdb233583e21f11e81e14d2eef5ef12eb3a6e81a358aafdaf55d62ee0b1b0b70ec16246b89dd54cd33e23b7810b4f712b08a08e490aa9fe004a529c63084a539ce30a74e10739d4a939284214e9c54a539ce4ed18c53949bb257d0fbf57d6daa56f92d35db6eaeefee19e2cf0b689e35f0d6b9e14f11d98bfd0fc43a65d695aa59992481e5b5ba4d8ef0dcc2cb35a5d444a4f6977032cf69731c37303ac912b577bf05bf6cfbaf81fa3d8fc38fdae357d50691a2b5b68fe0efda80e91737de0ff14687b96d748b5f8dcfa25acedf0d7c7fa6c220d3b57f156a56507803c5cc91ebb16b3a36a371a8697078bf84fe31fc36f1c6ad2f87bc37e28b7b9d7a3b36d45344d434dd73c3daade6988503eaba558f8934cd22e75ad3232c8b2ea3a4477d670ef4f3a640f196f409605937aba86491195d19432488e36bc6e8c0aba48ac5591832ba9dac08383fd09e04fd213c41fa36712e37199265f1c764d9dd3c2478978333d862b0187cce9e1a553ea79860ea7b3f6f9666b85a756b430d8e8d1a946b61eaca8e26957a5ecb93e5b8af83f2be31c0c30d8e94e857c3b9cf058fc3a8baf869cace7069b51ab426aded2949f2be58ca369c533f5abc39e2cf0ef8b34cb6d6fc29af68be28d1afa159ad756f0d6ad61af699750488aeb241a86917179672aba306568e765208e7b57ce7f1f7f6bbf837f006d60b0d7f5b7f167c47d6bce83c1bf06bc06f6be24f8a5e32bf8946db7b1f0edb4e5b44d2a29248bfb5bc59e269349f0c6836ccf75a96a5184113fe585efeccff00016f2eef3515f859e15d2ef35177935097c356d7be0ffb7c921633497d1784afb4582f6494bb79925cc52bc859b7b124e7aff037c27f873f0d52ee3f017823c2be1017aa8b7b3681a169fa7df6a088728353d4e1846a5a9618ee06feeee48625b258963fdd3c45fb4ff27ab90d68f0af8519ed2e29af859c70ff00dbf9ce5b3c8b058994528d6ab3c07362f1d4a8c97b454a1430ceb35184aa414db8fe4d82f026ac7190963f882854cbe324e71c2e12a47175237bf2c65566e95172568b9a5371526d2bef99f0f342f1e6afe26f881f1a3e304ba6b7c58f8bdac58ea5ade95a35d3ea3a1f807c29a1da9d3fc11f0c7c3ba9cb1c52ea3a6f8534c79a6d4b5611411eb9e28d535ad62386382e6045f5b917783e71794fac84bb0c600c127f11ec7d315244b8551804700718e40f4f43d7b0c7ebc578ebc3be2ff11dbd8db784fe20dc7c3c31dcc8756bcb0f0ae85e25d4750b4788aa5ad849e21f3ac747b88a43e6adf7f67ea4c7213ece0006bfcb1e26cef37f10389737e28e2ccce8e2f3ce25ccabe679b6658ea75bd83c4d77295bd961e8632a51c3508c6386c2d3a187adec692a54dde2a533f7bc16070f966130d80c152f6185c251850c3d1a7b4694395456f14e72b37393b7349c9eada3c23f690d5adf49bdf808da7586b1acf8c2c7f68af847e35f0ce89e148eca6f17cb63e01f145b7883c61aae8b6f7f79a7daac1a6f852df55b5d4ae6eefacec4c1a8fd826ba57bd8d1beb6f8affb677ed3fe3dd1358f0f7eceff0009746f8197d7965a84161f16fe3f6afa178af53d16ee58655b1bdf0dfc21f006a9acd95f5f413b452c773e33f19d9e976acbe65c687aa221b76f25f037c1ff000af81f50bef11453ebde2af1b6ab6c2c756f1df8db5797c45e2cbeb25759469d05dcb1dbd8685a32ca04aba1786f4ed2349f3552492d249515c7a59850f180a0f5e0f3fe791efd0d7ee5e19fd27bc4ef03b80b35f0e7c2dc5e4d84cbf34cf315c435f88332ca6ae2f32a199e3b0797e07110c9f055f18f0586c02a196e15d3face1e788a989962b17515175e187a1f379ef02641c5199e1735cf28e2b115b0987585861a9e27d961aa538d49548bc472d3556aca329c9594d414542293f79bff003c9f8813fc5cd4fe377c61d4bf692d73c45e2afda323f1ceb765f15bc49e2fba7bef106abe26b6ba7b6babe69a4440ba75c450c2fa2436690e996ba23e9d6fa6410d92c11ae21246e3b981ef8246403d8e7af24f1d0e7a62bfb0efdbb7fe096df09bf6c6bf8fe24e8dafdcfc20f8f561a7c7a747f10b4ad3e2d5345f1569d669b6cb4bf88be19796d4eb70d92661b0d5b4ebcb1d72cadc0b6f3ef2d522b48ff911f8d7e09f167ecf9f14fe267c19f882345bef1b7c2fd59746d42e3c2d7a6ff43d7a5bd82d6e345bbd2e59563bab66d4edefeca59b4dbf862bed3de692deee31244c5bfdbdfa287d2abc3cf1e386f099361ea53c97c48c9322a78fe29e13af42a424bd83c361f1d9be4d89941d1cc727c4632ad39529c6abc561d6268d2c650a755b94be5f1b923c8d3a3ec20b0916e185af18c12a9184748cd5af0a8a11d52f774bc7ddb1e7d797175a85e3689a6ce223122cdabea024c49a7db48bbe3b7b66394fed3bc507ca0726da0cdc1432180368d869f65a75bc76d630a5bc5bd9df058bcb2b7324d712b1696e2790e4bcb3bbc8cd9cb9150691a7be9d651452b092f66792ef519d40ccda85ce1ae1f1d4a29c431673b618e255c05ad43f74027775f7238e9edc74cfad7f6550a0a5fbe9d38aad3ba49c7f8507671a70dda7bcaa5b7a8e497b8a2979304e4fda4ac9b51928ad7d9a6969697bbccfabe5df4e83d30a461ba31c9efcf7208f4247d4f5f49f79662430c03c8e8793927eb8edc1151818f4e84f7c9f971c8f5cf42727b7d1c769ef82071db3f37f3238ebfa66bb23462d2bef757bdefd1ad75f4dd7e2692a8d2fcba765fafe2fc886e2da0bc8a486ee0867b69061e19e35963719ce1d2452877718ddc83d3048ae73fe11cb3d383be9babebbe1eb7895e4923d3758b886ca350bb9dfecb75f6ab78d100ced8d114740b8ae97a900f39ddc7523a0039c9e99f4e462b9dd473aadfc5a3afcd676c60bfd68e30248f79363a7124e07dae48dae2703adb43b0e04d5e5e6f9064b9b5250cd727cbf31bda9d18e370d0adcd392f75a94939c22a29ca6e3f0c53766f7df019ee739156962f23ce334c9f153d3eb193e638ccbab4ef28c946ad4c256a2ea2ba4dc6a732493d1ec53d0dfc557f1cf792f8b7c416da5cd20fecc86683491a84b68808fb5dd4cda6978bed8c0496f0ec596384c6f236f7da9b4340d319eeaeaf239b52bc9d3cc9eeb519e6b89e778a12913b90c908f2d78411c4800edcd6a823a05edc63e5fc3078030b8e32287c98e55390be54bf78f3feadb8c71eb83db3ef5e665de1c705e52e53c0f0ce4d46bce0955abf52a329cdf35de9514e314e493514ae925ab3d8cdfc46f10f3dc2d1c3679c77c5d9bd0c2b752852c6e7d98d48519f24e1cf4d2af1e59284e74d4afcdc92716ecd99fa45eea969a76966d75ff001243ff0012eb06509e21d69914fd920206c96fa45c28242aedda060631b6ba71e32f1722b63c4dabed6509ff001f2095cf70cd1b1ddc7273919273926be54b2d7bc56b676417c4977c595a0457b0d2240a05bc6aa1736392147dddc589ee4f5ab49e24f192003fe1232d8ea64d1f4a278040076c0a09edbb1ce3a0eb5fa4cb079b462942862d7bb17758983d1476fe3adf77e6fb1f3987c7e714e9c153ab984128c5da389d12568c55dd7e8ac979592e88fa757c65e3156dff00f096ebe76ed2237b9b7923ed81b24b56017a8201f7c83cd4b2f8e7c6d2c72c6be28d520f318309e28b4b32c2012711b49a7bc615b203891242c38c82d9af98478afc62a3e5d6ed5cff00d35d12c5863b93b1e2393d80206303a8cd4c9e31f1828c1bfd264186043e8a417cf3c98f514039e0607dd038e093cd2c2e70b6a38e5a5b4ab14e49e974fdbfc5ef2925be8b4beddf1cd73a517fed59adaeaea35df32db6fde3f3dbd12e8be9bb5f1d78fa08c23f8bb52b8e70af7369a234833f7b023d2e346ce3196538e36e2adbfc40f880adb60f164d6cc3b7f646853ab820001849627206493c8c1eb81c0f97bfe138f182e009b426e982da55e718c6785d57009e7a11d07be6ea78f7c51d1a0f0fc8d8e5fecfa9459f4c817ce303bf524fe06b9654b375a7b3cc53f7529269b8fbd1568b555bb2bf776d6e8b8e719d5acf1799a4edbc9b6f6b3bf33befe5d9d8fa661f895f12d57fe46e85cae4133f86f48639ec4f94202dc64e1597907af157a1f89df12415ddafe8f30c6dc3f85e352e320f3e5eab18cb7a01efe86be5e8fe20788930b2697a0c9c93b92e352873c8fe17130ce06786e31ce78ad18be226b0bd746d35c0c602ea778a7a7382d60e01c723a807ad7254a79b72b6e1992d3ac5b4db4b5fb4aef6becf7d4da39ce73a5b178fb2b6f04deb67d632befd765dd58fa7fc3d2f8bbe387c5cf817f03f56b9d11ec3e2b7c5bf03784753fb1e892db5c7f636a1afd926ac1647d4ee17e6d285f2bee8ca8dc768c715fdfbe9f6769a7dadbd8d94096d69670c367696d1fc9143696912c16d1468005448a08e38d14600540abc57f9d6fc17f8f171f09ff685f811f1cef3c1a75fb1f83ff10f46f18ea5e1bd3f5b861bfd72c2c7ed115ddae9d757f63159dbdff9572d2da9bb2b6e668a3476457661fd4b685ff05f0fd8ab54b68a4d63c3ff001ffc2f75e5a196d2f3e1b586b0b0bec5dd1a5e681e28be82e029c82ea103919dbb706bfc5ffda51e1478ddc7fc7dc038fe14f0eb8c78bf85f24e18c75358bc8f2ba998c30b9ce6599d278ea55b0b85e7c4c6a3c2e032d946abc3ba6e1271854728558afa3e1dcda95b1d3cd319ec71556ad18c1e260a8f3d0a747dce59c69460ed56ae21495dcd5ecd3563f73810d9c9c01fd39ce33dcf183edf87c5ff00157c71e1bd23c7ff00137e2178f34dbed7bc27fb2ff80345f15695e1cd3ec67d52e6e7c59e29b5d4f519b5eb2d2591ed2f35ab2d32c2d348d03519a375d11f50d4ef165b6264993e3af0f7fc172bf61df11f8bfc1fe0dd3f50f8bc753f19f89344f0a6973defc2ebfd3f4e8b52f106a16fa5d8c9a8de5d6a6a2d6cd2eae615b89d2299a28dcb2c726cc57ddbf10f4dd73e1efc506f8c565a0ea1e2ef87fe29f0841e04f8c5e1dd0f4f975bd7f4cb6d22eaf27f0df8e74ef0edbc735cf89f49b4b4d5b57d0fc61a4584373aaa6912da6a56363a82dadd5b8ff2db30e02e37e07cdf0380e37e0fe28e12cc335c057af93e033ec16278771d9ad2c363302b32a196d4c75184a35b19964b1f9550ab08c9fd6f1d87a3084e75e9a97d650c5613151a92c36268d6546718ce5466aaaa6e5094a2ea24af6e6709754a1194b63d2bc053f8a3e2159787bc55f127c19e02b616f0e9de2ff024da2788751f15ea7a15c6ada7971e75eea5a069315b6a5fd957be5dc6a1a15ccda7de09a7b5313c0b1cb27b18018123a0c120649c64e0fa67d3d73f4af8a34bf8c5f0cfc0fe1ed27e137ecf3aceade3df18f89fedda77c2ef085fbf882fb42f06c71dab5c4c752d775ed26c93c3de05f065a33ea72685a9ea17bad9b4b61a368f693068a1b7fa13c79f13340f841e0eb3d73c71a85e6a57d2c9a3e81a6e9de1dd1ae351f1378f7c6ba9ac76ba7f873c13e11d3c4d7fabf887c45a8890e99a1d8abf911b3cb7335bd8da5cdd45e2e3b25ccea66382c161b28c742ae6b8c783c8726a797e634b32c5a9e23d8e1e9e0326c4d5c6662d6231351d1a4954af1c4661f5c861aad6742bfb2d79e1184a72a9051a50e7ab55ce0e308a8a9373a9a462a3177727a28b8b7ba3d41b0a01dc01270013df9233fd7ffd54d53904aa97cff74331e393d01200c753c7247a67c1f5dd2be21e95a3781bc59fb53fed09f0abf605f0a7c48f12c5e1af04f82b539fc1de28f8a5ad6ad7703de5878735ff00887e37beff00855be1cf14cfa7c535cea3a2786741f14c5a2332dbcfe279ee13e7f983f6ecfdaaff00e0931fb05e8fe121fb43fc51f89dfb5a78ff00c41a9e9d6b1fc2ff000efc6ebff881e248b41bb981d53c5faf7837c0de26f05fc38d1f43d2a35324165aa5ad95deb2e63b1d26dee774d2c5fd87c1bfb3d7c78e25a187c671055e14f0fb095e34ea3c3e7f99cf31cf2846a454a2ab64b93d3aee155c1c64a9fd79bb35cce2e3251fccb34f1778470139d1c34f1b9bd5a6e7172c1506b0ae519a8bb62ebca9539a8bddc5385b552695cfbcf54f1c782fc3dbdb5ff17f85743f2fef8d6fc47a3692d1f539905fdedb94180793ee3a835c0df7ed1df0034f774bcf8d5f0ae392370af18f1ff85e69159b0047b2df539a40c7276aedcf0300d7c23f03bfe0a35ff04e8fdac3fe120f863ff04d4fd927c25a87c62b2d12cb59d5fc7ff18ff673f08681f0f7e1168f797b0e9c7c4fe269f57bbd57c45e3cd6e092461a0782746922ff00848f548f6de6bda7e9705f5d2fceff001d3fe0bf3af7c19d6be287ecfdf0dbf654fd9ffc49f183e1c5d5df8325f8e3a2de2e8df0a1fc496f6e905dead6df0d62f090f107f69e8d78f2c57de1d1e3297434d5ad26b4b6d6aeb4f8b9fd1728fa01f0be338d717e1c55f1f30b8fe37cb322c2f11e65c3d90f03e3b118ac1e578bad0a342b626ae2733787c2cabce74e587a5889d3a95a9558d6a74e54d391c14bc4ecdf1b81a598e5bc1989ad83af889e168623139a61e9aa952115ed2508c2949ba74da719d44f954f9a37b9facf37ed3bf00921796dfe2b784b51657544b7d06f67f125f48ee3e558ec3c3b6da9decd23118548eddd8938c6e200f883e2bffc1623f636f85da8f887c3c358f887e34f187866ea6d3756f0af867e1debd67a8daeab6ec525d3eee4f17278621b1b847ff58b73e5b463961c1afc82d1bfe0bcbff051ed2fe12cdf0dad7c71f095fc5735ade585cfc6b3f09ec60f89702decb2cad3592d86a961e0b86fecd2630e97a94be169e7b348ade4749ee2049ebf1b985e4fa96abad6b3ab6a7e20f10f88752bcd6bc41e21d6af66d4b5ad6f58d46e65bcd4754d56fe7779eeef6f6ee79eeae279199e596591dc926bf7de12fd973c11f5ba73e2df1078d731c2c6d2a9430582c9723a736a50bd07c91ccb1526d73a9d6a55f0ae3eef226ee77e138d388f10e71c465180cb61169539fd6a78e9ce4f57251b53a7185eda4e2eeef7d15dfecafed17ff05b6fda23e27d8ea3e18f809e0ad2ff0067dd02f7ceb77f196b97b69e37f8a13594d95dfa64696f1f853c2d7463f99678a1d6eeedddb30dd23a07afc4cb8375ae78bb51d4f56d4b53f10ead25e49aff0089fc45aedf5ceabadebde28d55a4905eea7a8ddbc935d5d244f25dc9248cdb5ee2dc00046a06ddc4f1db4535c4adb618229269589c058a25691ce4f40aaa4fbe31c66b3bc391caba6a5d5c214bbd4e49755ba006191ef58bc51313d0c16c2de1c1ec87000e2bfd04f083e8f9e14f82185796f86fc2182c9aa62a1196679d57a95f32cff33a542507469e3b38c6d4ad8ead4fdbc9d5861dd58e1e1253e4a49dede6e3f1b89c7d5a6f198895769b9f2b49429463bf25282508f34a51da2eeae9b7777ddcf5381818fa918e807193ebd33d73d29a0e587ca07d4f6c8ea339c8073d47e19a1411f7b7038c81c0c86c74e7b7d3a1ed4d079e17bf19c107e51c7e2075f607bd7ef093d12b2befd2ef4f4f5db6b7a9c8efd95baab3badb5eb7f4d2d626ddbb27951f77a73df39079e719ff003c848031e9803273918ebdfae4673fd2862319ce4f3c1c671818fa9cf4c771ebc53437cb920038c0f76ec71dbea79e322ba23095b5d7a7dcf456eeb7bb5a19c9eb7dd6ebb75bf9bd6da7cb6b105c5cc7696f7175310915b432ceec4ff0448ce429c1c12011cfaf39cd64f87adae174f5bbbc1ff130d5643a9de75fddbdce1a080743e5d95b086dd47631b11cb1aafe23ff00495d3347c7fc85f518a39867ad8d98fb6dde71ced9122484f1ff002d707ae0f4e18ae42fcbf2e38c742318047d4923b0e98a9a7ef62a53b3e5a1054e2ad64aad54aa54959dda92a7ec526f57cf35e9cad39d6d345495ed6d39e69352eaeea17edf1bf9a313bb79393c0e9c7cb9e73ee7db8a19be4909e7f772601ff71b3dbd3af1db93d2984923207e636e4647afe23a76cd3246629271cac720eb8c0d8df4ce4773f9576a8c7964ef14eda27e5b2b79bbe9dbf05515a2f5bdd3baf4e9f3d8f99ec862d2cb38c0b2b5c1f7f210fe7d73e9568952a3e4c107733649dc31c000f002f7c75c93c1e2aada736368c3aada5a819eff00b88f8e7d8f7f43560b6e6e99ff00ebf24fff005bf3ef5fa34adc94baa708eaf4bdadaabead6da3f9ee7ad42dece9b7bf2c176d74f5db5eff00891e3278193fcba038e9f5fce978c63a0031f8e3fae31e9d0fb538100f4c70467bfe5d338e3ebcfd13ae47f741c76ee78c1f5ff0ae694536fb5d2b6fff006f45a7b7f35bd15ceb8f35f4edafe1d3fe0898e39ceec0e318e7f3e7f977c74c38160df7410485231d3193f29ed9c73f51480f03d7767fcfbfe9cd48a7007386cb11951e98273eaa7903d3afa5734e9a7eeeb1d6f757b5edb49e9ba76dbcf757365b2fcd69ff000de63baf6fc0f1fcfa53c391800631d7df3c1f4ff38a8be6207e7df9e3bfe3d7da9e08da3bee5edeb9c003383d463ad6728b692bdefae8eddb6e9d7fad8d20ee9257b593d756fbfa16633ce4918e0e31cf43919fd73ef8ab71bfcabbb8c1e47aff002ebf5e3e9c55043c1049da00ce06081e9ffd71eb5307231dcf18ff003cfb76af3abc24d5aeef6f79af75dfdd6aeefcdba5dedd96b7d52fc365f76897e36f9f62a6aba8df68773a0f883481bb5ad035fd275bd263ceddd7da2df43ab403d4ed7b1deddf6af63cd7fa46fc12f88fa6fc59f855f0d7e266893adce9fe3ff03785fc61612dbb07046bba2d8ea332a05c90d6f7171340c9cba490ba1e54d7f9cff8374c8b57f105dcb75109acf4bd33c90ae32a6f35666462a783be2b281b07aaf9f91c9e3ec2b5fda33f68cf0d7c1bd2fe04683fb44fc54f097c17f0c457e9a6784bc35ae597864d8e977b73717f79a54de2ad234fb4f174fa1453dc5d496da6cdae7d96d6395ede24f21638d3fcc6fa70fd15788be9299970463b84b39c8b23ccb84730cdb2ecc71b9ebc5ca9d5c8f35c3e0a78eab87a784a739e2f1982cc32dc2c6860ead4c2d1a94ea625cb154a4a37795e73fd978bc7ca587a989a188850a7154a54e128e230ae6b9a52a96b426f11561394549c5d1872c5f35cfec3ff00681fda47e0d681f1f7f64ff85b71f12bc2379f163c43f1b64d32c3c0567ad5aea9e2c8f4cd5fc03e31d3efef6f34bb396e6e744b2599aca396ef545b4495dd62896520edfc37ff0082f5fed31fb61fecc3fb54fec95f133e01f8975ff0468ba3782bc5d69f0efc4da4e956faa5b5b7c57f115f4ba1f88e310df5b5e6972f89a6f0acfa7d8e9706a16f705b4dbdbf16f03acd391f9a7fb04f80354b4fdb4ff639f89d67a1c1a5691abfc70d06df4ebfd624babaf1578a23bab4d58dc6bf2cb3b49731d94c8d2bdbdcea57135dea11b1b8589616595ffb9ff1a7c39f01fc54f0c5e7847e23783bc35e3af0cea5b24bbd07c57a2d8eb9a5cb2c4dba2b85b5be8665b7bb8186eb7bdb5f26eeddbe68668db9aff3173ec070d7d0bbc77f0bb32c26323e2dd1c9385b3aad9ee2e9d3c265b279b6638fe23ca3133c829d4962a184af942c4c25469e27175ea556b1745e2f0f3aca747decdb28cdf89387b1d966654b13c335333784c560aa2839d5a9964d612b52aae9d550752963214ab53527184270719f238357fe0f7e367c29fdb4ff006e06d0fe22fedd1fb57f887c49e22d374b922d0fc3fe2582d751d2fc1b6572de7cb6f0e95a7cfe1df07689772923fb4ffb274ef3dca243757d3185123a7f09bfe09b1f0d7c6fa4c7a2f86fc23f18fe38f8ca32d16abab7c1fb1d6f53d0a39cc8c96ceba85b6911786f4b0f16d2f0eabae4ff006790b299e48d7cc3fda6f877f606fd8fbc33aa26b3a4fece9f0bbfb46190490cdab684de2348645c6d7b7b6f125d6af6b06303e58e05c6d5230c723eb2d3746d3345b1b7d334bd3ecb4cd3ad46cb7d3f4db5834fd3ed506004b7b2b4586d2dd00fe18a155006076aface26fda05c578e55970ee0f3d4e78975f0feda39470ae1b0babe5a152a60a5c4399e3687bcbdaa9e270d56ab8464ea2bbbfd260b84fc1dc9f0d4e9659e1850cdf17f518e0f138ee34cf31d9d53af2b439b151caf072c0e0286279a0e54dd38c69d1552718d3ba8b5fcde7fc1257f604fdb43f610f147c76d466f867e00b8f017c5f83c20de1b8fe20fc51b1d2bc7de1c97c372ea53467c43a77827c3de34b39226b7d526b77b5b4d4a19bed3045732471132469c9ffc14f3fe09cff0fbe187c14f8abfb687856e87c3bf8b4daee97e2cf899e00d2fc413f897e14f89efbc4de26b5d2b583e134d7347d275fd1b5bbfb8d51759852068ace7bc17f07f66224cb3a7f4f92ed450a085e8bc0185c90327dbff00d79afe243fe0a49fb5e7c73fda87c71aac1e2791747fd97bc13f137c43e18f0a7847c34d245147e21f0deb97de1cb4f13fc4a91c9b9d66e2fee6d5ce917b1ba693a3b5d8b58ec61b893ed93f95f473e36f17fc67fa4ee0b8eb27cf3867c3dccf198ee137e2567584ab8ec361f89787b03f57cbf07c315f2fccb30c7e1731c767983ca6785c3c68d0c04a9d6a15b308e269558cb0d8bf9eabc3583cbb22c4e1b2ccaf13530397d3c76330f84c2c655a8e4d46b62255aa6239e3173a197e12b6321072ab2abcb4e54a9ca52b739f04e9b7a6fac2d2ef6884cc819e3e7683d0e39c90482c1b2df291f5ab0c7193d49f4ebd723f4c63faf14c545851224015102aa2a81b55547ca06318c63d00fce86048f6207f87f4e07f857fd0dd0a4928a93bcd46376bdd4e492f7934aceedf5d1df65a1f0ee2d2577cd2ddbf2b7f5e96b74303c419b88acb4a5e9ab5fc16f31271fe876e7edb7c30396df05b98700807ce00f048ae8c9c80063071c0e02f3f2818e381c63191cf615cea1fb57891870c9a469601c8ce2e7559bd48c075b5b3c9c745979e0f3d09015f2b9c1556c7be3247e0719e3818eb5d186d675aa28a973cbd9c5db68d1f76d77ad95795669dd249bbdee611579ce56eaa176b6504afa77e7724fa5a29a6c979031d485dc0fa0e0f27a903a0ee071e94c0d8209eb9017be063d001d78c77c7634df988e873ebdb18e98fafb7a73c538671e991ce73d7a7d3bf1d09fd6bd24d3b5eef5d1ad5f4d7aadb4d2fa746438daebaeead2f46eeb7b5bcacbe6487b70403d39e9f28c123a773f8fd2a3cfddc639e9939195ec41fcb8c1cf34f6cf3cf0768e73c638240fa73edcf3d32d18e075c7538c018c0e7f41eb8ebc1e744b47aa76bbbf5e54b5765a3b68b76ff005ca5bded676efd53ef75d2db6bb7a9ce43fe9be28ba9d8e5345d3a1b34e4605dea6c2e6e0e71c32db416cbc1fbb2e38c9cf47bc95e9f31c6719e318fbbc7624678e4715cf78713ccb4bebf6393a96ada85e06c7061598da5be3d57ecf6a9b33fc3c8e78adec9247519e07b01f967e9f41586162dd28ca5eecabbf6d26f456ad2e786f75ee41c21b36943cae72d3d61292d1ce729df6d1b5cadf7bd351e8ff526de769c8c6303a72064641f6c9f6eb9a81fee4a09c62373c9cf3e5b7b1cf61fe714f6ebc9247718c1da5bf2cf03f2a475cc6fcf02094f3d8046c1e9ea3f11ed9aece44a3d74bb6df5d2fb74d77fc89959a95d6e9f6fd535f81f33d9126cec81e86ced8e00e0136e83380074e383c7bd596e0640e7d7a01d8f1eff00feba86cbfe3cac87fd39dafe1fb88f3cff00923d4d4878638cf248c75c8f73c75f6fe75fa34d49469ef6e58a76e5d2d15aabe8eeefb5ddfb247b542ea9434e8afaaecbcfaadfefd6e0a3383cf1b78fc79c71effd69a4633df078e7dcfd7f2c8c539770e3dc8fc78eb81d0647e9cf7a1b200efd0e4838ce318f7e993d2b9fdd7f126d74f46d3db6dbb2ff0033a22b6575babadfeff3d7adbcfa0a7079c00304719c93d73839c6738cf403b714b8e996196008c672bc9183d307d4027d4fa88f27bf07a60f3f51fe4e694671c7504fae7a76ce0e3afd49ace518f2db652bdddf5d1abbd5bb6fd5fa2b3b1aaddef6bf45d55bcfcbb25e64adc038e0ff0087f2e074a52381939e01e3b1e0fe55183c8e723d48c1fc704f7e3a9cf1de9f9c91df3df9c0038ff207d6b3505a59492dfbb6bae96e9a35aeb7b6fa1bc354bc9b5f2766fe64aa71ce32082a474ebee73df9f4cd3831e723a13fa73f31cf1ee73eb9c62a33907f1c8c1cf07d0f6c8aa7a8cb22595cf94b99648da28579cb4f39f2610081d4c8ea01ec40e8335c58b9d2c3d0ad5a52f768d39ce4da4aea11e6b2b26efdfbdff991529aa71954927cb4e2e72f48ae67d576efe87aefc35b416fa0c9a8c87326b9a85d6a5b88c62d430b5b14f5da2dadd182818fde67a939e9b51b5875ed63c19e14b92c6dbc57e32d0b48be8e2dc659f4b92f524d4635441bda26b58de3b860b88e266672179a7e9d68b61a75958c6bb12cacedad9139c6208922e0e393f292481c9cf03b7b47eccfa559dffc41f1f6bda84514da8f87b4cd034ad09650aef6169acadedc6a77b680e4c52debdb43672ceaa18c31b45bf0eea7f95fc64e2aff0052bc36e22cf9d3af3c57d52584a0e8cb9671c7e7153ead0aea72f8151c4623db4ae9b4959479ad7f7fc38e159718718f0df0f39d282c7e3e357173aca728fd5b074ea6679846515cae52af470f5e9538a6af3a916e564e4bec33afdf7c29f17fc32f8e3e1cb782eefbe0178aecfc7b67e17b9890e8daee8fa65a4f63ac68f7288a25b5b98f40b9bf3a1de5b3a1d3f518ed6678e6895e33fd8a7823c4da678bfc31e1df1568930b9d1fc4ba1693e20d2ae15d641369bad69f06a5632892366462f6d7311251993ae2bf903bcb786f6d2e2d2404c57304d6f280064c73c6d1be0b064276bb01952a4819539c1fd3bfd86ffe0a07f0d7e0c7c3bb4f809fb4678f343f065bfc2ff09d827c2bf19eb4af6d378efc11a7caba5af869f4fd360b9b8d4bc71e14964b58a64d22c7fe271a2dcda5f8b5826b4bd27fc0cf1c784336e2fc9324ceb24cb7199d67bc315f1381c56072dc2d7c6e6d8ee1fcd6b2c4c6787c1e1a9d5ad8ff00ec8ce655eb4e9d28d4c5aa19d62f12d3c1e0a72a1fd79e3df08bc1e2f05c5584c3fb3c057c3d1cbf36acab4550c2e2694a950cadba726951a55e84a585e7a76a2a7430d4e4954acb9bf7b1a65500b361402492707a8ebf4fff00576af903f6aafdb5fe11fec9d65e1bb6f1b36bbe21f1c78e5efa2f02fc36f075943a878b7c50ba7448fa8ea9b2f2eac74fd13c39a67990a6a7e22d5ef2dac2da49e28a3fb4dc3086bf27bf696ff829a78afe2ff886efc09fb2c788f5ef067c31d1ec6dc78afe2e3f86751f0ff8bbc5fad6a31c8dff0008d78060f166976f73e1fd1748b558e5d63c5474c7d4ef6f2e63b3d1a6b28e096e9ff3b1b46b3b8f104fe2dd4e4d475df16dddac96975e2bf126afab7893c453dadc4cb73736afabeb77b7f7ab6d35c4693496d14b1dbb4aaafe58c0af8ce08f00b1729e0734f12ab63324c1cf0f2c62e0fc1c2ad0e26c42a941cb014b3ac55554a1c391c44dd0c46230f4e9e3b3558193a2e395e32a46a61fe23827c25e21e33a186cce9bc3e5b90d5c53a72c7e29bface230f46aaa78a965b83e497b792e4a94a956c44a8d0f6a94d46b5284cfd2ff001d7fc1597e3d6bd6573a67c3cf80fe08f02dcdf4823b6f15f8c3e21dcf8d2e340b36044b712784b45f0f68b65ab6afb4816900f118d2e1b8fde5cbdd423ca7fc7fd7fe18ddddfc29f1b781e0d62e7c41ac78a2dfc497b1ea7e2136d11975ed7ef66d65de73616b1436f6c75799e7802432496aaeabe63ac4ac3d9db181803a0f53cf3d7a9edf41f53ca0fbfb4027279e7af03a7a8e7db8afe8ce15cbf22e0692ff0053387f01c3fcf98e5b9a62ea52ad98e638cc7e2f27a95aa65b2c56639be371f8ff006585788c47b3c2d0c451c3df115a6e94aad59cdff4c70ff83bc29c3d86cd614a598e36be6f9663327c562b1d888ce70cbf1b469d3c450a387a3468e160eaca11aae4e84e6e6a0949422a0bf2af46ba927b316f7314d6da8e992cda46ab677202dcd9ea9a739b4beb6980257724f1b85753b6442b2292181ad4208c02704f18e8739c703a839e299acdf586adf127e2b6a9a4c914ba55cf8defa3b59a064682e27b2b7b6b2bf9d3612bf3df4371b981fde302f8e4d51d66f1acb4bd46f0005aded27950100e6511ed8571c67748507bfd79aff76f84736af9cf09e419e6370df52af99647976638ac2b4d3c3d5c4e0a957ab4aced35cb29b8ae649e9ef2be8bfcc0ceb07472acd736cba9d658ba19666598602962e2d2589a380c5d5c343129c5b56ad0a4aa5d369f3754ca3e1dfdfc7a9ea47731d4b57bc742c319b5b371a7da819e40296ace3273f396c0c9ae88b1073cae3d79efdb231fd38accd22d7ec1a6d85960036d6b046e47799630663c9cfcd3176e7b1fcef8e833c9ebcf4e71c1fe5d3e98eff5186a52a746109b7cca29c9d97bd37fc494bce527295afd7aea78f4972d28292fb29cb4bfbd2f7a5d7f99f47a2f34d93e0e3a83803db041e4741fd71d6933f2f1d49e3a8cf19f5cf07b83cfaf3c21391c718dd818c83c73d3fc9a4de0819076853b4f75efebebdf9e38aee516acb95696bf65b74b3b5d7afaa467392493b2dfa5d6fd1edafa7e00720e71d48e4e08cfb1f6e00ed599abdd1b3d2b51b9c9530da4ecbc9199590a4438cb65a56403018e4f4eb5a258f39ce00c7e7cf7cfd7d78e3a7181af3348748b1539fb7eab6ab22f6fb35a6ebe98e403807ece88dd31bc838dd5359b542a5b494d7b383bb56a957f770e974bda4d5afeadd9339eac9461269d9f2cd2e64dd9b4a314bb6b67bbeda74d5d36d859d858d9825becd690c2dea4c712873db9660cc7be7f2ab84f4e08209381e9d7d3fa127f1e41c9e7249279cf049078e3d4fd73dbae0aed3852dc6e2df36ec9e38c119e99e3b718eb915d50872a4925eea4a2d5ad6514b4bebb2eaeeeef5d8c348a8c15fdd8c52e9b28a5f725a6ed5d26fa8bc6320641182a4f24e48073cf43d8fd683c452f3cb23af23a0547e9d8027a71d7148065f6e7a63a9e38e71c75e7a52c9b446fc6e023752013824231e7d79cf6fa67ad6bcbeebd572d9a6b75b356b7abe9a69e5625ea9abf476ef6f47ea7cd56a0fd8ecbb66d2d73d8e05bc7f4efe9df3ef529c8209f6191d31df3c77edfe351d9906cacca86dbf62b5003104e4db47939c0ce4e48e9818e6a63c0c9c8c9ebdfdc0faf7f7ef5fa3b8c5d3a6ad67ece3e5f6236b75bf7b5d2f43d9a1674e0b5f823e49e89bd3bf9f5433e65e871c649ed924f3f5ff01f4a0b71cf39ce7dce3008cfd3d38fe616ea0f3c11f8faf6e3b0ef487f873c74f6c03c7bf4e00fe5d2b95c2dbdeeba5efb6de5af6f3f43a62d6ed6ba696f4bf6dad6ff008037b9e3b700fa9fc38f5e9ed4bd81038e01271d475c67fc38fc3340c67919ce7bf73d3d7bff00914dff0038f4ff003fd0529477d972a6d5adaf372f6ff0e9b6fbf47ac5deed2d7fa7b8f047b73c77e3be3d7a9e79c607d69c1801ebc71d38c01ebd39f4cfa547d076c71f51f41fa1fd29412571d41031fa91dfdbbf6aca2e3df7d1bddbb5b7e8ff003bfa58d632764ad67a75f9db6f979937afbf07e95674c83edfafe836246e537eb79383d3ecfa723dd10d9fe1f39205e9fc6077154f79c671d3af3f9638ee7ffafeb5d2f81226b9d7f54bdc111e9da7c1651b93c0b8bf94dc4c001c6e105b42793bb0e063078f0788251a780a91564ebce961e376db7194b9ebab3bdffd9e155bdd689d9dacb1c5d4b5251574eace14edafc3277a89e96fe0c6a3d74d3b1ec64ff11393e9ed939fa0cfa8e79aed7e0cdcea1a77c68f0d496923db69de21d23c41a1eb6db4183507d3ac86b1a6d9fcc571796b2033a4b1ee68a294c7280b7081bcd752d4adf4bb0b9d42e640915bc6d236ec9cb018545500b333be155546e62703ad7dbde1cf85571e0ef83de0e74d3a3bbf8d7e36f12689e22f0a58dc3adbcb69776eada9be897d7522b1d3b45b6f0e49a94de2aba54016eef922659648aca31fc5df49fe32ca387f8223c2f8ea74b118fe39ab5f27cbe95570e4c1c695075f119c55e7b4696172b92c3e2711899b84684212ab294651a76fd83c0ee19cd73ce31a39e6027568e1782961f88b32ab0e652ab429e269d08e5749457354c4e6f09d6c0d0c3c6ff58e79d3b4a2a76f79c020283d723e9db27f13f9e7debc6be17d95a788e4d6be26ea104573ac6bdae6b563a25d4e8b34ba2785b45d46e346d334bd30c9b8d8c774f6571a96a26dfca92eee6f1bed05c4512a5cf10f8f2e6e347b0d3f4057d17c63acf89f4ef064fa5eb30aaea7e11d52f04d36a73ea160f8f3df4dd36d6faf34d9d4be9faabada5c4134d6b2f3db787f44d1bc1ba0dbe8f6328b4d2f4e1339b8bfbb52ef3dd5c4975797977773148cdcdede4f35d4cdfbb8ccd2b7971c71ed41fe6453a58cc932ac6c5bab85ccb35c452c152a74f99625e5d8775278f4e50d634715899e5f0a52a529fd6a3431104dd2bbabfe85d4960f3dce302ff738ccb32ac2d5c6e25d5517416678b586595be4aa9c675b0b85866156ac2a453c2cebe16a5bdbba6e9f4458b101d9df1dce4f51ef9229738fa1c8cf6f4c7e46b8ab8f1ad9dfdc9d13c170ff00c271e289cf9369a568120bdb2b49d94ecbaf116b76fe6695a0e97011e75ddc5edda5c185192cedee6e1e389bd0b4df82be367d3ece1d63e2e6a3e7b411b6a72695e12f0d4338bb9177ddc5a66a1770dc34567148c62b092e34f9ae92de389a77967dcc7c5af84a782a74eb67798e1725fac36f0f4f328639e27134d24ea57a787c2e131559528de31556bc6953ad37c94275674eaaa7f4786c4d6c5d4961f24cbf159c7b085b113cbbeacb0d8493e554a8d4af5abd0a1ed6a479e5eca94aa54a5084a55a14d4e93a9c7eb1e26874cd5348f0fda69baa7887c49ae8b8974ff0f6810db5c6a6d65671992f354ba17575656ba7e956df244fa85edcc10bdccd0db42659dfcbae16e6dbe2bfc47f12def81fc3fa2e9fe1bd3b4a11c7e34bf97c43e7dce9cb791878740d4359d16deea0b0d56fad1c4b75a4786e6d4759b6b2951eeb55d13cf8a47fa0358d07c3df043c0be28d7fc2b6171a878b35186cf4f8359d66ee6d5fc45e23f126ab776fa3f87e0d4b54badd3cb6916a57d0b8d3edd6df4fb6852536f671f35e99e01f07d9f817c3561a0db39baba8fcdbdd73549466ef5df125fb1b9d735cbe93acd75a95fbcb31727f7707936e8161863458ff005af26c932f9e6b96e594b33afede580ca71b9b42b28e231f4614abe3f30597c2baa3470797d1c561296168e23db623138ac443152a946342ae123bae10cf73dcce193e639b55ca687b0a798e6d84cabd84eae1f2dad3961f0797cb319c252ab8dcc6b61b195311570f0a5470d85c2d4a30756a55a55a7f9b1fb437ecf9e27f87866f8b1a35bf866e3c2f6ba6693a678cfc39e0ed1350d186876fa6c4b636be2e82d6ef54d57fb460b7b7f22d75d915adef7ecf147a94e9394ba917e54d7a48ef6cf4ab48dd648754d574c42c8d9592da3905fcac87a9468adb92bf2e0e7201afdf9bfb2b3d52caef4dd4208eeec350b5b9b1bdb59543c573677713dbdd5bc8a410d1cd048f1b82390c4609c57e07ebfe12b9f007c53f12fc2fba692587e1d6a7aa9d3269320dce85abf932785a624fccecba4dd3c2c4f1e644e73c1afef7fa1b78e59971d65f997877c4d56855cdb21c350c5e49898c79278bc92a626950c5e1eaae66a7572da95297254d273a1898c1a6e8ca6ff86fe969e0c65de1ee6596f1570ec2b4721e29c4d6c163f0b524aa47019e462b117a4d420a387cc7071c457851e44a8d7c1627979615a9a5bc1805618196233d70a01edec7a1e9f4a4041032339c01ea00f7e873fe4d34f4e33ffeae48e339edd3f2a68186eb9247d4e723d864715fe81c7a5b5d9b5d76bed776fd773f9024ef749eadf77be8fefb7e77d879edc638c74c761faf3cfa8c7ad03271c0ff001eff00e7a1c7b52e072391cfd493c0e9ee00fc7a53b0171ce00ce011d4e460e3dbdb8c608ea6ba22f45749dd5f57f7496a96ff00ccecb5dec72ca57b757f34f4b24d5d7de30ae460f2063bfbf1cfb1fc062b1dd9a5f11da2ff000e9da55d4c08cf135f4eb6ca339e9e4432fde53cb753db6411b48c127bf5c63b74ee304fd2b9cd25dae75af135cf0563b8b0d3232083c5a5a0b89870481fbebcefc8239dbc573d67cd3c2d3937efd4e695f5f76942752327afc31a8a1bfa6ece7ab2d69c5abb75535a6dca9d56e56e8fd9a52764acecefb3e8828c1049c2938c7ae481df9e3fafe2a08da7a9209c739031c9c8ff00207e1430c7707b7e5fa7f5e7d68507232320e7dbb803f107f3cf6eb5dd4db92565b2f2d15aeefaf9b57ea3934ddde97b35656e5db457edd5df5f3771f9c3641c918c7a7231800f1c0f4fa678cd3246ca49dff7527603908fc9e80f6edfe149db1eff00d0e7dbb7e3d7b5123850c0b1188a520e07f71860f4ce471d7ae7e95acd7bafadd7a59efabd9dd3ddf77d0c24b476df96daa7a2b27e575d17a1f37d937fa0d98ee6d2d4139f4823c0ff00eb76fe53c873caf60383ce7a73edd3271dc7b66acdae93a8476760469ba9912d85a383fd9d7c4736e84107c8c1cf5c83cff394e97aa00cdfd9ba8a81804b69b799c13c11983b91c607383838afd01d7a5cb0fde413e58a8fbc9dad157ebf656be7dcf628465ece9d95bdd4afbeebcfaf77b69ab33082d83d38271d73dc607aff004fd0f4c8c600edd4e3a1f6e0f3d062afc9a66a4a016d3b5200027274fbc5ce7dcc03f9739c556fb25d83cd9df71c9ff41bbe8781ff002c7bfe3d0f5ac5e228dd7356a6acb6738ebb5dbd6e9ad7f2b5f45d6a32b6cefd9a77d6dd95ba90e790718e323df9f5c1edd7f3ef487ae48ea09f5ea383f89fd7d2a778674c17b5bc8c70a0bda5d4601f62d101d3f0e2a1255492e24518e37c528c9ef8053b9e3df9fa566f1187766ebd3d2fafb4a7bc6d7ddf469d93e9bf629292e965a37a37bd979bb88d8e3a1e003d3f9f5faf6e948031ed8ebc73c7e3cf5cf41c9fd6904b171f364918c057e33938c6dc835300a5771dd86002131bfe9f28c03dfa60e7f09f6d8776e5af4b66db954825f65ad6eba736dd6d6ba66d18b96c9bb6afbdbd2deb7fc86edf7efcfa638cfae0f4eb8af44f87d6821d0e6bd6037eafa8dddf230183f66561696809ce5bf716e245e07fac208af34bc996df4fba9e42104714ceac55941648d805c95c6771500632580c0278af72d2a08f4fd174d89888e2b5d3adcb9c7ca8ab6c8f237cc4679dcdce33939c57c9f11e269cea60a8c2719a842b626a5a519eadc69527eeb765cbedd6bacae9a5a69cb888ca35a8c649c6d4e75126d6ae768467d3dd51f6a93d1dff000f4ff82de054f8a7f1a7c27e19bbb7375e1cf0a8ff0084f3c570b0dd6f7106953469a06957239565d4b5c7b66789b8960b59c608071fa8be0489bc5fe2ef13fc47bb5f32c2d27bbf02f8195812b168fa35d94f13eb76e08da8de22f10c325b24a9f33e99a2d9264c6e6be47fd9abc3f79e07f81de3df8b9242d1f897e253ff00c531e626d9e3d1e376f0ef83218c852e89a86af7f36a8178dc934126de86befef0b68307863c37a1f87ad55443a369363a6ae0712496b02473cec792d25c5c096e25724b492caeec4b3127fc40fa54f88f2e2ef107896b60b11ed72dca6b3e06c8e5095e9c6865ca9e2b8a31546cda8d6c5e3eb61303ed62ed5b031ad4a578ceebfd3cfa2df012c9384b26af8da3c98cce7978d3348ce2a35250c43ab97f0961aaaddd1a382c3e3b378d3959d0c76228d48a4e365e45f1dbc29a06a7a1e9dae0b1b44f1ee99aee8c3e1fea51585bcfabdcf89e4b831e9fa1f987ca9e5d2f52864bcb7d5a26b811db694f797ebb1ed55aacf87be0c58cb243aa7c4696c7c67ac232cd6da5b599ff00843341940395d2745bb6986a172a58ab6b1ad1bbbb90006da0b08c98aba6b780789be255e5d4c564d3be1cd94163a7c18dd19f157896cfed7a8ea0d95c79fa66806d2c6d08e62fed6bc3c338c7a763038c76fc33cfb0ce33edf8e2bf9f311c4b9c6559465993e1b1d5a188861de2aa629b4f1781c3e611a75b0f9660714d3ad85c33c372632b4284e9deae3ab50b462aaaabfd1982e15c9b36ceb36cf2b6068fd56a6216123845151c1e3f1580954a38accf1b868a54b1388fac4aa60a9bab177a7828559aa9295374e8e9fa669fa4dbadae976165a6da02596d2c2d6dacedd5b81bbc9b68a28b760633b3240c678cd697001fe13c9c67f1fa76fe9df9681d4703b82339f7c0ebedd7ae7b8a6b364e3db83eb8fd7bfd2be26b56ab89a8ead7ab56b5493bcead59caa4e4f4d6539b9393ff0013beff003fd02851c2e0a946961a8d1c35082b46950a70a34e37b6d4e946115d1e91ed7f3f20f88264d4fc7df077c32306ccf8875df19ea71738923f0768921d2d64e0ab469adead6170558fcd2c11b0042e47b2afdd1fa7d3d0ff0091f4e6bc8f5a65ff0085c9f0ec73bbfe10ff00886c1fa9e66f0b02393d7278c7604719af5d1ce7b7a7bf4e7d318af5f39938e5dc35463a42394622b492d14eb56cf33653aad759ba74a85272f89c28c13ba89e0e4314f33e2aaf27cd5259ce1e8464fec50c3e4593ca9528b5af22ab88c4d551bd94ebcddaf29317afa0fa671d2bf2aff6cff0dc7a1fc6ef0678c2289638fc73e0abcd0afa60a019752f095e89ed3cdf57fecfd5154124b6c84750a08fd541d33d87f51e9ebfad7c2ffb786822e7e1ef82fc5d1a1693c1fe3ed37cf9029cc7a7f896dae346b866c73e58ba7b266241190bd3ad7ecbf457e257c33e3a703622557d961f34c7d6c8314dbb4670ce30d57098784efeeb4b1d2c2548a7f6e9c6da9f8dfd2a787a1c43e0af14b54bda62321fa9711615f2b94a93caf174a78c9c1caed7fc2654c7c1c93d2336de87c10aac071d4f56fae770fccff23de9768033d719079c77c0c739e9d49ef40e848ee39ebc038e7df8e7af3dba54fb011b7763919cfa0ef8fa73f9d7fbc319439b5767a74decfbbdbf3b69d8ff001b66d74f5eef65e5dbb7fc3c4724e08c8038ff00231c93ebf850dd707b7b918e7bf5c76ea3f1e29e14ee079c1e0903a8cf1d7d4f19e7819ebc52955c1ea7a0ce3ae319e3839ee0f7efcd68e49a4b57ca928bbdf4ddff00c3b5afa58e56ef7eaafeef5edaebf96df710e540239031f3138e80e72338ed8f4c7d339e7fc32164b09ef1700ea5a96a37db93243acd70f1c2d93827104119c91ec38c01a3abdcfd874bd42ecf5b7b2b99d411d4c714854638049385c6475007614fd1ad45ae97a65bb28531d859865dbb407102338dbd8972723d49c9249ac13e6c5c1b5a52a336f76dcab4e093d365cb425b6babeece693bd48dd6918cdf4ddb845696b5b595bcd3e85cc631df1c9183eb83ec71f5ede94fe98fe1ce7fa723b7518fd7d69ea8477e5ba93ec4f18ea3807dbb638a56504f23207a6411cf240cfe5db06bbe326b7568ad17572d16aaef65e5d7a037d5eba6ef4b5ba6ff00e7a6ba0c0b927a2ed1c63bfa91f50491ce060d231db14aa76906395b76dc9fb8e064f1f975e7a7a3d500c1cb007209fcf9cf6eb8fce9aeb94940c92629001c039d8dce7907fcf5adeeb9657b68ba5eeb4d2f7d2ebe7bae8653d13ed67aa6f4d75f5be9a796963ecdd13c35e379742d02e63f1668f1c4fa1693b226f0dc8d857d36d9a30cc3575f99130188fbcd92300e2b4bfe116f1cab6e3e2bd0db2a57fe4599b3ce7bff006cf6272063ebc1a28afa9f651fabd27cd57e0a7b56acbecae9cf63f64c1d284b0f41b8eae8d36deab5518eba356f9113f84bc7cc085f17e83f30e0b785a738e8791fdb986393d7008c9fc5b1f853c7ab956f157870b64e1c785ae38e7d0eb7e83a67838a28af3ea41293b4aaec9ff1aabddaef33b551a49c5722fb3d5be91eef5d90927853c7406078abc3ccec7397f0acfb46386e9ae13c91c1046076cf5a7ff08a78ef7c864f12785640a308bff0895c0c7d58eb8cc7d303681451594e947dcf7aaeaeeff7f5b57cbff5f0b74a9fb9ee475b5f4f4feac59ff843fc6a22466d73c23b8aaf2be16bbc6e607248feda52006ce006e17df39a73783fe22831ecf10782380a5b7f853513bd189032175e1b581c9e09ebd68a2bc9ae9aab868a9d551a8e7ce956aab9b9694a4af69a7f124f4dedadcdaa52a51847969c55d733b2eb7dcf32f8b5a17c41d37c05af5c5eeb5e0d9ac248eced6ea1b2f0d6a105e496d7f7d6d6524705c4fad5c450c844f9f3da194aa82046c4d783f87bc1373f11bc7fe09f85d0de43a747e34d54db6a37efe6116fa1e9f0bea3ac456eb121637b77636f259dae4244924c2492540982515f9778ad8fc6e4dc0dc7799e5989ab85c7e0384330c560f12a5ed6a61f114f098e953a94fdb7b48a70924e29c5c534acb4478f82c0e1732e39e17cbf1b4635f078dcdf25c162a8372846b617139bd3a15e8ca54e509a552949c1ca328cd26ed24f53f5d3c61e1bb6b7baf855e01d3ededacf4cb8f156953456b1168ece0d17c0da74dab5be9eaa916e27cfb4d31618b6796e2190cb2a61449e8dad47ae2eafe0df0a685fd97ff09178ff00c4f6fe15d0eeb5692ec68ba6dd4d6577a95c6a3aaad9c5f6e9e0b6b2b1b8682ced551ef2ecc36f25d5942f25dc6515fe10649429e6d9e701e0b318bc5e1b32ab5ebe3a9559cffda6b62337c652af56a4e328d4e7ab4f0b8784dc66b9a34a29f5bffafd99bfec7c878f719962583c465b84c251c054a5156c2d2c3653839e1e952a7252a6a9d19622b4a9c1c1c60ea3e54b4b57b6f875aafc32f895e30f87d7be2087c5136a1a6e8df1264d7e4d3c6933c977e26b8d4746d474d7b1824b9852d2c66f0f427482b23cb158ca20b99269a2f3e4ecc68d74703cd87a7193276ce73fbbe3a718cd14571f8a14696078d734c3e129c30f423432b9469538a54e0ea655839cf923b422e526d42368456918a8a48f5fc2194f30e05caf138da9531388789cd612ad56a4e5524a966b8c8439e5cd79c9462af395e726b9a52726db6ff64dcf24c901c67bbf719ff9e7c75fce9469570dfc500f70d2673f4f2f1f87d79a28afcebdb556afcefaf45fdd5d11fa5bc261da97b9b5fedcff00957f7bf03cb7c41a45cc7f16fe1a057b7ccba07c448092d26428b7f0f4a07fab1d5d173eb8af575d2ae0fcbe64238fef3e38edf738ebefde8a2bddce2a4fea7c37ef6f9254be8bfe87f9cc7b76d0f0b22c35058ce274a1651cf693494a7d720c81bfb5addeb67a120d2ae700f99074040cbe338eff00bbfe58ede95f39fed65e153a9fecebf1584ed01163e197d6a23972cb73a3ded96a16ecbfbb186592000723af2704d14577f00e2abd0e3ae0bad46a3a7569f1570f4e138a8f34671cd70528c969ba96a8f17c4fc1616bf875c774ab525529d4e0ee258ce1294dc651793e2d34d731f93da7dbbdc59db4c767ef2d6090825b1f3c4b8e00ed907af51c93578594d900bc7f20e31bba02ca470a32703a9fff0051457fd20538aedf6232f9be5d7f13fc0e508db6fb117bbebcb7ea1f63941c6e8f8cf720f1cf5d9ea3a6391df3d17ec52724b270deac7041ea3e4c7423834515d0e11e45a6a92b3ebd3aee66a95351d23d2fbbded7bee733e2bb1965d256d774606a3a869d60e493c4771a840b2f21','Unknown','1970-01-01 00:00:00',NULL,0,'muhca.pp.mobile@gmail.com','FR','TESQ00000004','3','eyJhbGciOiJSUzI1NiIsImtpZCI6IjI2NzdjMjMxY2QxZDdmZTY4M2I3YTQ3MDdkZjU0ODZhNzNjMWVhMjYifQ.eyJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXVkIjoiZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXV0aF90aW1lIjoxNDczNDUwMjIxLCJ1c2VyX2lkIjoiYTNjMDA2OGEtMzUwMy00MjEwLWE4OGQtMGVjNDc3ODFmZTJmIiwic3ViIjoiYTNjMDA2OGEtMzUwMy00MjEwLWE4OGQtMGVjNDc3ODFmZTJmIiwiaWF0IjoxNDczNDUwMjIxLCJleHAiOjE0NzM0NTM4MjEsImVtYWlsIjoibXVoY2EucHAubW9iaWxlQGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwiZmlyZWJhc2UiOnsiaWRlbnRpdGllcyI6eyJlbWFpbCI6WyJtdWhjYS5wcC5tb2JpbGVAZ21haWwuY29tIiwibXVoY2EucHAubW9iaWxlQGdtYWlsLmNvbSJdfSwic2lnbl9pbl9wcm92aWRlciI6InBhc3N3b3JkIn19.m9O8RBSbGxEM7QmXiMjqD2TJQ0qFD6Cy_YP2j80QZWemvLEGp-2Q6owGDBQvfglFSmdbmmwRfXogcA_xc4VJ2S4cV045uAb3IajPpaYfaWzVaOeO4Fj5b2fHNR8frpgWQ4jlyIs1BQgAVEYQ2yB9HDSjrLsEkjdnoKw5bN1k39mWrui2VK0LYZvYnPgEPi_5j2OuLCtMkRJkJavrxsqPHIwWzlUUW-7qUJk-y0kfXCdMfGab2Fnoq9_-cd_ctUE2b9R5yIHtV_yafRRCYAKesCf2H0Y937BMgARuqh3aOL9q2tUhlfRJ1YQ3lg9eWiI7DiYVBo4el6PA0jCyINah_A','2016-12-14 18:50:02'),(114,49112,'Opal5','','Test5','QA_Opal','We Da Best','ffd8ffe000104a46494600010101006000600000ffdb00430001010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffdb00430101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffc0001108007800f003012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00fefd80181c0e83b52e07a0fca81d07d07f2a5a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f957c51fb6dffc1413f660ff00827ef81b44f19fed13e32bbd3b50f18de5fe97f0e3e1c7847479fc55f13fe26eada5410dd6a963e0cf09d9bc2d3db6936f736b2eb9e23d6ef743f0878796f74f1e20f11696da969eb75f6c57f9af7fc1593e3c699fb68fedf9f1f7e246b30699e2cf047c35f136adfb3bfc16875286df59d274af87df09357bef0f788b52d0a2bb89ededff00e13ef8a56fe36f165edfc112dcea5a5cbe17b69e796db46d3d21f0b88b3ca59065ef19520ea549d58e1f0f4d24d4ab4e339a735cf4dba708539ce7cb28b7caa09c5c9497bfc3991d4cff00315838cfd9d2a74e588c4d4bb8b8d184a107183e4a89549cea4630bc2495dcdc64a2d3fd51f8f5ff00072c7ed47e2fbdbfd3bf66cfd9ff00e15fc0ef0db0960b2f127c67bed57e30fc4874f31bc9bf6f09783357f04f803c3776f0ec26ca4f16fc43b68999834f2e001f983f143fe0b51ff0562ba58f5fd43f6d3f10e91e13b79e287c49a2f80fe0dfecf7e109b4ab1bfba86d4788746d61be15ebbe20922d1659a29751d2751d5ef7cfd27ed5756d7d0deda2a5dfc6bfb2ff00ecb3f1a7e3ff00c72b0f81ff00b3e78762d674cd67c357be2ad7af7c4daddcd87813e09d8e97aae9fa74fe26d7b5978355d62cbc27e223aa1b3d1fc25a1d8eb3ab5ff8974d9adbc37a65ae9f3eaf75a4ff00425e0eff00837c7e156a5a3bd97c7efda53e2bf8d46a5666db5cf0d7c22d0bc23f09fc2d2a4e805cd9c1ac6bda7fc47f1cdc5a37282e63d6b44b9953e716f68cc113f883c67fa687863e0966b85cb7c45f116382cd71984a798d1e16e1eca71999e7cf2daf2942356b6132ac3e22393d4aaa153ea53cf334c02c4b87b7c1e2311865ed8fd7709c1f92aa1568e17249e231941f24ea57a92c4538e222a33a4e75b133a385a94aa270a938d2a09aa53e5a9429cdb82fc9697fe0aebff0550f877a9f86753d13f6f1f8bd7d6f7be2ad3bc39ab697e2ff0005fecf3e34b5b85f10192c2c6ead93c43f05ae9a1934dd485bcd3dbc1246b77a7c979b1ede7820987e967eccbff072b7ed3ff0a3c7be0df0b7ed97e06f08fed05f0a3c47fdac9ae7c42f84de10ff00857bf1b7c156fa34761757daf2f826cf57d47c0bf14acad34fb8b8bcbaf0ee8165f0ff00c47225b4aba18d7b5296c341bbfa9fc4bff040ff00d867c4167a7c76de24fda7b41d5747bcfed4d275cb3f8dc3579ecf574b5b9b487529348f14f84b5df0e5ecb0417972b1c175a3b5ba1959e28e394248bf849ff0504ff826cfc55fd80fc55e19f89367e27d47f68ef84fe3fbd1f0efe1ef8c2f6c742f87fadfc33f185ddbcfad2f833e25e9da6b1f0ee3c75068b25de8df133c3b6a24d5ee7406f07c9e10d16fbfb197c41f23e0b7ed02f077c5de29cbf83f85f8c73cc2f13e329e32396645c7192bca7fd61ad4a955c42c2e0332862731cad6229528d4c4aa38acdb2fc7626952961b0543135230a32334e0fcb1d39ac4647f50a75eb50853c5e09c25f56bf2466dd3c2ce72e6acd2a14693c2d6a4eacd4dca2e7271ff00480f86bf11fc07f187e1f782be2b7c2ff1468fe37f875f117c31a2f8cbc11e2fd06e05de8fe23f0c7886c20d4f47d5f4f9f6a3982f6cae219424d1c5710b3341730c3711c91276d81e83f2afc4bff8377fc3be38f0e7fc1267f6748bc6f7ad7dfdb7affc70f16f83a4104f6b6a3e1ff8b3e38fc41d7fc24fa3dadc7efa0f0ddf585f9d57c2aadf2cbe1ad4349b88c08a6403f6d6bfd26a351d5a34aab8f2bab4a9d4e5d7dde782972fbca32babdbde8c5f78a7a2fc22bd3f635ab51bf37b2ab529f32b7bdc9371e6f76528eb6bfbb292ed26b51303d07e54607a0fca968ad0c84c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a2801303d07e54607a0fca968a004c0f41f95181e83f2a5a28010741f41fca9690741f41fca96800a28a2800a28a2800a28a2800a28a2803e7ff00dac3e2fafecfbfb2f7ed17f1d1a58627f83ff03fe29fc4ab533902292ffc17e08d6fc41a75b9dd9566babfb0b6b68d08c3c92aa7f157f96ff876d6eec740d16d35195ee353874ab01ab5cc8c5a4bad624b68e5d5ef2573f33cd7ba9c9777733b659e59ddd8e58d7fa177fc177f5eb8f0ff00fc125bf6cc92da56865d7bc17e0af023329219edfe23fc5bf87de00bd8463a89ecbc4b710baf428ec0fcb9aff3eb9a4433905955e69a41121655795be790a46a4832388d5e42a80911a3b9015188fc97c4eaf2e6c9f0cafcaa38dc44adb37fecf4e0dabfd94aa5aeb4bc8fd6bc31a0b9338c4b5ef3960f0f17d92f6d52693dfde94a95edbb8c53e87f4bbff0406f005b59fc26fda63e2f491236a3e33f8cda07c33b1b92aa648bc39f0a3c01a2eb06d23931b96193c59f137c4b3ca8a42bcb1465c168908fddcf10f8a7c2de10b28b52f17f8a3c35e12d36794c106a3e2ad7f48f0e69f34eb826086f75abcb1b5966008262495a4c73b715f887ff00048cf885a47c1aff008262fc4cf8a1acc9a6403c3bf1aff6a3f124167ac6a306936fac6a9e1ed4acf4ed274a3733491498d46e749d3f4f736e24ba8e2949863791110fee3fc2ff00857fb10fc38f8e1e17fd9d3e347893e15fc6ff00dbf3c7ff0009e7f8d3ae3fc55d1b4cf157c42f11782ac359b9d075bd6be1c687e20d3f51d23e1a7c25d23c429a9e89e13f00f840e916d0e95a3cf77a8c1e24d72c3c4be28bbff0a70df430e2bfa697d2a3e9259e63f8ce9703706f0271b50e14c56795326a99fe65996372acba8e4996e5393e58f31ca70decb0595e474aae638fc466308e1beb181850c2636789acf09f659c71ad0e17cbf0518611e331b8f9e37111a4eafb2a54a9fd6aa2e6ab350a924df34630a6a1aa8cbde8a49bb1617f63aad8da6a9a55f596a9a5dfc42e2c353d36eedf50d3afedc92a27b2beb4926b5bb80b0204d6f349192080d915f2afedadf02bc19fb507c09d73f67ef164be02bdbbf177887e1c78ab40f067c41f156afe13d17c73a87c3cf88be19f1c5bf84b51d4fc297d67e3cd2f43f1b45e1fbbf04eafe21f05a5ceb9a369de20bcbfb1b5bf92dce9f75e89fb75fc1df0f7ec7be11d23f682fd957c37e17f8597fe37f8bff083e08fc48f873a35b59681f087c437bfb467c49f09fc0ef05fc5e7f00dba41e18d23e207c2bf881e38f0b78c357d67c3d61a4defc43f03da78bbc29e306d6eeae7c2faa7867d97e267c25ff827b7ecad61f05fc25f1abe0de81f11bc4bfb4d7c66f097ecfda178f7e20fc209fe3efc45f891f16fc75a6eab77a7dffc4bf1cdcf85fc49a8687a5df2683a8dcdd6b3aacfa07807c1e862b2d1ed3c39a1c1696567fa0f877fb247c54e0ff18a79fe0fc6bc9387f24e01ccb86b8b7c39e3ac3706ff006e66b9d7106071f2cc70d87ccf83717c4184c1e570c8f199761e5992c46759a60f34a18bc253c2d3ad1af9852cbbc0cc3c53c062f2954259454ab5f190af86c7615e29d2a7468ca118f3d2c4c68ca739555397b3b5384a9ca0e4da6a1cdf5efecade3ff0c7c51fd9bbe0878efc19e048fe16f85f5ff869e139346f8636f1e9f1da7c39b6b0d2adf4a6f01d87f645bda691269be0f9ac65f0f69b71a4dadae977561a75bdce9d6d059cb0449efd5f9eff00b2de869fb397c7bf8a9fb15f85eff50bef823e12f849f0bbe397c00d0b52b8bad4aebe0f7847c67e27f889f0fbc4df04ac758bdb9b9d42f3e1ff0086b5df87b65e23f85b6daa4d737be1dd1bc57ad7816cae87857c19e18b1b1fd08aff007ae92aaa95255e70a95d5382ad529d3952a73aaa29549d3a72a95654e129de5184aad49422d45d49b5ccff001376bbe54d46eec9bbb4afa26ecaeedbbb2bf641451456820a28a2800a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28af08fda8753f8bda2fece7f1bf56f8056af79f1a34ef861e32bbf865041a65a6b77a7c61068778fa3cda5685a891a6ebdaddb5c0173a1e85a915d375ad621b1d2f506167773913397246536a72518b938d384aa4da8a6da85382739cddad18453949da314db48695da5a6aedabb2d7bb7a25e6f63ddebf2f2e7fe0a2de25865d53e255bfece7ab6a9fb2be91aeeb1a7dc7c53d1fc78baafc5bb9f08681aadd687ac7c62d2fe0258f83276d4fe18d95e585feb620b2f88f37c4ebef025abf8af4df871797925bf86a7f9c7e16db78863f0a787fe30fec97fb4e7c4fd7358bcb376bc9be37f8ff00c7bf187e1e7c5cd574b996db5dd0fe34781bc6f7d36b9f0c7c6c9aa59cfa3f89aefe16d97c2ff18fc37d763b8d2352f086a3a5e8cde009b8cf807f173c39a67c5af177c0e8346bbf872fae4de29f899e13f84faeea7617be21f841e333aa596b7f1b3e0edbdd5a3470f893c0116b7e2ed2fe337c01f1ee91690f87bc63f0bfc7fae685a4dbe913fc25d6fc3fa37f945e297ed11afc45e15e7fc73f46ecbd438cbc1fe31c0e37c65f0a7c57e1eab94f1b52f0db0f8daf93e6d9a65b97e0735c4c2384c3e775b2ac2e7f8dc1e23179970be1ebd4c466d83cb5d3a6b17fa4e5bc0ca8e6387c1e7f3b61736c2ce394e65975755706f1ee11a94e9d4a92a71b4b914fd9c6718c2ac95a0e77f77f7c745d6b47f1268da4f88bc3daa69fae681afe9961ad687ade91796fa8e93ac68faadac57da66aba66a16924b6b7da7ea1653c17765796d2cb6f756d3453c323c6eac74ebf327f63cf16693f03bc7de20fd96755be1a57827c637dadfc4efd98a1be7f274bb1b4d4669352f8b1f01fc3d73288ed926f02788e6b9f889e06f0a4729b983e1af8d2eb46f0dd91f0e7c2cd4ffb33eb4f8cdfb537c0af805aa787fc3bf12fc6cf67e30f15dbdc5ef867e1ff00853c31e2ff00891f11f5dd36ce430ddeb3a6fc3cf871a078afc693787ecee00b4bdf11b6869a0d95dbc56b77a9413cb1c6dfe867859e30f05f8b3e14f0bf8c19066586c2709f126454b39ad5f31c661684721ad4ef4738cab39c54ea430b86c66439952c565998ce552342388c2cea539ca84e9d497c3e63956332cccb119556a53962b0f59d251842527556f4ea528a4dca3560e3385aeed2b3d5347e687fc1c4179269dff0490fda53514824b9161e26fd9c6f24b589fca6ba5b7fda6fe0fcab69e690cb17daa658ad848e0a46d28761806bf902ff008282fec35f0fbf65ef097ecc9e1cd434cfda3f5af8c57de2cd72d3e307c5dbe3ae7853f673f126bf7df07351f145ff00803e16b6957d64233e13bbbe8b4dd3ef5a086f7c47a7e97e26375e22f116a7a6ebd69a27f601ff00057af127c36fdae3fe08c3fb67f887e1678963f197841be1bcde2092ff004c8352d2b58d1f53f833f127c35e2ef13695ab68bad58e9fe21f0a78b3c297be0cbeb7d6340f11e93a6eb9a0ea36525beaba6db4d13c55fcbc7c7aff0082967857f6b7fd97be1afecdbfb4dfc0ff001a7fc2c6d07e26fc139afbf68bf873f107c23a0e83a649a1f8c745f09f88fe2ae87a2ead67aa78934af15f887e1d6b9e2fd3b5ff00056a1e1af107840dd6bfa9345ae4fa6a599b7fc0fe92b8ff0013f03c7fe01710700e071d9f7014734e23a1e25e1b21cef2ecb71f1caf36cb70583e1ce21847178bc1ff00ac391e49f5bce71b9864981c54eb636bd6ca3174b078dc460f053c37de70347fd833555635a51c3637078974614a7521374bdfad471108ff000dce9d06a855aabd8c270ab0ab282a899f777fc116bf653fd97fe237ec45e2e3e38f813f0bbc5faddcfc76f8ede11d57c45e25f09691e22f1745a3ea0344bcb282c3c53afdaea7af6937767a3f88625d3351d3ef60bdb17105ddbce2e63129fdc3f86ff1dbe23fc17bbd0d7e39fecb7e22fda3fc7fe03f0a1f871e03fdac3e08d87c1dd57e2478c7e1bfdae1b9834af89ba27c46f1afc3bf19780fc5576d63a6ddf8e2d7c1fab78b7e19f8b3c4b6b278bec27f0c9d423f0a683f197867c21fb27ffc1197f650f8a1e2af1a7c52f1edafc1cd1fc7771e3bf1978d3e21cf6be2ef15eabe2ff15c5e1cf0568fa1f86bc3fe09f0cf87ed27bad462d0b42d374cd1744d02169a78af759d5ef047fda17f07c67f127fe0e06fd8d7c49f06750d7bf636f1537c74f8e1a9b8d3342f87be2bf05fc40f02e95e009274904be38f8b975abe8da522f853452abf66d0fc27acdfebbe34d5a4b3d1349bad36ca6d53c45a2ff919e16fd213e96afc75f15fc4bfa2ff000a713f89fe14f89fc7d2ca955e3be18e27c7787f80c7462b1d83ad8dcd6199e5d4f82ea64b4336af3ad379de5d838e598cc3ff006a60a73fa851c3fde66f9070b63b0b81cbb33ab0c06674615abe1b0f81a986a798d4a188ad294692a1c95215f9946292e5972b84dc2718a9b5fab5fb4445ad7edb9a5436bfb557806d3e0a7ec9de10d461d7f4ef849e25f8976169e3af1ef8ea0692d7c39e38f8c3e3ff0086fe26b6f0cfc3cd1bc15f6d6bcf047c3cf04fc43f12dfea1e339b4ef1778a3c53633f87b44f0cd717f1813c49fb287c22f1afed01f0c7f69bf8cde0cd4be1af87ae7c59a069bf1d7e3e78ff00e33fc13f1edfd847e768ff000c7c59a0fc66f1478aee0697f12efcdaf83acf54f007883c33e3cd3750d6ac752f0beb0d7b6cb637bfc18fed09f1c7f688fda43c61ab78ff00e3d78ffc47f15f53834f97595f0ff8ead6def7c376767a9eaada4f86341d1be1e047f0378122f11eb2b21b0f0ef85b45b793c29e17b49750f11eade29f16de437569c3f896dafb4eb8b8d2163d2fecfe19b2f0d6b198f469359b0b4d2f59fb4e8baaebba4f85354bcbdd2e18341f10699733ea5a569f6f697579e0fd5eeaded2fad755b2b4bb97fbaf11e067d2ab8a7c42e1cf1478b3e9899e70d66d0ccb28c6e75e1df86190e7597786986cb32ac460ea4f86f2acbb1fc5f1c36654b14eb55a389cdf89f86f35c4e63471d1ad8ec0e2a8d158697994329c96865b5f2f870fe1e71e59c56371a9623314eac2acfeb0eb42952b4a11a32b53a5570f4a9ce8ca1ed2f2b2ff00563fd9dfe0b6b3e069fc63f16fe2678ba2f88bf1e7e3541e17baf885e2db0b01a2f84f43f0ef862db537f037c29f861e1d5d4f5b3a07c33f00ff00c247e23bad296f75df106bde24f13789fc59e32f106bd7d7de215b6d3fe9aaff003d7ff823ff00fc1593e2dfec47f127c1df093e2eebbac6abfb1ff887c5da67c3ef1df8235fd4b51d774cfd9d752f105f5a5968df183e0a6bba9f997b63f08a3d4355b1bcf881f0f5653a0693e1bbcd53c4de1fd3740f12f85f5ed2fc55fe8500e402390790472083d0835fea1e519c6133ac2fd6b0beeb8cfd9d7a127194e855e58cf91ca129c2a42509c6a51ad4e73a7569ca328cafcca3f8ee6f9462f25c57d5b129494a0aa50ad15250af49b71e64a718ce138ce32a75694e319d2a91945ab72ca4514515ea1e50514514005145140051451400514514005145140083a0fa0fe54b483a0fa0fe54b4005145140057c51fb557ed0df12fe1a78dfe0e7c1ef837a3f8124f1f7c5ad3be24f8c2ebc5bf13575abff08783fc09f09ffe109b6f12cf6fe14f0deabe1ed7bc6fe2cd5f57f88be15d3b46d0ad3c49e1bb0d3f4b3e21f136afacac3a25b691acfdaf5e23f1cff677f84ffb46787b4ad03e28f87ae2fe7f0ceaff00f092781fc5be1fd6f5af07fc40f877e295b3b8b08bc51f0ffc77e17bed2bc51e12d6d6caeae6cae6e349d4e0b7d5b4cb8bbd175bb6d4f45bdbdd3ae7e438ff002de30ce38278a32bf0ff0088f05c21c6f8fc971d86e16e27cc729a39e60723ceaad171c166189ca711cd431b4a8d4b374eb53c452836ab54c263614de12bf560aa616962f0f531b4278ac242ac6588c3d3a8e8cead24fde846aa4dc1beeacdeca51bf32fc32f8cb61fb5e781fc6baefed0de04f857f09bc6be3cd4665bef891a1fecf579e21f03693f1f34bd3ece382dec7e25fc0bf89bacebba6c7f132deceda3d33c1df1f7e1afc5c6f1fe8f8b1d0fc6de01f8a7e01b6b7f0fe89f71d9683e1fd5353d13e205ff0082b48d3bc763c2f0e9116b1a9e91a0dd78e3c3ba36a862d5b51f05bf8a2ce3baba4b0b7d4e47fed2d374bd566d0ee753825bc83ed2192e1cf18fecf7fb56fc0fb1b8d43e1d6b317ed8de0bb18e575f0678d2ebc1df0b7f68eb4b689621041a17c40b4b3f0efc19f89f75feb145978d742f84ba9c883ed17ff10b58bc3e4cb4be1c78fbc3ff0014bc0fe1df1ff85a5b86d1fc436923fd92fa06b4d6342d62c2e65d37c47e12f12e9ce4cfa2f8bbc1daf5a6a3e19f16e817612fb42f10e97a8e97791c73db3ad7fcc17d3af81be93fc2b9e643c43f487e0de0a59e632798f0ff00fc472f0ef09432da1e2861a785c3bc2e5bc531c96a657964b31cb70584c547052ccb83f86f3ec765b52be1b172cd72ecb304f07fd17c118ce1bc4d3af87c8f158bf631e4af1c9b3097b5797cf99f354c2caaaa953964dc54953c455a50924e3cb394dc97c6be0cf027c51d0757f0278e346d1fc59a349fd9d79a9e87753b0bdd32e4492cfa16b76973a7dcdb6b9e17d6ad2e6096efc37e26d22ef4ad6b4dbdb77bdd1352b6bab732a78ce95e1ff87ffb24f807c71e3ff13789be23fc48f11f8835ab06f1378fbc637f7df12fe3cfc58d6353d6d3c33f07fe1369da95cb1d7bc67a969dfdade1ef859f093c23f682d7779345a9ea77171e25f1178bbc51a9749f153e0bdbf8bfc49e0bf8a5e093a6f84fe34f807c41e1c6d2fc7090bda4de21f004fe21d3a1f1f7c2bf1b4d6111b9f13f82fc5be169b57b6d2f44d556f2df40f1bffc235e2cd0869bad69315d9f9de5fdadbe04f8cbfe0a53fb187ecd767ab378dcf84be257c4dd77c57ac78721d3fc45e0bf0a7c77b6f81bf106dbe12fc3df11ea515cbc12f8aad7c3b77f13bc5da941a347aa5c7c3fd7347f02ff00c24c9a35debf6335a7e6df462f0eb89fc72e31e03f01b26e39e32a5e1771d7146033cf16f87701531582caf217c311752ae6d5e8bc4e2f23ab3cd3098aa19670be6f8da742be2b88b1184cbf159457ad94e555b1bd5c559be5fc3d85c5e778ec3e5b4733a0a181c9b135e74b9f1588c7ca34f0f86a7cea15a55bda25cd429f3b7057849467351fd36fd9fbf653be93f662f8d3f0e7f686d3ad22f11fed7ba9fc54f177c73f06e85a95bea1a67846dfe30f84ac3c02ff000e748d7ade016fac5df837e1a68de1bf0b6afe2a8237b5f11f8b74ed6fc4f611c761a9da5b43fe77ff00b707ec9df1cbf612f10fc46f817f1efc37afd8ea5e1ed0bc4f17c35f8a91e85aac9e01f8e3e19d234dba97c2be3af04f896ded6e3499b5ed4ad60d3ae3c5de0596f63f157837c52750d3aef4e9b4a1a4eb5aa7fa9c57cd9fb5e78ef46f007ecedf12ef356d1f55f135e78b7464f857e10f07e83fd9cbaf78d7e20fc5ebbb7f867e01f07e8d2eaf7ba6e936779e22f15f8a74ad35f54d5f51d3f46d12ca7bbd6f5abfb1d234ebebb87feb0b0fc03c3782e1ee17e14cab0bfd9192707e5d9564bc3d86c34a53865b93e4f84c2e5d82c073625d5a95a851c160f0d45cab5495797b18d49d694dcdcff02ca789331cb3158ec44147172cd15458ca5554ad5ead495492ab174f95c6aa9d5a9cb64e3cb5271e4d62e3f85dff000516fd912f7fe0a57ff04e6f127c11d1b5eb2f0ef8efc7fe0df85ff15be1c6bbaab30d1a0f889a0da691e31d1ecb5b9515de0d1fc470dcea7e19bfbe4490e991eb4356f2e51626293f84cd7ffe09b5fb4ffecbde2dbbf0568bf1d2c7e0beaba9d95d5d789740f8f3e13bef83d3c1e25d161d9a75835e6ab1f8c7c0de32d175549af61d0bc75e01f147897c3f305459a48a1bd8ae6bfd1aff00665f15f86f5ff833e03f0d68ba887f117c2af04f823e1a7c49f09df44da6f8bfe1e78e7c1fe15d2b40d73c2be38f0c5cac3aaf86b58b5bed3ae0dbc77f6b0d9eb1a7fd9f5bd02e754d0afac352b9f75beb4b4d4ecdf4ed4eced353d3a439934fd4ad60bfb1909c83e659ddc735b3f539dd11aff988f02be9a7e277d106b71cf84589e138e6bc29478db3cccabf09e7b87a39367191e795561f2dc4aaeb36c8f35acb0ef0f96e5f89fec9ab84c1ca18da0b111c5429e23154711fd2f81ca386f31ab86ce734ca296735eae0b0349e2686719c6538da0b0fcd526b038eca71b0c3d29625559e1b16f1b97e64fd8f2fd5d61aac1ce5fe749ff0499f86563fb5cfed1df1c3f670fdaebf69cd43e1578e3c13e14b187e13787bc127e14799f12be24de78a2d7436d26cbc5d75e0cf1ae83ad6976f0dd69d71043a1dbcb757d63ac36bf6f72ba7e85a8a1fea93f625ff00820d7ec51fb42fc29b0d77c79f1ebf6c58be38f81174af03fed17f0d2e359f83bf0f356f869f17adf42d275af11f8624d2f4cf8357da87fc227a87f6a41e22f87fe23d23c51adf87fc63e0bd5745f13f87bc47abd9df0baafd7dbff04de68da15dd87c13d33e16fc3bf15df4f6705a6aba8fc3c179e1c8209aee31a8bdff0087fc15acf80b53d5267b47b8fb244be23b087ed8d1bddb4b009227f46ff8258d8691e29fd9ab4dfda46f3c6fa5fc48f8affb4c4b61e34f8bfe2ad0e3d1acbc3fa4f893c25a7a7806cbe17f86341d0ef35287c2fe1ef85b69a0cde1d5d0357d5f5cf15c3e226f126a1e2cd66f75ed4af560ff0055be843f498cfbe953e2e78ad9dd2e13c8722f0e32ae14e12862785334cd68e719bf0d714a957c260f1fc314709c1f9360a9641c5d470f9f62b3cc3e6b9b67599e1b33cab2efa9e2b0f8294b0f88fc978de8e3786f0983a3473acebdbd6c5e3278797d7abd4e6c04a7cf0c3e2f15eda94b1189c1c5d1a71af4b0783a3594ea4fead072b43c6fe0dffc1057fe0999f07f52d3b5bbaf827ad7c67d734abbb6beb0bdf8ff00f11bc6bf15b4b86ead2513c32b781b5ad5e2f86f71b6654942dcf83664574528aa0107f63800a02a8000000006000380001c0007000e94b457fa9d430f87c343d9e1a851c3d3bdf92852a74617efc94e318dfe47e4d5f1388c54fda626bd6c454b5bda57ab52b4eddb9ea4a52b7cc28a28ad8c428a28a0028a28a0028a28a0028a28a0028a28a0041d07d07f2a5a41d07d07f2a5a0028a28a0028a28a00f2af8ebf177c39f007e0b7c58f8e1e2f49e6f0c7c22f875e31f88daddadabc51de5fe9fe0fd02ff005d9f4db069cac3fda1a98b1161a7ac8c11ef6e604270d5fe7a5e13fda27f6acf82be3cf899f133c07e20f881e05d4fe337c46f1e7c5df1ac5f076e741f881e1d9bc53f133c5bac78d756d2fc79f06be2ec7ad697e27d6bc2f77aecde1ab1f89be148cf8b7c49a2693a6c9ad7f673c51d9c7fda0ffc1686eee6cffe098bfb58bdb332fda3c25e0fd3af0a9233a4eadf14bc0ba5eb4ae47fcb27d22f2f9260786899d5be526bf8d6b824dc4e4f5334a4fd77b66bf913e9538ec2e2f09c3bc259c64d93f1070ee694b31c766b93e7b97d0ccb2ec7d5c354c251c1fb7c262a1530d5d61955c4ce147154b1185756ad2ad5b0d5674283a7f8c78a7c5d9e70d661c3bfd898ec565d5a31c6e31e270589c460f13ed62e8d0a5cb88c355a55396109d7e7a3394f0f5bda45d7a353d9d3e5e57e2f7ed59f1dbf68cd3ed744f8dfae7ed35f1d34cb3bfb3d474df87f37c34d0be07fc31b6d6b4db98ef34bd6b53d1b48b7f867e1cd5354d32ea34b8d3b56f166b5e2a3a4ce9f6bd22d6dae9564af67ff00827e5c7c47f037ede3fb0b78c74b83c3fe11d50fed07a27c35d07e17f86a0d3b56f0ae8fe05f89de1cf1668bf14a7d5aedf4ab3b7d5bc68fe075d6752b1d6f40b1d1ed3c2afa75cdbe933ea91eb5afdf6abe795d07c21f8f9aafecd9fb56fecc5f16f44f00e85f12b56f05eaff00187c4ba47867c4de24bdf0ae851eb317c1ef1278374dd76fb54d3bc3be28be93fb13fe13eb9b9b7d3edb4c8e5bbb978b6ea3a7f95e71fc13c29c4f0ff0cf1370ae0323ca726e0be1acab35966d88a39560a86072fc060f0942b63b3596132acab0b83ca306f1985c3d5a58aad81ca2198622152a529622a42b55a557f2fcb78c736cef8bf26cd388739c7e2a786c6c3178accf35c7e2f30af430584a72c462e9d1849fd5f0f4a786a35294a384c1d3ab284e54d49c6a4e13ff00450afc80ff0082b9fed55f0eff00664d1ff636b9f1d47af6b30eadfb5d781fc6fad785bc23a2dcebde2a3f0c3e13685e20d77c77f106cb4e84a8b9d27e186b9abfc3ff0010ebb65099f5bd6e09edfc37e14d3b52f14eb7a3dab7f3affb42ff00c1457f6e4fda6af2683c63f1fb5ef84fe0491982fc2bfd99db51f83da2dc42f1b4325bf897e235aea9aa7c66f1324f1315ba82c7c75e11d0aec33893c3688de58fcddf167c39433378e7c21a8f892d3e26f876cf54bbd0356d5bc69e34f12dbeb5e79b5bfd4fc29e23b3f13788358b6bed17c5f2e976369a9dd2c516af697b0697ae596a115fe8f686bfaa33dfa48708d29d5c0f0f51c6e638aa90953c2e658cc24b0b92431137cb4a58a8d4af4335faa5dfefea53c1c6a5387bf4e9d6b729fabe63e33e4586aae964b4b118cc4c5a786c6e2f0ee8e56aba9274de2213ad471cf0f756ab28e1e338c7de846a2d0fee57c5df03ff0066efda9b4df07fc63b64b2d7750d67c2f637df0e7f687f82de39d6fc11e3cbaf07ea70fda34b97c37f16be1c6afa36b5ae785a5490cd6da06b57dadf85d6632a5ce85f6817110f30ff008639f8a9a6cd1c3e10fdbc3f68cd3b4c0caa965f103c13fb32fc4e9204c803ccf116abf05342f114f1a2fdeb8d4f56beb9d80bcd72ed973fcb7fec9ffb627c7dfd99acb4ff00187ecdbe30d361f87de3c82cbc71ae7c04f89967a87883e0cebf7fe24b48354bdd5b4fb4d2aeac3c47f0a7c6d7cd73ff0013bf14fc39bdb7d375bd4637d43c5be0bf175e812d7ea9f8d3fe0b67a7f8eff67cf8c3e0bd4bf669f8cff0cbe3af8abe14f8fbc1de05d43c1fe20f871f147e1747e38f13f85354d03c3daf0f1abf897c0be2ed1744d2f57d42db56b99f5ff871637b67696afb60bcb855497e7b318fd1c7c6584315e26704786b9be7581a6b0f89c2f893c31c339966b977b06e3530d81cdf3ac054788c3d3929aa51c162a137149d5c261e72f66bf66e16f1bf83336c1549e2335970ae714a9cbebb97e331b3cbe9fb782f7e34712a74b078b8caac6d171fdf5ad2a94694a4e2ba3f86769f14bc6dad7c34f881fb4efed1bf177e277ec7ff00b43f8e7c55f0e7c27a06830f83ff0067ff00f844acbc49e32baf0f7ecd3e29f8b7ad7c13f0e783fc59e27f00fed17a5db41a26bd670f8b341d3fc27e27f1efc30d2ee9755d0f5ef11dcd87dc7af7ed0dfb2a7fc1343f6b1f865e11f1978cbc11fb3efc1dfdacfe0f789f4db2f0258e89368be0af0d7c4cfd9967f05e9da378eadb48f0ee9efa4f84ac3c55f0c3c7e9e08f176bf736961a7dcbfc2bf012ea179e7417370bfcb678fbf6f6fdbe7e20fc013fb2d49e17f82be1cf004be0bd23e1aeb1e2cf047c3fd73c39f116efe1fe8d6ba8e97a6d968573e27f1ef897c01e05f14de7877c25a6adb78a749d03509fc27e27d77c377ba35b68f7100d42c733f68ff885f1bff6bbd713e22fc75f17f87ee7e30787fc25e08f08fc36f12784ac6f74fd13c107e1c4535de95e20b48efc9bf7d5be22f8c6f75ef1a7c580162b2d4a3f14ddf832c609340d07499e6f278278b3c2af07f87b32c0f07f0bf00f0cd4c7670ab61728e03c832ee1ec0e330b8aaefd862b39593653845379461aa56b623114ab62eb61bd8d0a1ed2b3acd789c79e36701fd417f626618ccfb1d568e57183c6ac5af63889350c6ce55ebfb4ad4e852a50588ab08d28c2a4a74d61956729aa1fe853f0f7e267c39f8b5e1ab3f19fc2bf1f7833e24f84350245978a3c07e27d17c5de1fba6555678e0d6341bdbfb092545743244b70648f700eaa4e2bb7aff378f82bf10bc51e17b9d37e2f7c1bf1578d3e017c4d99a6b4f106b5f0b7c413784f5eb0f1268b7d269fe21f0d78b6ceda39fc33e3cb3d2f5bb2bab36d37e20787bc51a46a16496f746c1a1ba898ff0040bfb267fc177bc55e10934ef067ede3e19b7d73c303cbb68ff69ff843e18bb8e5d223dd1a7dbbe34fc15d28ea7a8e9f651a1966d4bc79f093fb7347842fda753f875e0ed2639f508bf5ae10f1d384b88f17fd919aaa9c2d9e2ad2c33c1e675e954c0d6c4c67ece5470b9b4153c3cea7b45c908e2a9e0e55a4e30c3aaf29247c570f78a1906735fea18fe6c8f33551d1f618dab4e784ab594b91d3a198454293939fbb18e221867395a34bdab6affd41515ca781bc77e0af89de10f0f7c40f871e2df0df8f3c0be2dd320d67c2fe30f086b5a7788bc35e21d26e41306a3a3eb7a4dc5d69da8da48559567b5b89537a3c64874651d5d7ed67e94145145001451450014514500145145001451450020e83e83f952d20e83e83f952d0014514500145145007c69ff0512f85377f1bbf612fdae7e1769b6f2ddeb7e2afd9fbe27c7e1bb5810c935c78b34af0b6a1aef84e18907cc6493c49a6696a9b72e188280b000ff093a3eb36de23d1f47f11d990d67e21d274cd7ad194820daeb36306a56e411c730dd27d3a57fa401018156008208208c820f041078208e083d6bf806fdac7f672b8fd8e7f6a7f8bff00b35b584ba6f84b4bd5effe287c057916516bad7ecfbf10f5abed57c356da44f37cd783e14f892e75cf83dad44a5a5d353c31e18beba58edbc51a44975fcc3f49ce1cc463f20c8f8930d4e5523916331384c7f245c9d3c1e6cb0ea9e26a35f0d2a58cc1d1c3b7d6a63699f8978d793d5c565595e7346129c72bc457c3e294536a14330f61c95a76da14f1186a746ffcd8989e13587a8787ec752d73c33e219dee1351f0a1d7869be4baac12c5e24d3134bd4e0bc8d919a588c70dadc41e5bc4f1dd5ac2e59a3f3237dca2bf8a6955a9464e74a6e12953ad45b5bba588a3530f5e0eff0066ad1ab529c9758cdaea7f374272837283716e3520dafe4ab0952a91f49d39ca12ef193415c7f8f7c50fe10f0b6a5abda5a1d4b5c9bcad1bc25a2a30137883c65ac9363e19d120079ff4bd49e39afa51f2d8e916ba96a7314b6b19e4497c61e35f0ff81b4db8d475d96fe6962d275bd72db41d074abff11f8af57d33c39a75c6adaedde89e19d221b9d5b51b5d234db4b8bdd5b514823d2746b389ef75ad474db247b94e77c2fa1ebdae6af69f103c7967169bab416b7117833c1315cc57f6bf0ff4ed4e011deddea57d0ffa2eaff10b5bb36169aeea969bb4dd034e32785fc3b2cf6d2eb3ab6b7eae0f2f952a14739cc70f38e53eda71c3fb453a6b37c4e1b925530183968ea420e7496638aa6dd3c050aa9ca4f175f0385c576d0c2b853a78fc55292c139cd52e6528ac7d6a2e0e785a12d1c945ce1f5bad07cb85a534e52f6f530d46bf51e0bf0e7fc21fe0df09f84fed0b76de19f0de89a14d769bbcbbbb9d334eb7b4bbba88300c21b9ba8e69e1560192291118656ba5a28af2ebd6ab89ad5b115a5cf5b1156a57ab3b25cf56acdd4a92b2b25cd393765a2be871ce73ab39d49bbcea4e5526ec95e7393949d968af26dd96819a28a2b220f2b81ffe112f8ad3e9e7f77a1fc5bd36e35ab21d22b6f891e0db0b7835e8500c2a49e2cf0447a6ead83f34d79e0bd62e3996ea563eaaacca43292aca432b292acac0e432b0c1041e4104107915e5ff001834fbfb8f035e6bba22db9f12fc3fbfd3fe23f868ddb5c476afa8f83de4bebeb1bb92d20babc4b1d73c36faf787efcda5adddc7d935598c1697532c70bfb4f8ebc21e3ef84be2cb6f01fc61f07cdf0f3c59aa685a678bbc26cfab58f88bc0bf13fc0dae0b36d0be20fc1af88da6ac3a07c4bf05eac350d3e3173a7c7a7f89b42bdbeb3d2bc61e15f0d6a979656975f515727ccb36c91711e0f055b1385cafeaf956795e8a557ea95a1154f2bc4e26941ba9470d8bc12a383862250f653c660711ed6a46ad7a6aa7b32cbf1b8ecb5e6f87c354ad87c0fb2c1665569a53fabd44b97055ab422f9e9d1af8750c3c2ab8f24b1186abed26aa558297b27eca7fb597c7efd86bc6d3f8cbf679d56cefbc21ae6b0dac7c4dfd9c7c59a8dd597c20f8a8f72546adab697e445767e117c58bb8944967f137c2ba7c961ac6a115aa7c4cf0b78d34f48a6d3bfb41fd8e7f6cef82dfb6ff00c288fe28fc1fd4efadee34bbe1e1df88bf0e7c4f0dbe9bf113e1378de2b586eefbc17e3dd0a0babc4b1d46382786fb4ad56c2eb50f0df8af43b8b1f12785359d6741d42cf509bf840af5dfd9bff6a0f147ec3dfb407827f6a1f0bdedd43e1ad26ef41f077ed1be1781dffb3fe247ecf3a8eb71db7897fb4ac83a4573e29f83e354bbf8a3f0db5772b7da75d693e22f0947709a0f8eb5fb69ff006ef05bc65ccb27cc72ee11e25c4d4c76458ead430196e3311373c564b88ad28d1c2d275a57955cae75250a32a555b7818ca35684e187a53a12fd2fc38f1171996e2f07906735a78acab13529e170988ab272af96d5a928d3a11f6b277a981949c69ca9cdb7864e35294a34a13a52ff00413a2991c91cb1a4b13a4914a8b247246c1d248dd4323a3a92ac8ea432b29218104120d3ebfb88fe9c0a28a2800a28a2800a28a2800a28a28010741f41fca9690741f41fca96800a28a2800a28a2800af8ebf6c8fd857f67dfdb9fc19a1785be35e89ad5b6bbe0bbfbdd5fe1afc52f026b1ff08afc53f865ab6a704369abdcf83fc4eb6b7d07f676bd656f6f67e26f0a788b4bf10782bc516f6b623c45e1bd524d334c92cbec5a2b2af42862a8d5c362a8d2c4e1ebd39d1af87af4a15a856a5522e352956a55232a7529ce2dc6709c651945b524d3b19d5a54abd2a946bd2a75a8d584a9d5a35611a94aad39a719c2a539a94270945b528c938c9369a68fe59bc65ff06f9fc76d36f641f0bbf6caf873e24d19a4736f17c63fd9ff0057b6f135b405cf951de6bbf0c3e29f873c3dab4eb16d125cdaf817c3c9249922d505759f0ebfe0dedf18dddd5acdf1cbf6caf2f49f307f68683fb3f7c17d37c1bab5cc1c6f86dfc73f157c5df1756c99c657ed169e0882ea2ff5905c4526d65fe9b68af80a7e12786b4b15f5c8f06e4bedb9f9f96742a54c2a95f9b4c054ab2c0a8df682c3722d9451f271e00e0c8d6fac2e1dcb9d4e6e6e5942a4e85ef7b7d52752585e5ed1f63ca96c8fe62ffe0a69fb33fecaff00f04f5fd8853e0f7ecf3f0eec744f889fb5ff00c50f02fc1df1dfc4cf126afab78cbe3478efe1c78605dfc5df89f0f89be23f896ef50f13df787751f0d7c3797c3375e19b6beb1f07d949e2e8e2d37c3f64970231f80accceccec72cec598fab31c93f8935fb93ff070078d9758fda33f653f8600fc9e03f83df187e295ca8271fda1f103c5be02f01e8333ae701e2d33c21e368216c06d97976a0905c57e1a57f257d23732857e39c364986853a382e1cc8f0383a385a308d3a142b6314b30a9eca942318528cb0d5f034f9211514a8412d124bf04f17f191abc514b2da31853c364f96e170d4a8538c614a94f109e327ece104a304e8d5c343962924a9455ac90514515f801f95051451400aa4020b2248a08dd1c8a1e375ee922302ae8c32aeac08652411835ce5afc61f8c7f14f42f02fecfbf1b7c7f75e35f05fec4f776da67ecd5e0ebad1b49b2b0f0df803c49a05fd8780bc757fab2c773e20f1978db47f0a4baffc258357d4f528748f0ee99e159adb48f0fda6ab7979ae5ff455e53e30953c3df11fe16f89d57647e25bad6be12eb728e3ce875bd32f3c5fe0ff00338c1367e26f0b5d58dab1e50f89af1171f686cfd5f0de759ae0709c439365f8ec4e0e8f1065356962a387a8e9fd63fb323531ea85571b4e74f1385863b00e94251555e3546a73d2e7a73f6f29ccb1d85c3e6d97e17135b0f4b35c0ca18854aa3a7ed560f9b14a9d4b6b3856a2b138574d35cff59b4b9a0a5097ab573fe28f046adf14b48ff8549e1fb77bbf117c64d5bc37f06bc3d6910ccd71ad7c5bf12693f0f2c044bd58dbc9e2437b2e3fd5db5acf3b6122661d057eb7ff00c1167f65abcf8f3fb4f5c7ed11e20d35a4f841fb27ea17d69a15e5cc3bec3c63fb4b6bba04ba7db69f606485e1bcb6f829e06f10dfeb1afcb1b8fb278f7c6de0fb68241aaf83f5982d7d5f0bf85f17c59c6f90e5d87a73961f0d8ec366799d6516e186cb32fc452c46267392d22eb72c30941bd2589c45183b2936bbf82b24af9ff12e5582a5093a54b15471b8da893e5a382c255a75abce72da2ea251a14aff00156ad4a3f6aebfaf3d2f4fb7d234cd3b4ab4dff65d32c6d34fb6f318bc9f67b2b78eda1dee7977f2e25dcc796393deaf51457fa707f6b05145140051451400514514005145140083a0fa0fe54b45140051451400514514005145140051451401fc79ff00c16fe3d52ebfe0a2369e6db5c35a5afec81f0562d2dd61919248ae7e2efed1f36a2632aa54e2ea2812523f89235272a00fca4fb1de7fcfa5cffdf897ff0088a28aff0036bc77ad38f8afc58b46954c9d2bdeff00f24fe53e67f1cf8a136b8ef3e5fdfcbf7bff00d0ab021f63bcff009f4b9ffbf12fff001147d8ef3fe7d2e7fefc4bff00c451457e45ede7da3f73ff0033e039df97f5f30fb1de7fcfa5cffdf897ff0088a3ec779ff3e973ff007e25ff00e228a28f6f3ed1fb9ff9873bf2febe61f63bcff9f4b9ff00bf12ff00f115e41f1bed6ea0f07e83a87d9ae127d27e2c7c16d42c9bc89030bc3f14fc29a7aac7f2fdf9edb50bab6da397599939dc4128af67876b49e7f92c5a8da59a60632567ac6589a71945ebaa945b8b5d5368efcae6de6597ab2b3c661935aea9d68269ebaa6ae9ad9a6d3d0fb77f65bfd90be3afedb9f1257e1afc14b1bdf0ef8474bd592c7e2efed17a969125cf807e0e693134326ad63a15c5dc4ba5fc41f8dd3d8cca9e13f86fa64d776fa0dfdd58f88fe26cda0f86e182cf5ffee17f67ef809f0c7f661f839e02f811f077401e1cf87bf0eb455d1f44b396e24bed4afa696e27d4359f117887569ffd2f5df15f8a75bbcd47c47e2af105f33dfebde21d5352d5af647b9bb9589457fa19e0bf08649c37c179566197509bc7f10e0307996698dc44a353135aa54a6e54f0f09c614d53c1e1b9e6a8508c74739d4ab3ab5672a8ff00adbc37e1ecb726e1cc0633074a4f179c6130d8cc7626b38ceb5494e0e51a319461050c3d17297b2a4968e5294e539c9c9fb2514515fae9fa0051451400514514005145140051451401ffd9','Unknown','1970-01-01 00:00:00',NULL,0,'muhcap.p.mobile@gmail.com','EN','TESQ00000005','3','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im11aGNhcC5wLm1vYmlsZUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsImlhdCI6MTQ2NjcxMTE3OCwidiI6MCwiZCI6eyJwcm92aWRlciI6InBhc3N3b3JkIiwidWlkIjoiZWMyMjViOTgtNDMzYy00NzBkLWFlN2ItYTJiYmRjODcyNzBhIn19.OhEIW1RzoiVWiA4jG-VZy_8875tvYGvJtl72XSfNDGc','2016-12-14 18:50:02');
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
INSERT INTO `PatientControl` VALUES (110,0,'2017-01-23 16:00:01','2017-01-24 16:43:25'),(111,0,'2017-01-23 16:00:01','2017-01-24 16:43:25'),(112,0,'2017-01-23 16:00:01','2017-01-24 16:43:25'),(113,0,'2017-01-23 16:00:01','2017-01-24 16:43:25'),(114,0,'2017-01-23 16:00:01','2017-01-24 16:43:25');
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
INSERT INTO `PatientMH` VALUES (110,1,NULL,49108,'Opal1','','Test1','QA_Opal','Tommy gun','Male','1981-06-20 00:00:00',5146542334,1,'m.uhcapp.mobile@gmail.com','EN','TESQ00000001','3','INSERT','2017-03-06 16:31:02'),(111,1,NULL,49109,'Opal2','','Test2','QA_Opal','Test123','Female','1970-01-01 00:00:00',NULL,0,'mu.hcapp.mobile@gmail.com','EN','TESQ00000002','3','INSERT','2017-03-06 16:31:02'),(112,1,NULL,49110,'Opal3','','Test3','QA_Opal',NULL,'Unknown','1970-01-01 00:00:00',1234345678,0,'muh.capp.mobile@gmail.com','EN','TESQ00000003','3','INSERT','2017-03-06 16:31:02'),(113,1,NULL,49111,'Opal4','','Test4','QA_Opal','Briana','Unknown','1970-01-01 00:00:00',NULL,0,'muhca.pp.mobile@gmail.com','FR','TESQ00000004','3','INSERT','2017-03-06 16:31:02'),(114,1,NULL,49112,'Opal5','','Test5','QA_Opal','We Da Best','Unknown','1970-01-01 00:00:00',NULL,0,'muhcap.p.mobile@gmail.com','EN','TESQ00000005','3','INSERT','2017-03-06 16:31:02');
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
  `Enabled` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SourceDatabaseSerNum`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SourceDatabase`
--

LOCK TABLES `SourceDatabase` WRITE;
/*!40000 ALTER TABLE `SourceDatabase` DISABLE KEYS */;
INSERT INTO `SourceDatabase` VALUES (1,'Aria',0),(2,'MediVisit',0),(3,'Mosaiq',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (53,'Patient',110,'6e0f7e47-cd90-4323-89bc-e22b1fd09f58','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','1474901325','2016-09-02 14:52:15'),(54,'Patient',111,'82f26b56-fde4-447a-91c4-8c5aa5b05445','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','eyJhbGciOiJSUzI1NiIsImtpZCI6ImY4M2JlMWM2MDljMDZiYWY1M2VmNzc2MDBlZDBiNTJiNTdlNWYyYTQifQ.eyJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXVkIjoiZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXV0aF90aW1lIjoxNDc2ODA0MzMwLCJ1c2VyX2lkIjoiODJmMjZiNTYtZmRlNC00NDdhLTkxYzQtOGM1YWE1YjA1NDQ1Iiwic3ViIjoiODJmMjZiNTYtZmRlNC00NDdhLTkxYzQtOGM1YWE1YjA1NDQ1IiwiaWF0IjoxNDc2ODA0MzMwLCJleHAiOjE0NzY4MDc5MzAsImVtYWlsIjoibXUuaGNhcHAubW9iaWxlQGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwiZmlyZWJhc2UiOnsiaWRlbnRpdGllcyI6eyJlbWFpbCI6WyJtdS5oY2FwcC5tb2JpbGVAZ21haWwuY29tIiwibXUuaGNhcHAubW9iaWxlQGdtYWlsLmNvbSJdfSwic2lnbl9pbl9wcm92aWRlciI6InBhc3N3b3JkIn19.TfUQQxFjy5FDvlmwO-1_jL2_9E8h9tzIMclEVZ9HXQPv8mBfrw_3vEN7D2oJMZuHGmXy4kjTh_90BOrFEyrllnkVAh43O74P5Jtw8Z88JYkNG-ZETuD3foVzxMBCabZYNSdi5DktawJwkFDi4h61IVAGG4RwJjgSnu1pCCdoOOIC9z_cg0_aTCwRZm4dFN_gYwFtwoyiOuRk9A80QxZ6LqR2p5DTj3kLI3rvukfRmo1H73vQlTke3U0xzpTnM3FpxVbU7ekxGpo2amacTutsJ0XpTDStkMVY__1lkePHAUmKAx6lwNhk-ELuzH-OmAn5EsjIpqiLsueawxO0GJTs4w','2016-10-18 15:26:27'),(55,'Patient',112,'622e548a-3547-49d3-a71e-0a12eac4a6d6','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','Admin Panel','2016-06-23 16:18:43'),(56,'Patient',113,'a3c0068a-3503-4210-a88d-0ec47781fe2f','c08d3c9cdc43ba722324a8f3183048c55f6b0ba86e5254f65f2c414653dcf211','undefined','2016-09-09 19:39:59'),(57,'Patient',114,'ec225b98-433c-470d-ae7b-a2bbdc87270a','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','Admin Panel','2016-06-23 16:19:51');
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
INSERT INTO `UsersMH` VALUES (53,1,'1474901325','Patient',110,'6e0f7e47-cd90-4323-89bc-e22b1fd09f58','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','INSERT','2017-03-06 16:49:11'),(54,1,'eyJhbGciOiJSUzI1NiIsImtpZCI6ImY4M2JlMWM2MDljMDZiYWY1M2VmNzc2MDBlZDBiNTJiNTdlNWYyYTQifQ.eyJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXVkIjoiZmlyZWJhc2UtYnJpbGxpYW50LWluZmVybm8tNzY3IiwiYXV0aF90aW1lIjoxNDc2ODA0MzMwLCJ1c2VyX2lkIjoiODJmMjZiNTYtZmRlNC00NDdhLTkxYzQtOGM1YWE1YjA1NDQ1Iiwic3ViIjoiODJmMjZiNTYtZmRlNC00NDdhLTkxYzQtOGM1YWE1YjA1NDQ1IiwiaWF0IjoxNDc2ODA0MzMwLCJleHAiOjE0NzY4MDc5MzAsImVtYWlsIjoibXUuaGNhcHAubW9iaWxlQGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwiZmlyZWJhc2UiOnsiaWRlbnRpdGllcyI6eyJlbWFpbCI6WyJtdS5oY2FwcC5tb2JpbGVAZ21haWwuY29tIiwibXUuaGNhcHAubW9iaWxlQGdtYWlsLmNvbSJdfSwic2lnbl9pbl9wcm92aWRlciI6InBhc3N3b3JkIn19.TfUQQxFjy5FDvlmwO-1_jL2_9E8h9tzIMclEVZ9HXQPv8mBfrw_3vEN7D2oJMZuHGmXy4kjTh_90BOrFEyrllnkVAh43O74P5Jtw8Z88JYkNG-ZETuD3foVzxMBCabZYNSdi5DktawJwkFDi4h61IVAGG4RwJjgSnu1pCCdoOOIC9z_cg0_aTCwRZm4dFN_gYwFtwoyiOuRk9A80QxZ6LqR2p5DTj3kLI3rvukfRmo1H73vQlTke3U0xzpTnM3FpxVbU7ekxGpo2amacTutsJ0XpTDStkMVY__1lkePHAUmKAx6lwNhk-ELuzH-OmAn5EsjIpqiLsueawxO0GJTs4w','Patient',111,'82f26b56-fde4-447a-91c4-8c5aa5b05445','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','INSERT','2017-03-06 16:49:11'),(55,1,'Admin Panel','Patient',112,'622e548a-3547-49d3-a71e-0a12eac4a6d6','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','INSERT','2017-03-06 16:49:11'),(56,1,'undefined','Patient',113,'a3c0068a-3503-4210-a88d-0ec47781fe2f','c08d3c9cdc43ba722324a8f3183048c55f6b0ba86e5254f65f2c414653dcf211','INSERT','2017-03-06 16:49:11'),(57,1,'Admin Panel','Patient',114,'ec225b98-433c-470d-ae7b-a2bbdc87270a','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','INSERT','2017-03-06 16:49:11');
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

-- Dump completed on 2017-03-06 11:50:43
