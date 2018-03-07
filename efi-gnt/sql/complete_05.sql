-- MySQL dump 10.14  Distrib 5.5.56-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: efi_gnt
-- ------------------------------------------------------
-- Server version	5.5.56-MariaDB

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
-- Table structure for table `diagram`
--

DROP TABLE IF EXISTS `diagram`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diagram` (
  `diagram_id` int(11) NOT NULL AUTO_INCREMENT,
  `diagram_key` varchar(255) DEFAULT NULL,
  `diagram_email` varchar(255) DEFAULT NULL,
  `diagram_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT 'NEW',
  `diagram_title` varchar(255) DEFAULT NULL,
  `diagram_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diagram_time_started` datetime NOT NULL,
  `diagram_time_completed` datetime NOT NULL,
  `diagram_type` varchar(10) DEFAULT NULL,
  `diagram_params` text,
  PRIMARY KEY (`diagram_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2154 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gnn`
--

DROP TABLE IF EXISTS `gnn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gnn` (
  `gnn_id` int(11) NOT NULL AUTO_INCREMENT,
  `gnn_email` varchar(255) DEFAULT NULL,
  `gnn_key` varchar(255) DEFAULT NULL,
  `gnn_size` int(11) DEFAULT NULL,
  `gnn_cooccurrence` int(11) NOT NULL,
  `gnn_filename` varchar(255) DEFAULT NULL,
  `gnn_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gnn_time_started` datetime NOT NULL,
  `gnn_time_completed` datetime NOT NULL,
  `gnn_ssn_nodes` int(11) NOT NULL DEFAULT '0',
  `gnn_ssn_edges` int(11) NOT NULL DEFAULT '0',
  `gnn_gnn_pfams` int(11) NOT NULL DEFAULT '0',
  `gnn_gnn_nodes` int(11) NOT NULL DEFAULT '0',
  `gnn_gnn_edges` int(11) NOT NULL DEFAULT '0',
  `gnn_pbs_number` int(11) DEFAULT NULL,
  `gnn_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT NULL,
  PRIMARY KEY (`gnn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6529 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_group`
--

DROP TABLE IF EXISTS `job_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_group` (
  `gnn_id` int(11) DEFAULT NULL,
  `diagram_id` int(11) DEFAULT NULL,
  `user_group` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-07 11:20:27
