
--
-- Table structure for table `PFAM_clans`
--

DROP TABLE IF EXISTS `PFAM_clans`;
CREATE TABLE `PFAM_clans` (
  `pfam_id` varchar(24) DEFAULT NULL,
  `clan_id` varchar(24) DEFAULT NULL,
  KEY `clan_id_Index` (`clan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `PFAM_clans_old`
--

DROP TABLE IF EXISTS `PFAM_clans_old`;
CREATE TABLE `PFAM_clans_old` (
  `pfam_id` varchar(24) DEFAULT NULL,
  `clan_id` varchar(24) DEFAULT NULL,
  KEY `clan_id_Index` (`clan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `analysis`
--

DROP TABLE IF EXISTS `analysis`;
CREATE TABLE `analysis` (
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
) ENGINE=MyISAM AUTO_INCREMENT=53831 DEFAULT CHARSET=latin1;

--
-- Table structure for table `analysis_example`
--

DROP TABLE IF EXISTS `analysis_example`;
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
) ENGINE=MyISAM AUTO_INCREMENT=35896 DEFAULT CHARSET=latin1;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
CREATE TABLE `db_version` (
  `db_version_id` int(11) NOT NULL AUTO_INCREMENT,
  `db_version_date` varchar(255) DEFAULT NULL,
  `db_version_interpro` varchar(255) DEFAULT NULL,
  `db_version_unipro` varchar(255) DEFAULT NULL,
  `db_version_default` tinyint(1) DEFAULT '0',
  `db_version_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`db_version_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `email_status`
--

DROP TABLE IF EXISTS `email_status`;
CREATE TABLE `email_status` (
  `email` varchar(255) NOT NULL,
  `opt_in` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `family_info`
--

DROP TABLE IF EXISTS `family_info`;
CREATE TABLE `family_info` (
  `family` varchar(10) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `long_name` varchar(255) DEFAULT NULL,
  `num_members` int(11) DEFAULT NULL,
  `num_uniref50_members` int(11) DEFAULT NULL,
  `num_uniref90_members` int(11) DEFAULT NULL,
  PRIMARY KEY (`family`),
  KEY `family_Index` (`family`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `generate`
--

DROP TABLE IF EXISTS `generate`;
CREATE TABLE `generate` (
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
) ENGINE=MyISAM AUTO_INCREMENT=43644 DEFAULT CHARSET=latin1;

--
-- Table structure for table `generate_example`
--

DROP TABLE IF EXISTS `generate_example`;
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
) ENGINE=MyISAM AUTO_INCREMENT=29550 DEFAULT CHARSET=latin1;

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
  `generate_id` int(11) DEFAULT NULL,
  `user_group` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

