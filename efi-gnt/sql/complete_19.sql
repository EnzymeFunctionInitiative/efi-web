
--
-- Table structure for table `bigscape`
--

DROP TABLE IF EXISTS `bigscape`;
CREATE TABLE `bigscape` (
  `bigscape_id` int(11) NOT NULL AUTO_INCREMENT,
  `bigscape_diagram_id` int(11) DEFAULT NULL,
  `bigscape_job_type` varchar(10) DEFAULT NULL,
  `bigscape_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT NULL,
  `bigscape_pbs_number` int(11) DEFAULT NULL,
  `bigscape_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bigscape_time_started` datetime DEFAULT NULL,
  `bigscape_time_completed` datetime DEFAULT NULL,
  PRIMARY KEY (`bigscape_id`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=latin1;

--
-- Table structure for table `diagram`
--

DROP TABLE IF EXISTS `diagram`;
CREATE TABLE `diagram` (
  `diagram_id` int(11) NOT NULL AUTO_INCREMENT,
  `diagram_key` varchar(255) DEFAULT NULL,
  `diagram_email` varchar(255) DEFAULT NULL,
  `diagram_status` enum('NEW','RUNNING','FINISH','FAILED') DEFAULT 'NEW',
  `diagram_pbs_number` int(11) DEFAULT NULL,
  `diagram_title` varchar(255) DEFAULT NULL,
  `diagram_time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diagram_time_started` datetime NOT NULL,
  `diagram_time_completed` datetime NOT NULL,
  `diagram_type` varchar(10) DEFAULT NULL,
  `diagram_params` text,
  `diagram_results` text,
  PRIMARY KEY (`diagram_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2257 DEFAULT CHARSET=latin1;

--
-- Table structure for table `gnn`
--

DROP TABLE IF EXISTS `gnn`;
CREATE TABLE `gnn` (
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
) ENGINE=MyISAM AUTO_INCREMENT=6517 DEFAULT CHARSET=latin1;

--
-- Table structure for table `gnn_example`
--

DROP TABLE IF EXISTS `gnn_example`;
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
) ENGINE=MyISAM AUTO_INCREMENT=10443 DEFAULT CHARSET=latin1;

--
-- Table structure for table `job_cancel`
--

DROP TABLE IF EXISTS `job_cancel`;
CREATE TABLE `job_cancel` (
  `job_process_num` int(11) NOT NULL DEFAULT '0',
  `cancel_status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`job_process_num`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `job_group`
--

DROP TABLE IF EXISTS `job_group`;
CREATE TABLE `job_group` (
  `gnn_id` int(11) DEFAULT NULL,
  `diagram_id` int(11) DEFAULT NULL,
  `user_group` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

