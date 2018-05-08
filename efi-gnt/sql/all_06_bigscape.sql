
DROP TABLE IF EXISTS `bigscape`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bigscape` (
  `bigscape_id` int(11) NOT NULL AUTO_INCREMENT,
  `bigscape_diagram_id` int(11) DEFAULT NULL,
  `bigscape_job_type` varchar(10) DEFAULT NULL,
  `bigscape_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT NULL,
  `bigscape_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bigscape_time_started` datetime DEFAULT NULL,
  `bigscape_time_completed` datetime DEFAULT NULL,
  PRIMARY KEY (`bigscape_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

