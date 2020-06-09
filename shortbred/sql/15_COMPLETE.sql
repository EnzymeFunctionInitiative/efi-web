
--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `app_name` varchar(255) DEFAULT NULL,
  `app_email` varchar(255) DEFAULT NULL,
  `app_institution` varchar(255) DEFAULT NULL,
  `app_body` text,
  `app_status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `email`
--

DROP TABLE IF EXISTS `email`;
CREATE TABLE `email` (
  `email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `identify`
--

DROP TABLE IF EXISTS `identify`;
CREATE TABLE `identify` (
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
) ENGINE=InnoDB AUTO_INCREMENT=735 DEFAULT CHARSET=latin1;

--
-- Table structure for table `identify_example`
--

DROP TABLE IF EXISTS `identify_example`;
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
) ENGINE=InnoDB AUTO_INCREMENT=2207 DEFAULT CHARSET=latin1;

--
-- Table structure for table `quantify`
--

DROP TABLE IF EXISTS `quantify`;
CREATE TABLE `quantify` (
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
) ENGINE=InnoDB AUTO_INCREMENT=708 DEFAULT CHARSET=latin1;

--
-- Table structure for table `quantify_example`
--

DROP TABLE IF EXISTS `quantify_example`;
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
) ENGINE=InnoDB AUTO_INCREMENT=2173 DEFAULT CHARSET=latin1;

--
-- Table structure for table `cgfp_job_cancel`
--

DROP TABLE IF EXISTS `cgfp_job_cancel`;
CREATE TABLE `cgfp_job_cancel` (
  `job_process_num` int(11) NOT NULL DEFAULT 0,
  `cancel_status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`job_process_num`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `cgfp_job_group`
--

DROP TABLE IF EXISTS `cgfp_job_group`;
CREATE TABLE `cgfp_job_group` (
  `identify_id` int(11) NOT NULL DEFAULT 0,
  `user_group` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`identify_id`,`user_group`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

