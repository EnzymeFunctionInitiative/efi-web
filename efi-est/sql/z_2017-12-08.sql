-- MySQL dump 10.13  Distrib 5.1.71, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: efi_est_dev
-- ------------------------------------------------------
-- Server version	5.1.71

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
-- Table structure for table `analysis`
--

DROP TABLE IF EXISTS `analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `analysis` (
  `analysis_id` int(11) NOT NULL AUTO_INCREMENT,
  `analysis_generate_id` int(11) DEFAULT NULL,
  `analysis_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT 'NEW',
  `analysis_min_length` int(11) DEFAULT NULL,
  `analysis_max_length` int(11) DEFAULT NULL,
  `analysis_filter` enum('eval') DEFAULT 'eval',
  `analysis_evalue` int(11) DEFAULT NULL,
  `analysis_name` varchar(255) DEFAULT NULL,
  `analysis_pbs_number` int(11) DEFAULT NULL,
  `analysis_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `analysis_time_started` datetime DEFAULT NULL,
  `analysis_time_completed` datetime DEFAULT NULL,
  `analysis_filter_sequences` int(11) DEFAULT NULL,
  PRIMARY KEY (`analysis_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7657 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_version` (
  `db_version_id` int(11) NOT NULL AUTO_INCREMENT,
  `db_version_date` varchar(255) DEFAULT NULL,
  `db_version_interpro` varchar(255) DEFAULT NULL,
  `db_version_unipro` varchar(255) DEFAULT NULL,
  `db_version_default` tinyint(1) DEFAULT '0',
  `db_version_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`db_version_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_status`
--

DROP TABLE IF EXISTS `email_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_status` (
  `email` varchar(255) NOT NULL,
  `opt_in` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_counts`
--

DROP TABLE IF EXISTS `family_counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_counts` (
  `family_type` varchar(15) DEFAULT NULL,
  `family` varchar(100) DEFAULT NULL,
  `num_members` int(11) DEFAULT NULL,
  `num_uniref90_members` int(11) DEFAULT NULL,
  `num_uniref50_members` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `generate`
--

DROP TABLE IF EXISTS `generate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `generate` (
  `generate_id` int(11) NOT NULL AUTO_INCREMENT,
  `generate_email` varchar(255) DEFAULT NULL,
  `generate_key` varchar(255) DEFAULT NULL,
  `generate_type` enum('BLAST','FAMILIES','FASTA','ACCESSION','FASTA_ID','COLORSSN','CDHIT') DEFAULT NULL,
  `generate_blast` text,
  `generate_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT 'NEW',
  `generate_families` tinytext,
  `generate_evalue` int(11) DEFAULT NULL,
  `generate_pbs_number` int(11) DEFAULT NULL,
  `generate_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `generate_time_started` datetime DEFAULT NULL,
  `generate_time_completed` datetime DEFAULT NULL,
  `generate_sequence_max` tinyint(1) DEFAULT '0',
  `generate_num_seq` int(11) DEFAULT NULL,
  `generate_num_family_seq` int(11) DEFAULT NULL,
  `generate_total_num_file_seq` int(11) DEFAULT NULL,
  `generate_num_matched_file_seq` int(11) DEFAULT NULL,
  `generate_num_unmatched_file_seq` int(11) DEFAULT NULL,
  `generate_fasta_file` varchar(255) DEFAULT NULL,
  `generate_blast_max_sequence` int(11) DEFAULT NULL,
  `generate_fraction` int(11) DEFAULT '1',
  `generate_domain` tinyint(1) DEFAULT '0',
  `generate_seq_id` varchar(255) DEFAULT '1',
  `generate_length_overlap` varchar(255) DEFAULT '1',
  `generate_uniref` enum('--','50','90') DEFAULT '--',
  `generate_no_demux` tinyint(1) DEFAULT '0',
  `generate_db_version` int(11) DEFAULT NULL,
  `generate_program` enum('BLAST','BLAST+','DIAMOND','DIAMONDSENSITIVE') DEFAULT NULL,
  `generate_results` text NOT NULL,
  `generate_params` text NOT NULL,
  PRIMARY KEY (`generate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7433 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `generate_output`
--

DROP TABLE IF EXISTS `generate_output`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `generate_output` (
  `generate_id` int(11) NOT NULL AUTO_INCREMENT,
  `output` text,
  PRIMARY KEY (`generate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pfam_info`
--

DROP TABLE IF EXISTS `pfam_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pfam_info` (
  `pfam` varchar(10) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `long_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pfam`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_token` (
  `user_id` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-12-08 10:07:59
