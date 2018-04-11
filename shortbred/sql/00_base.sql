
--
-- Table structure for table `identify`
--

DROP TABLE IF EXISTS `identify`;
CREATE TABLE `identify` (
  `identify_id` int(11) NOT NULL AUTO_INCREMENT,
  `identify_email` varchar(255) DEFAULT NULL,
  `identify_key` varchar(255) DEFAULT NULL,
  `identify_filename` varchar(255) DEFAULT NULL,
  `identify_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT NULL,
  `identify_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `identify_time_started` datetime DEFAULT NULL,
  `identify_time_completed` datetime DEFAULT NULL,
  `identify_pbs_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`identify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


--
-- Table structure for table `quantify`
--

DROP TABLE IF EXISTS `quantify`;
CREATE TABLE `quantify` (
  `quantify_id` int(11) NOT NULL AUTO_INCREMENT,
  `quantify_identify_id` int(11),
  `quantify_metagenome_ids` text DEFAULT NULL,
  `quantify_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT NULL,
  `quantify_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `quantify_time_started` datetime DEFAULT NULL,
  `quantify_time_completed` datetime DEFAULT NULL,
  `quantify_pbs_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`quantify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

