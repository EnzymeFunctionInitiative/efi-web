
DROP TABLE IF EXISTS `pfam_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pfam_info` (
  `pfam` varchar(10) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `long_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pfam`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

