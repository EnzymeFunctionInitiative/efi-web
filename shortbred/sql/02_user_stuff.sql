
--
-- Table structure for table `job_group`
--

DROP TABLE IF EXISTS `job_group`;
CREATE TABLE `job_group` (
  `identify_id` int(11) DEFAULT NULL,
  `user_group` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`identify_id`, `user_group`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

