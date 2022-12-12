

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
  `analysis_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `analysis_time_started` datetime DEFAULT NULL,
  `analysis_time_completed` datetime DEFAULT NULL,
  `analysis_filter_sequences` int(11) DEFAULT NULL,
  `analysis_custom_cluster` int(11) DEFAULT NULL,
  `analysis_cdhit_opt` varchar(10) DEFAULT NULL,
  `analysis_params` text DEFAULT NULL,
  PRIMARY KEY (`analysis_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27083 DEFAULT CHARSET=latin1;

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
  `analysis_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `analysis_time_started` datetime DEFAULT NULL,
  `analysis_time_completed` datetime DEFAULT NULL,
  `analysis_filter_sequences` int(11) DEFAULT NULL,
  `analysis_custom_cluster` int(11) DEFAULT NULL,
  `analysis_cdhit_opt` varchar(10) DEFAULT NULL,
  `analysis_params` text DEFAULT NULL,
  PRIMARY KEY (`analysis_id`)
) ENGINE=MyISAM AUTO_INCREMENT=35896 DEFAULT CHARSET=latin1;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `app_name` varchar(255) DEFAULT NULL,
  `app_email` varchar(255) DEFAULT NULL,
  `app_institution` varchar(255) DEFAULT NULL,
  `app_body` text DEFAULT NULL,
  `app_status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `bigscape_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `bigscape_time_started` datetime DEFAULT NULL,
  `bigscape_time_completed` datetime DEFAULT NULL,
  PRIMARY KEY (`bigscape_id`)
) ENGINE=InnoDB AUTO_INCREMENT=310 DEFAULT CHARSET=latin1;

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
  `diagram_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `diagram_time_started` datetime DEFAULT NULL,
  `diagram_time_completed` datetime DEFAULT NULL,
  `diagram_type` varchar(10) DEFAULT NULL,
  `diagram_params` text DEFAULT NULL,
  `diagram_results` text DEFAULT NULL,
  PRIMARY KEY (`diagram_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2588 DEFAULT CHARSET=latin1;

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
  `generate_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `generate_time_started` datetime DEFAULT NULL,
  `generate_time_completed` datetime DEFAULT NULL,
  `generate_sequence_max` tinyint(1) DEFAULT 0,
  `generate_db_version` int(11) DEFAULT NULL,
  `generate_program` enum('BLAST','BLAST+','DIAMOND','DIAMONDSENSITIVE') DEFAULT NULL,
  `generate_results` text DEFAULT NULL,
  `generate_params` text DEFAULT NULL,
  `generate_parent_id` int(11) DEFAULT NULL,
  `generate_is_tax_job` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`generate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=26676 DEFAULT CHARSET=latin1;

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
  `generate_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `generate_time_started` datetime DEFAULT NULL,
  `generate_time_completed` datetime DEFAULT NULL,
  `generate_sequence_max` tinyint(1) DEFAULT 0,
  `generate_db_version` int(11) DEFAULT NULL,
  `generate_program` enum('BLAST','BLAST+','DIAMOND','DIAMONDSENSITIVE') DEFAULT NULL,
  `generate_results` text DEFAULT NULL,
  `generate_params` text DEFAULT NULL,
  `generate_parent_id` int(11) DEFAULT NULL,
  `generate_is_tax_job` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`generate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29550 DEFAULT CHARSET=latin1;

--
-- Table structure for table `gnn`
--

DROP TABLE IF EXISTS `gnn`;
CREATE TABLE `gnn` (
  `gnn_id` int(11) NOT NULL AUTO_INCREMENT,
  `gnn_email` varchar(255) DEFAULT NULL,
  `gnn_key` varchar(255) DEFAULT NULL,
  `gnn_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `gnn_time_started` datetime NOT NULL,
  `gnn_time_completed` datetime NOT NULL,
  `gnn_pbs_number` int(11) DEFAULT NULL,
  `gnn_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `gnn_est_source_id` int(11) DEFAULT NULL,
  `gnn_parent_id` int(11) DEFAULT NULL,
  `gnn_child_type` varchar(10) DEFAULT NULL,
  `gnn_params` text DEFAULT NULL,
  `gnn_results` text DEFAULT NULL,
  PRIMARY KEY (`gnn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7605 DEFAULT CHARSET=latin1;

--
-- Table structure for table `gnn_example`
--

DROP TABLE IF EXISTS `gnn_example`;
CREATE TABLE `gnn_example` (
  `gnn_id` int(11) NOT NULL AUTO_INCREMENT,
  `gnn_email` varchar(255) DEFAULT NULL,
  `gnn_key` varchar(255) DEFAULT NULL,
  `gnn_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `gnn_time_started` datetime NOT NULL,
  `gnn_time_completed` datetime NOT NULL,
  `gnn_pbs_number` int(11) DEFAULT NULL,
  `gnn_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `gnn_est_source_id` int(11) DEFAULT NULL,
  `gnn_parent_id` int(11) DEFAULT NULL,
  `gnn_child_type` varchar(10) DEFAULT NULL,
  `gnn_params` text DEFAULT NULL,
  `gnn_results` text DEFAULT NULL,
  PRIMARY KEY (`gnn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10443 DEFAULT CHARSET=latin1;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `group_name` varchar(100) NOT NULL,
  `group_status` varchar(10) DEFAULT NULL,
  `group_time_open` datetime DEFAULT NULL,
  `group_time_closed` datetime DEFAULT NULL,
  PRIMARY KEY (`group_name`)
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
  `identify_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `identify_time_started` datetime DEFAULT NULL,
  `identify_time_completed` datetime DEFAULT NULL,
  `identify_pbs_number` int(11) DEFAULT NULL,
  `identify_parent_id` int(11) DEFAULT NULL,
  `identify_copy_id` int(11) DEFAULT NULL,
  `identify_params` text DEFAULT NULL,
  PRIMARY KEY (`identify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=805 DEFAULT CHARSET=latin1;

--
-- Table structure for table `identify_example`
--

DROP TABLE IF EXISTS `identify_example`;
CREATE TABLE `identify_example` (
  `identify_id` int(11) NOT NULL AUTO_INCREMENT,
  `identify_email` varchar(255) DEFAULT NULL,
  `identify_key` varchar(255) DEFAULT NULL,
  `identify_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `identify_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `identify_time_started` datetime DEFAULT NULL,
  `identify_time_completed` datetime DEFAULT NULL,
  `identify_pbs_number` int(11) DEFAULT NULL,
  `identify_parent_id` int(11) DEFAULT NULL,
  `identify_copy_id` int(11) DEFAULT NULL,
  `identify_params` text DEFAULT NULL,
  PRIMARY KEY (`identify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2207 DEFAULT CHARSET=latin1;

--
-- Table structure for table `job_cancel`
--

DROP TABLE IF EXISTS `job_cancel`;
CREATE TABLE `job_cancel` (
  `job_type` varchar(4) NOT NULL DEFAULT '',
  `job_process_num` int(11) NOT NULL DEFAULT 0,
  `cancel_status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`job_type`,`job_process_num`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `job_group`
--

DROP TABLE IF EXISTS `job_group`;
CREATE TABLE `job_group` (
  `job_type` varchar(4) NOT NULL DEFAULT '',
  `job_id` int(11) NOT NULL DEFAULT 0,
  `other_id` int(11) DEFAULT NULL,
  `user_group` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`job_type`,`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `quantify`
--

DROP TABLE IF EXISTS `quantify`;
CREATE TABLE `quantify` (
  `quantify_id` int(11) NOT NULL AUTO_INCREMENT,
  `quantify_identify_id` int(11) DEFAULT NULL,
  `quantify_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `quantify_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantify_time_started` datetime DEFAULT NULL,
  `quantify_time_completed` datetime DEFAULT NULL,
  `quantify_pbs_number` int(11) DEFAULT NULL,
  `quantify_parent_id` int(11) DEFAULT NULL,
  `quantify_params` text DEFAULT NULL,
  PRIMARY KEY (`quantify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=738 DEFAULT CHARSET=latin1;

--
-- Table structure for table `quantify_example`
--

DROP TABLE IF EXISTS `quantify_example`;
CREATE TABLE `quantify_example` (
  `quantify_id` int(11) NOT NULL AUTO_INCREMENT,
  `quantify_identify_id` int(11) DEFAULT NULL,
  `quantify_status` enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED') DEFAULT NULL,
  `quantify_time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantify_time_started` datetime DEFAULT NULL,
  `quantify_time_completed` datetime DEFAULT NULL,
  `quantify_pbs_number` int(11) DEFAULT NULL,
  `quantify_parent_id` int(11) DEFAULT NULL,
  `quantify_params` text DEFAULT NULL,
  PRIMARY KEY (`quantify_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2173 DEFAULT CHARSET=latin1;

--
-- Table structure for table `taxonomy`
--

DROP TABLE IF EXISTS `taxonomy`;
CREATE TABLE `taxonomy` (
  `Taxonomy_ID` int(11) DEFAULT NULL,
  `Domain` varchar(25) DEFAULT NULL,
  `Kingdom` varchar(25) DEFAULT NULL,
  `Phylum` varchar(30) DEFAULT NULL,
  `Class` varchar(25) DEFAULT NULL,
  `TaxOrder` varchar(30) DEFAULT NULL,
  `Family` varchar(25) DEFAULT NULL,
  `Genus` varchar(40) DEFAULT NULL,
  `Species` varchar(50) DEFAULT NULL,
  KEY `TaxID_Index` (`Taxonomy_ID`),
  KEY `Domain_Index` (`Domain`),
  KEY `Kingdom_Index` (`Kingdom`),
  KEY `Phylum_Index` (`Phylum`),
  KEY `Class_Index` (`Class`),
  KEY `TaxOrder_Index` (`TaxOrder`),
  KEY `Family_Index` (`Family`),
  KEY `Genus_Index` (`Genus`),
  KEY `Species_Index` (`Species`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group` (
  `group_name` varchar(100) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  PRIMARY KEY (`group_name`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
CREATE TABLE `user_token` (
  `user_id` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `user_action` varchar(10) DEFAULT NULL,
  `user_group` varchar(100) DEFAULT NULL,
  `user_admin` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

