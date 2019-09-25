
DROP TABLE IF EXISTS `identify_example`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `identify_example` (
  `identify_id` int(11) NOT NULL AUTO_INCREMENT,
  `identify_email` varchar(255) DEFAULT NULL,
  `identify_key` varchar(255) DEFAULT NULL,
  `identify_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `identify_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `identify_time_started` datetime DEFAULT NULL,
  `identify_time_completed` datetime DEFAULT NULL,
  `identify_pbs_number` int(11) DEFAULT NULL,
  `identify_parent_id` int(11) DEFAULT NULL,
  `identify_copy_id` int(11) DEFAULT NULL,
  `identify_params` text,
  PRIMARY KEY (`identify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=720 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `quantify_example`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quantify_example` (
  `quantify_id` int(11) NOT NULL AUTO_INCREMENT,
  `quantify_identify_id` int(11) DEFAULT NULL,
  `quantify_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `quantify_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `quantify_time_started` datetime DEFAULT NULL,
  `quantify_time_completed` datetime DEFAULT NULL,
  `quantify_pbs_number` int(11) DEFAULT NULL,
  `quantify_parent_id` int(11) DEFAULT NULL,
  `quantify_params` text,
  PRIMARY KEY (`quantify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=697 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

