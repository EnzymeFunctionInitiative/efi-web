
--
-- Table structure for table `job_info`
--

DROP TABLE IF EXISTS `job_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_info` (
  `job_info_id` int(11) NOT NULL,
  `job_info_type` varchar(10) NOT NULL,
  `job_info_status` varchar(10) DEFAULT NULL,
  `job_info_job_id` int(11) DEFAULT NULL,
  `job_info_msg` text DEFAULT NULL,
  `job_info_time_created` datetime DEFAULT current_timestamp(),
  `job_info_time_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`job_info_id`,`job_info_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


