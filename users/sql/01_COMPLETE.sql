
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
  `user_admin` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

