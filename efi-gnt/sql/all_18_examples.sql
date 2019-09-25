
DROP TABLE IF EXISTS `gnn_example`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gnn_example` (
  `gnn_id` int(11) NOT NULL AUTO_INCREMENT,
  `gnn_email` varchar(255) DEFAULT NULL,
  `gnn_key` varchar(255) DEFAULT NULL,
  `gnn_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gnn_time_started` datetime NOT NULL,
  `gnn_time_completed` datetime NOT NULL,
  `gnn_pbs_number` int(11) DEFAULT NULL,
  `gnn_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `gnn_est_source_id` int(11) DEFAULT NULL,
  `gnn_parent_id` int(11) DEFAULT NULL,
  `gnn_child_type` varchar(10) DEFAULT NULL,
  `gnn_params` text,
  `gnn_results` text,
  PRIMARY KEY (`gnn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

