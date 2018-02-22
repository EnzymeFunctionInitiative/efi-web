
--
-- Table structure for table `job_group`
--

DROP TABLE IF EXISTS `job_group`;
CREATE TABLE `job_group` (
  `gnn_id` int(11) DEFAULT NULL,
  `diagram_id` int(11) DEFAULT NULL,
  `user_group` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Get rid of user_token since it is no longer used
--

DROP TABLE IF EXISTS `user_token`;

