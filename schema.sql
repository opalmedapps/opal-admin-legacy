-- MySQL dump 10.13  Distrib 5.5.43, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: Ackeem
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
-- Table structure for table `Room`
--

DROP TABLE IF EXISTS `Room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Room` (
  `RoomSer` int(11) NOT NULL AUTO_INCREMENT,
  `RoomName` varchar(20) NOT NULL,
  `NumTables` int(11) NOT NULL,
  PRIMARY KEY (`RoomSer`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Room`
--

LOCK TABLES `Room` WRITE;
/*!40000 ALTER TABLE `Room` DISABLE KEYS */;
INSERT INTO `Room` VALUES (1,'ContouringRoom',2),(2,'Dosimetry',10),(8,'ClassRoom',14),(9,'Ackeems Room',2),(10,'Johns Room',3),(11,'Living Room',1);
/*!40000 ALTER TABLE `Room` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RoomStaffAssignment`
--

DROP TABLE IF EXISTS `RoomStaffAssignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RoomStaffAssignment` (
  `RoomStaffAssignmentSer` int(11) NOT NULL AUTO_INCREMENT,
  `RoomSer` int(11) NOT NULL,
  `StaffSer` int(11) NOT NULL,
  PRIMARY KEY (`RoomStaffAssignmentSer`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RoomStaffAssignment`
--

LOCK TABLES `RoomStaffAssignment` WRITE;
/*!40000 ALTER TABLE `RoomStaffAssignment` DISABLE KEYS */;
INSERT INTO `RoomStaffAssignment` VALUES (1,2,3),(2,2,1),(3,1,1),(5,8,3),(6,8,4),(8,2,4),(9,10,1),(10,9,4),(11,2,7),(13,8,1),(14,11,1),(15,2,5),(16,2,0),(17,2,8),(18,11,8),(19,10,3);
/*!40000 ALTER TABLE `RoomStaffAssignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `StaffMember`
--

DROP TABLE IF EXISTS `StaffMember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `StaffMember` (
  `StaffSer` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(20) NOT NULL,
  `LastName` varchar(20) NOT NULL,
  PRIMARY KEY (`StaffSer`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `StaffMember`
--

LOCK TABLES `StaffMember` WRITE;
/*!40000 ALTER TABLE `StaffMember` DISABLE KEYS */;
INSERT INTO `StaffMember` VALUES (1,'Ackeem','Berry'),(3,'John','Kildea'),(4,'Bob','Loblaw'),(5,'Peter','Petrelli'),(7,'Nathan','Petrelli'),(8,'New','Person');
/*!40000 ALTER TABLE `StaffMember` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-24 16:37:53
