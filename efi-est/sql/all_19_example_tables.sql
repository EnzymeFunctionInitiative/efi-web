
DROP TABLE IF EXISTS `analysis_example`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `analysis_example` (
  `analysis_id` int(11) NOT NULL AUTO_INCREMENT,
  `analysis_generate_id` int(11) DEFAULT NULL,
  `analysis_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `analysis_min_length` int(11) DEFAULT NULL,
  `analysis_max_length` int(11) DEFAULT NULL,
  `analysis_filter` varchar(4) DEFAULT '',
  `analysis_evalue` int(11) DEFAULT NULL,
  `analysis_name` varchar(255) DEFAULT NULL,
  `analysis_pbs_number` int(11) DEFAULT NULL,
  `analysis_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `analysis_time_started` datetime DEFAULT NULL,
  `analysis_time_completed` datetime DEFAULT NULL,
  `analysis_filter_sequences` int(11) DEFAULT NULL,
  `analysis_custom_cluster` int(11) DEFAULT NULL,
  `analysis_cdhit_opt` varchar(10) DEFAULT NULL,
  `analysis_params` text,
  PRIMARY KEY (`analysis_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `generate_example`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `generate_example` (
  `generate_id` int(11) NOT NULL AUTO_INCREMENT,
  `generate_email` varchar(255) DEFAULT NULL,
  `generate_key` varchar(255) DEFAULT NULL,
  `generate_type` varchar(10) DEFAULT NULL,
  `generate_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `generate_pbs_number` int(11) DEFAULT NULL,
  `generate_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `generate_time_started` datetime DEFAULT NULL,
  `generate_time_completed` datetime DEFAULT NULL,
  `generate_sequence_max` tinyint(1) DEFAULT '0',
  `generate_db_version` int(11) DEFAULT NULL,
  `generate_program` enum('BLAST','BLAST+','DIAMOND','DIAMONDSENSITIVE') DEFAULT NULL,
  `generate_results` text NOT NULL,
  `generate_params` text NOT NULL,
  `generate_parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`generate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

