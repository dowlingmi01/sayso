-- MySQL dump 10.13  Distrib 5.1.58, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: sayso
-- ------------------------------------------------------
-- Server version	5.1.58-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `sayso`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sayso` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `sayso`;

--
-- Table structure for table `admin_role`
--

DROP TABLE IF EXISTS `admin_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_role`
--

LOCK TABLES `admin_role` WRITE;
/*!40000 ALTER TABLE `admin_role` DISABLE KEYS */;
INSERT INTO `admin_role` VALUES (1,'superuser','Administrator with all privileges','2011-11-03 15:49:49',NULL);
/*!40000 ALTER TABLE `admin_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_user`
--

DROP TABLE IF EXISTS `admin_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `first_name` varchar(64) DEFAULT NULL,
  `last_name` varchar(64) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_user`
--

LOCK TABLES `admin_user` WRITE;
/*!40000 ALTER TABLE `admin_user` DISABLE KEYS */;
INSERT INTO `admin_user` VALUES (1,'admin@email.com','827ccb0eea8a706c4c34a16891f84e7b','Super','Admin','2011-11-03 15:49:49',NULL);
/*!40000 ALTER TABLE `admin_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_user_admin_role`
--

DROP TABLE IF EXISTS `admin_user_admin_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_user_admin_role` (
  `admin_user_id` int(10) unsigned NOT NULL,
  `admin_role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`admin_user_id`,`admin_role_id`),
  KEY `admin_role_id` (`admin_role_id`),
  CONSTRAINT `admin_user_admin_role_ibfk_1` FOREIGN KEY (`admin_role_id`) REFERENCES `admin_role` (`id`),
  CONSTRAINT `admin_user_admin_role_ibfk_2` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_user_admin_role`
--

LOCK TABLES `admin_user_admin_role` WRITE;
/*!40000 ALTER TABLE `admin_user_admin_role` DISABLE KEYS */;
INSERT INTO `admin_user_admin_role` VALUES (1,1);
/*!40000 ALTER TABLE `admin_user_admin_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_user`
--

DROP TABLE IF EXISTS `external_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `external_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT 'This may be null if the ''internal'' user has not been created yet',
  `uuid` varchar(255) DEFAULT NULL,
  `uuid_type` enum('integer','email','username','hash') DEFAULT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `install_token` varchar(64) DEFAULT NULL,
  `install_ip_address` varchar(255) DEFAULT NULL,
  `install_user_agent` varchar(255) DEFAULT NULL,
  `install_begin_time` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `first_name` varchar(64) DEFAULT NULL,
  `last_name` varchar(64) DEFAULT NULL,
  `domain` varchar(64) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_user_unique` (`uuid`,`starbar_id`),
  KEY `external_user_user_id` (`user_id`),
  KEY `external_user_starbar_id` (`starbar_id`),
  KEY `install_token` (`install_token`),
  CONSTRAINT `external_user_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `external_user_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_user`
--

LOCK TABLES `external_user` WRITE;
/*!40000 ALTER TABLE `external_user` DISABLE KEYS */;
INSERT INTO `external_user` VALUES (1,2,'rationalplanet@gmail.com','email',1,'08XK0TIRZYJPSIFHJ6BKQZ1H53JJ5I3VEWQME4RAPSHMRHI6BJCYVFUGS9SKK3X3','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.106 Safari/535.2','2011-10-28 14:12:19',NULL,NULL,NULL,NULL,NULL,'2011-10-28 11:11:41','2011-11-08 10:04:34');
/*!40000 ALTER TABLE `external_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_user_install`
--

DROP TABLE IF EXISTS `external_user_install`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `external_user_install` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `external_user_id` int(10) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `begin_time` datetime NOT NULL,
  `completed_time` datetime NOT NULL COMMENT 'This is not exact',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_user_unique` (`ip_address`,`user_agent`,`begin_time`),
  KEY `external_user_install_external_user_id` (`external_user_id`),
  CONSTRAINT `external_user_install_external_user_id` FOREIGN KEY (`external_user_id`) REFERENCES `external_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_user_install`
--

LOCK TABLES `external_user_install` WRITE;
/*!40000 ALTER TABLE `external_user_install` DISABLE KEYS */;
/*!40000 ALTER TABLE `external_user_install` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_age_range`
--

DROP TABLE IF EXISTS `lookup_age_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_age_range` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `age_from` int(10) DEFAULT NULL,
  `age_to` int(10) DEFAULT NULL,
  `ordinal` int(10) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_age_range`
--

LOCK TABLES `lookup_age_range` WRITE;
/*!40000 ALTER TABLE `lookup_age_range` DISABLE KEYS */;
INSERT INTO `lookup_age_range` VALUES (1,13,17,10,'0000-00-00 00:00:00'),(2,18,24,20,'0000-00-00 00:00:00'),(3,25,34,30,'0000-00-00 00:00:00'),(4,35,44,40,'0000-00-00 00:00:00'),(5,45,54,50,'0000-00-00 00:00:00'),(6,55,64,60,'0000-00-00 00:00:00'),(7,65,NULL,70,'0000-00-00 00:00:00'),(8,18,NULL,80,'0000-00-00 00:00:00'),(9,18,49,90,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_age_range` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_email_frequency`
--

DROP TABLE IF EXISTS `lookup_email_frequency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_email_frequency` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `extra` varchar(255) DEFAULT NULL,
  `default_frequency` tinyint(1) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_email_frequency`
--

LOCK TABLES `lookup_email_frequency` WRITE;
/*!40000 ALTER TABLE `lookup_email_frequency` DISABLE KEYS */;
INSERT INTO `lookup_email_frequency` VALUES (1,'often','Bring \'em on!','Often - earn the most Pay.So!','Earn a lotta Pay.So! :D',0,'0000-00-00 00:00:00'),(2,'occasionally','Occasionally','Occasionally - earn a little Pay.So','Earn a little Pay.So. :|',1,'0000-00-00 00:00:00'),(3,'never','Never','Never - no Pay.So :(','Earn no Pay.So. :(',0,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_email_frequency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_ethnicity`
--

DROP TABLE IF EXISTS `lookup_ethnicity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_ethnicity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(32) NOT NULL,
  `label` varchar(32) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_ethnicity`
--

LOCK TABLES `lookup_ethnicity` WRITE;
/*!40000 ALTER TABLE `lookup_ethnicity` DISABLE KEYS */;
INSERT INTO `lookup_ethnicity` VALUES (1,'white','White',NULL,'0000-00-00 00:00:00'),(2,'african_american','African American',NULL,'0000-00-00 00:00:00'),(3,'asian','Asian',NULL,'0000-00-00 00:00:00'),(4,'latino','Latino',NULL,'0000-00-00 00:00:00'),(5,'native_american','Native American',NULL,'0000-00-00 00:00:00'),(6,'hawaiin-pacific-islander','Hawaiin Pacific Islander',NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_ethnicity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_gender`
--

DROP TABLE IF EXISTS `lookup_gender`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_gender` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(32) NOT NULL,
  `label` varchar(32) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_gender`
--

LOCK TABLES `lookup_gender` WRITE;
/*!40000 ALTER TABLE `lookup_gender` DISABLE KEYS */;
INSERT INTO `lookup_gender` VALUES (1,'male',NULL,NULL,'0000-00-00 00:00:00'),(2,'female',NULL,NULL,'0000-00-00 00:00:00'),(3,'unspecified',NULL,NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_gender` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_income_range`
--

DROP TABLE IF EXISTS `lookup_income_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_income_range` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `income_from` int(10) DEFAULT NULL,
  `income_to` int(10) DEFAULT NULL,
  `ordinal` int(10) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_income_range`
--

LOCK TABLES `lookup_income_range` WRITE;
/*!40000 ALTER TABLE `lookup_income_range` DISABLE KEYS */;
INSERT INTO `lookup_income_range` VALUES (1,0,20000,10,'0000-00-00 00:00:00'),(2,20000,40000,20,'0000-00-00 00:00:00'),(3,40000,60000,30,'0000-00-00 00:00:00'),(4,60000,80000,40,'0000-00-00 00:00:00'),(5,80000,100000,50,'0000-00-00 00:00:00'),(6,100000,NULL,60,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_income_range` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_mime_type`
--

DROP TABLE IF EXISTS `lookup_mime_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_mime_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL COMMENT 'See http://en.wikipedia.org/wiki/Internet_media_type',
  `base_type` enum('application','audio','image','text','video','x') DEFAULT 'image',
  `common_ad_type` tinyint(1) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_mime_type`
--

LOCK TABLES `lookup_mime_type` WRITE;
/*!40000 ALTER TABLE `lookup_mime_type` DISABLE KEYS */;
INSERT INTO `lookup_mime_type` VALUES (1,'gif','GIF (image)','image/gif','image',1,'0000-00-00 00:00:00'),(2,'jpg','JPG (image)','image/jpeg','image',1,'0000-00-00 00:00:00'),(3,'png','PNG (image)','image/png','image',1,'0000-00-00 00:00:00'),(4,'tiff','TIFF (image)','image/tiff','image',0,'0000-00-00 00:00:00'),(5,'svg','SVG','image/svg+xml','image',0,'0000-00-00 00:00:00'),(6,'javascript','Javascript','application/javascript','application',0,'0000-00-00 00:00:00'),(7,'pdf','PDF','application/pdf','application',0,'0000-00-00 00:00:00'),(8,'html','HTML','text/html','text',1,'0000-00-00 00:00:00'),(9,'text','Text','text/plain','text',0,'0000-00-00 00:00:00'),(10,'mpeg','MPEG (video)','video/mpeg','video',0,'0000-00-00 00:00:00'),(11,'quicktime','Quicktime (video)','video/quicktime','video',0,'0000-00-00 00:00:00'),(12,'flash','Flash (video/other)','application/x-shockwave-flash','x',1,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_mime_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_poll_frequency`
--

DROP TABLE IF EXISTS `lookup_poll_frequency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_poll_frequency` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `extra` varchar(255) DEFAULT NULL,
  `default_frequency` tinyint(1) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_poll_frequency`
--

LOCK TABLES `lookup_poll_frequency` WRITE;
/*!40000 ALTER TABLE `lookup_poll_frequency` DISABLE KEYS */;
INSERT INTO `lookup_poll_frequency` VALUES (1,'often','Bring \'em on!','Often - earn the most Pay.So!','Earn a lotta Pay.So! :D',0,'0000-00-00 00:00:00'),(2,'occasionally','Occasionally','Occasionally - earn a little Pay.So','Earn a little Pay.So. :|',1,'0000-00-00 00:00:00'),(3,'never','Never','Never - no Pay.So :(','Earn no Pay.So. :(',0,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_poll_frequency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_quota_percentile`
--

DROP TABLE IF EXISTS `lookup_quota_percentile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_quota_percentile` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `quota` int(10) DEFAULT NULL,
  `quarter` tinyint(1) DEFAULT NULL COMMENT 'true for 25,50,75,100',
  `ordinal` int(10) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_quota_percentile`
--

LOCK TABLES `lookup_quota_percentile` WRITE;
/*!40000 ALTER TABLE `lookup_quota_percentile` DISABLE KEYS */;
INSERT INTO `lookup_quota_percentile` VALUES (1,10,0,10,'0000-00-00 00:00:00'),(2,20,0,20,'0000-00-00 00:00:00'),(3,25,1,30,'0000-00-00 00:00:00'),(4,30,0,40,'0000-00-00 00:00:00'),(5,40,0,50,'0000-00-00 00:00:00'),(6,50,1,60,'0000-00-00 00:00:00'),(7,60,0,70,'0000-00-00 00:00:00'),(8,70,0,80,'0000-00-00 00:00:00'),(9,75,1,90,'0000-00-00 00:00:00'),(10,80,0,100,'0000-00-00 00:00:00'),(11,90,0,110,'0000-00-00 00:00:00'),(12,100,1,120,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_quota_percentile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_search_engines`
--

DROP TABLE IF EXISTS `lookup_search_engines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_search_engines` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_search_engines`
--

LOCK TABLES `lookup_search_engines` WRITE;
/*!40000 ALTER TABLE `lookup_search_engines` DISABLE KEYS */;
INSERT INTO `lookup_search_engines` VALUES (1,'bing','Bing',NULL,'0000-00-00 00:00:00'),(2,'google','Google',NULL,'0000-00-00 00:00:00'),(3,'yahoo','Yahoo!',NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_search_engines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_social_activity_type`
--

DROP TABLE IF EXISTS `lookup_social_activity_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_social_activity_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_social_activity_type`
--

LOCK TABLES `lookup_social_activity_type` WRITE;
/*!40000 ALTER TABLE `lookup_social_activity_type` DISABLE KEYS */;
INSERT INTO `lookup_social_activity_type` VALUES (1,'facebook_like',NULL,NULL,'0000-00-00 00:00:00'),(2,'tweet',NULL,NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_social_activity_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_survey_type`
--

DROP TABLE IF EXISTS `lookup_survey_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_survey_type` (
  `id` int(10) NOT NULL,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_survey_type`
--

LOCK TABLES `lookup_survey_type` WRITE;
/*!40000 ALTER TABLE `lookup_survey_type` DISABLE KEYS */;
INSERT INTO `lookup_survey_type` VALUES (1,'technology',NULL,NULL,'0000-00-00 00:00:00'),(2,'food',NULL,NULL,'0000-00-00 00:00:00'),(3,'religion',NULL,NULL,'0000-00-00 00:00:00'),(4,'news',NULL,NULL,'0000-00-00 00:00:00'),(5,'celebrities',NULL,NULL,'0000-00-00 00:00:00'),(6,'politics',NULL,NULL,'0000-00-00 00:00:00'),(7,'sports',NULL,NULL,'0000-00-00 00:00:00'),(8,'household',NULL,NULL,'0000-00-00 00:00:00'),(9,'television',NULL,NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_survey_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lookup_timeframe`
--

DROP TABLE IF EXISTS `lookup_timeframe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lookup_timeframe` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `seconds` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lookup_timeframe`
--

LOCK TABLES `lookup_timeframe` WRITE;
/*!40000 ALTER TABLE `lookup_timeframe` DISABLE KEYS */;
INSERT INTO `lookup_timeframe` VALUES (1,'one_hour','1 Hour','3600',NULL,'0000-00-00 00:00:00'),(2,'one_day','1 Day','86400',NULL,'0000-00-00 00:00:00'),(3,'one_week','1 Week','604800',NULL,'0000-00-00 00:00:00'),(4,'one_month','1 Month','2592000',NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `lookup_timeframe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metrics_page_view`
--

DROP TABLE IF EXISTS `metrics_page_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metrics_page_view` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `url` text,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `metrics_page_view_user_id` (`user_id`),
  KEY `metrics_page_view_starbar_id` (`starbar_id`),
  CONSTRAINT `metrics_page_view_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `metrics_page_view_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metrics_page_view`
--

LOCK TABLES `metrics_page_view` WRITE;
/*!40000 ALTER TABLE `metrics_page_view` DISABLE KEYS */;
INSERT INTO `metrics_page_view` VALUES (1,2,1,'http://saysoccer.com/','2011-10-31 10:08:45','0000-00-00 00:00:00'),(2,2,1,'http://saysoccer.com/','2011-10-31 10:15:15','0000-00-00 00:00:00'),(3,2,1,'http://saysoccer.com/','2011-10-31 10:17:09','0000-00-00 00:00:00'),(4,2,1,'http://saysoccer.com/','2011-10-31 10:23:54','0000-00-00 00:00:00'),(5,2,1,'http://saysoccer.com/','2011-10-31 10:49:30','0000-00-00 00:00:00'),(6,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=websocket','2011-10-31 10:56:17','0000-00-00 00:00:00'),(7,2,1,'http://en.wikipedia.org/wiki/WebSocket','2011-10-31 10:56:25','0000-00-00 00:00:00'),(8,2,1,'http://www.facebook.com/','2011-10-31 11:23:14','0000-00-00 00:00:00'),(9,2,1,'http://www.facebook.com/','2011-10-31 11:23:21','0000-00-00 00:00:00'),(10,2,1,'http://www.google.com.ua/search?sourceid=chrome&ie=UTF-8&q=test','2011-10-31 11:53:16','0000-00-00 00:00:00'),(11,2,1,'http://app-dev.saysollc.com/starbar/hellomusic/rewards/user_key/o959sm770n66pefuvru44i7ev7/user_id/482/auth_key/309e34632c2ca9cd5edaf2388f5fa3db/test/sdf','2011-10-31 12:23:39','0000-00-00 00:00:00'),(12,1,1,'htpp://duduz.com','2011-11-02 14:35:03','0000-00-00 00:00:00'),(13,1,1,'htpp://duduz.com','2011-11-02 14:36:48','0000-00-00 00:00:00'),(14,2,1,'http://portal.ebay.de/weihnachtsgeschenke/','2011-11-07 09:23:23','0000-00-00 00:00:00'),(15,2,1,'http://www.google.com.ua/#sclient=psy-ab&hl=uk&site=&source=hp&q=test&pbx=1&oq=test&aq=f&aqi=g4&aql=1&gs_sm=e&gs_upl=1970l2103l0l2894l4l2l0l0l0l0l128l242l0.2l2l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1418&bih=790','2011-11-07 09:29:03','0000-00-00 00:00:00'),(16,2,1,'http://www.google.com.ua/#sclient=psy-ab&hl=uk&site=&source=hp&q=test&pbx=1&oq=test&aq=f&aqi=g4&aql=1&gs_sm=e&gs_upl=1970l2103l0l2894l4l2l0l0l0l0l128l242l0.2l2l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1418&bih=790','2011-11-07 09:31:37','0000-00-00 00:00:00'),(17,2,1,'http://www.google.de/','2011-11-07 10:03:37','0000-00-00 00:00:00'),(18,2,1,'http://search.yahoo.com/search;_ylt=A03uoSceu7dOeXkAZmebvZx4?p=test&toggle=1&cop=mss&ei=UTF-8&fr=yfp-t-701','2011-11-07 10:06:18','0000-00-00 00:00:00'),(19,2,1,'http://search.yahoo.com/search;_ylt=A03uoSceu7dOeXkAZmebvZx4?p=test&toggle=1&cop=mss&ei=UTF-8&fr=yfp-t-701','2011-11-07 10:07:08','0000-00-00 00:00:00'),(20,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 10:07:20','0000-00-00 00:00:00'),(21,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 10:07:55','0000-00-00 00:00:00'),(22,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 10:08:00','0000-00-00 00:00:00'),(23,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 10:08:03','0000-00-00 00:00:00'),(24,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 10:20:23','0000-00-00 00:00:00'),(25,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 10:21:58','0000-00-00 00:00:00'),(26,2,1,'http://www.google.com.ua/search?aq=f&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 10:22:00','0000-00-00 00:00:00'),(27,2,1,'http://client.local.sayso.com/hellomusic/home?sayso-install=true','2011-11-07 11:46:06','0000-00-00 00:00:00'),(28,2,1,'http://www.1112.net/lastpage.html','2011-11-07 11:46:15','0000-00-00 00:00:00'),(29,2,1,'http://www.1112.net/lastpage.html','2011-11-07 11:46:52','0000-00-00 00:00:00'),(30,2,1,'http://www.1112.net/lastpage.html','2011-11-07 11:48:59','0000-00-00 00:00:00'),(31,2,1,'http://www.1112.net/lastpage.html','2011-11-07 11:49:20','0000-00-00 00:00:00'),(32,2,1,'http://www.google.de/','2011-11-07 11:49:35','0000-00-00 00:00:00'),(33,2,1,'http://www.google.de/','2011-11-07 11:53:06','0000-00-00 00:00:00'),(34,2,1,'http://www.google.de/','2011-11-07 11:57:12','0000-00-00 00:00:00'),(35,2,1,'http://www.google.de/','2011-11-07 11:57:28','0000-00-00 00:00:00'),(36,2,1,'http://www.google.de/#sclient=psy-ab&hl=de&site=&source=hp&q=test&pbx=1&oq=test&aq=f&aqi=g4&aql=1&gs_sm=e&gs_upl=496991l497217l0l497546l4l3l0l0l0l0l304l791l2-2.1l3l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=1dc4c32e9c7dabe9&biw=1280&bih=738','2011-11-07 12:06:33','0000-00-00 00:00:00'),(37,2,1,'http://www.google.de/#sclient=psy-ab&hl=de&source=hp&q=test%20happens&pbx=1&oq=&aq=&aqi=&aql=&gs_sm=&gs_upl=&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=1dc4c32e9c7dabe9&biw=1280&bih=831&pf=p&pdl=500','2011-11-07 12:11:38','0000-00-00 00:00:00'),(38,2,1,'http://www.google.de/#sclient=psy-ab&hl=de&source=hp&q=test%20happens&pbx=1&oq=&aq=&aqi=&aql=&gs_sm=&gs_upl=&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=1dc4c32e9c7dabe9&biw=1280&bih=831&pf=p&pdl=500','2011-11-07 12:11:51','0000-00-00 00:00:00'),(39,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 12:13:36','0000-00-00 00:00:00'),(40,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 12:13:54','0000-00-00 00:00:00'),(41,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 12:16:01','0000-00-00 00:00:00'),(42,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&kak','2011-11-07 12:16:14','0000-00-00 00:00:00'),(43,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&kak','2011-11-07 12:16:21','0000-00-00 00:00:00'),(44,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&kak','2011-11-07 12:16:59','0000-00-00 00:00:00'),(45,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test','2011-11-07 12:17:21','0000-00-00 00:00:00'),(46,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&dudz','2011-11-07 12:17:31','0000-00-00 00:00:00'),(47,2,1,'http://www.google.de/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&dudz','2011-11-07 12:17:52','0000-00-00 00:00:00'),(48,2,1,'http://www.google.de/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&dudz','2011-11-07 12:21:16','0000-00-00 00:00:00'),(49,2,1,'http://www.google.de/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&dudz','2011-11-07 12:24:31','0000-00-00 00:00:00'),(50,2,1,'http://www.google.de/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=test&dudz#sclient=psy-ab&hl=de&source=hp&q=teste&pbx=1&oq=teste&aq=f&aqi=g4&aql=1&gs_sm=p&gs_upl=0l0l5l5997l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=1dc4c32e9c7dabe9&biw=1280&bih=552','2011-11-07 12:27:45','0000-00-00 00:00:00'),(51,2,1,'http://www.bing.com/','2011-11-07 13:01:12','0000-00-00 00:00:00'),(52,2,1,'http://www.bing.com/search?q=test&go=&qs=n&sk=&sc=8-4&form=QBLH','2011-11-07 13:01:29','0000-00-00 00:00:00'),(53,2,1,'http://www.bing.com/search?q=test&go=&qs=n&sk=&sc=8-4&form=QBLH','2011-11-07 13:01:53','0000-00-00 00:00:00'),(54,2,1,'http://www.bing.com/search?q=testing+bing&go=&qs=n&sk=&sc=8-8&form=QBRE','2011-11-07 13:02:03','0000-00-00 00:00:00'),(55,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=yahoo','2011-11-07 13:02:30','0000-00-00 00:00:00'),(56,2,1,'http://www.yahoo.com/','2011-11-07 13:02:37','0000-00-00 00:00:00'),(57,2,1,'http://search.yahoo.com/search;_ylt=A03uoR505LdOUdcApy2bvZx4?p=test&toggle=1&cop=mss&ei=UTF-8&fr=yfp-t-701','2011-11-07 13:02:50','0000-00-00 00:00:00'),(58,2,1,'http://search.yahoo.com/search;_ylt=A03uoR505LdOUdcApy2bvZx4?p=test&toggle=1&cop=mss&ei=UTF-8&fr=yfp-t-701','2011-11-07 13:03:29','0000-00-00 00:00:00'),(59,2,1,'http://search.yahoo.com/search;_ylt=A0oG7hf05LdOVVwA_ppXNyoA;_ylc=X1MDMjc2NjY3OQRfcgMyBGFvA2FvBGNzcmNwdmlkA0QzT3VkMG9HN3Y0S0pUcGRUcmZrZEF6NVVzOXkzRTYzNUtnQUI1LjAEZnIDeWZwLXQtNzAxBGZyMgNzYnRuBG5fZ3BzAzEwBG9yaWdpbgNzcnAEcHFzdHIDdGVzdCBhZ2FpbgRxdWVyeQN0ZXN0IGFnYWluBHNhbwMxBHZ0ZXN0aWQDTVNZMDE1?p=test%20again&fr2=sb-top&fr=yfp-t-701&pqstr=test%20again','2011-11-07 13:04:46','0000-00-00 00:00:00'),(60,2,1,'http://search.yahoo.com/search;_ylt=A0oG7hf05LdOVVwA_ppXNyoA;_ylc=X1MDMjc2NjY3OQRfcgMyBGFvA2FvBGNzcmNwdmlkA0QzT3VkMG9HN3Y0S0pUcGRUcmZrZEF6NVVzOXkzRTYzNUtnQUI1LjAEZnIDeWZwLXQtNzAxBGZyMgNzYnRuBG5fZ3BzAzEwBG9yaWdpbgNzcnAEcHFzdHIDdGVzdCBhZ2FpbgRxdWVyeQN0ZXN0IGFnYWluBHNhbwMxBHZ0ZXN0aWQDTVNZMDE1?p=test%20again&fr2=sb-top&fr=yfp-t-701&pqstr=test%20again','2011-11-07 13:05:55','0000-00-00 00:00:00'),(61,2,1,'http://search.yahoo.com/search;_ylt=A0oG7hf05LdOVVwA_ppXNyoA;_ylc=X1MDMjc2NjY3OQRfcgMyBGFvA2FvBGNzcmNwdmlkA0QzT3VkMG9HN3Y0S0pUcGRUcmZrZEF6NVVzOXkzRTYzNUtnQUI1LjAEZnIDeWZwLXQtNzAxBGZyMgNzYnRuBG5fZ3BzAzEwBG9yaWdpbgNzcnAEcHFzdHIDdGVzdCBhZ2FpbgRxdWVyeQN0ZXN0IGFnYWluBHNhbwMxBHZ0ZXN0aWQDTVNZMDE1?p=test%20again&fr2=sb-top&fr=yfp-t-701&pqstr=test%20again','2011-11-07 13:06:49','0000-00-00 00:00:00'),(62,2,1,'http://search.yahoo.com/search;_ylt=A0oG7hd75bdODhMAq5NXNyoA?p=%26test%20again&fr2=sb-top&fr=yfp-t-701','2011-11-07 13:07:00','0000-00-00 00:00:00'),(63,2,1,'http://www.yahoo.com/','2011-11-07 13:07:14','0000-00-00 00:00:00'),(64,2,1,'http://search.yahoo.com/search;_ylt=AgQYjTrs5b95Rnq2KshwkuebvZx4?p=test&toggle=1&cop=mss&ei=UTF-8&fr=yfp-t-471','2011-11-07 13:07:20','0000-00-00 00:00:00'),(65,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+again+with+function','2011-11-07 13:22:33','0000-00-00 00:00:00'),(66,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+again+with+function','2011-11-07 13:24:56','0000-00-00 00:00:00'),(67,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+again+with+function#sclient=psy-ab&hl=uk&source=hp&q=testing+yahoo&pbx=1&oq=testing+yahoo&aq=f&aqi=g-vL4&aql=1&gs_sm=e&gs_upl=9461l11264l0l11516l8l5l0l2l2l1l242l1109l0.1.4l6l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=588','2011-11-07 13:25:14','0000-00-00 00:00:00'),(68,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+yahoo','2011-11-07 13:25:48','0000-00-00 00:00:00'),(69,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+yahoo','2011-11-07 13:25:52','0000-00-00 00:00:00'),(70,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+yahoo','2011-11-07 13:26:20','0000-00-00 00:00:00'),(71,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+yahoo#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=1&gs_sm=e&gs_upl=13268l13831l1l14084l5l4l0l0l0l0l252l820l0.2.2l4l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=588','2011-11-07 13:26:48','0000-00-00 00:00:00'),(72,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz','2011-11-07 13:27:30','0000-00-00 00:00:00'),(73,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:28:27','0000-00-00 00:00:00'),(74,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:28:58','0000-00-00 00:00:00'),(75,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:29:34','0000-00-00 00:00:00'),(76,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:30:06','0000-00-00 00:00:00'),(77,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:30:13','0000-00-00 00:00:00'),(78,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:32:42','0000-00-00 00:00:00'),(79,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:32:45','0000-00-00 00:00:00'),(80,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:32:48','0000-00-00 00:00:00'),(81,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:33:30','0000-00-00 00:00:00'),(82,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:33:39','0000-00-00 00:00:00'),(83,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 13:33:50','0000-00-00 00:00:00'),(84,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:04:08','0000-00-00 00:00:00'),(85,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:06:28','0000-00-00 00:00:00'),(86,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:06:50','0000-00-00 00:00:00'),(87,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:07:07','0000-00-00 00:00:00'),(88,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:07:43','0000-00-00 00:00:00'),(89,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#sclient=psy-ab&hl=uk&source=hp&q=testing+duduz&pbx=1&oq=testing+duduz&aq=f&aqi=&aql=&gs_sm=e&gs_upl=0l0l0l41998l0l0l0l0l0l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:19:50','0000-00-00 00:00:00'),(90,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=testing+duduz#pq=testing+duduz&hl=uk&sugexp=kjrmc&cp=5&gs_id=t&xhr=t&q=lalla+ward&pf=p&sclient=psy-ab&source=hp&pbx=1&oq=lalla&aq=0&aqi=g1&aql=f&gs_sm=&gs_upl=&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=b6d6165f74ab06b7&biw=1280&bih=867&bs=1','2011-11-07 14:20:39','0000-00-00 00:00:00'),(91,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=polling+events','2011-11-07 14:29:46','0000-00-00 00:00:00'),(92,2,1,'http://www.google.com.ua/intl/uk/options/','2011-11-07 14:39:51','0000-00-00 00:00:00'),(93,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=polling+events#pq=polling+events&hl=uk&sugexp=ppwl&cp=46&gs_id=44&xhr=t&q=polling+events+in+input+fields+with+javascript&pf=p&sclient=psy-ab&source=hp&pbx=1&oq=polling+events+in+input+fields+with+javascript&aq=f&aqi=&aql=&gs_sm=&gs_upl=&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:39:53','0000-00-00 00:00:00'),(94,2,1,'http://www.google.com.ua/','2011-11-07 14:41:48','0000-00-00 00:00:00'),(95,2,1,'http://www.google.com.ua/#hl=uk&sugexp=kjrmc&cp=15&gs_id=17&xhr=t&q=lalaloopsy+test&pq=lalaloopsy&pf=p&sclient=psy-ab&source=hp&pbx=1&oq=lalaloopsy+test&aq=f&aqi=&aql=&gs_sm=&gs_upl=&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=52cf90c6499c20ee&biw=1280&bih=867','2011-11-07 14:42:59','0000-00-00 00:00:00'),(96,2,1,'http://www.google.com.ua/preferences?hl=uk','2011-11-07 15:04:21','0000-00-00 00:00:00'),(97,2,1,'http://www.google.com.ua/','2011-11-07 15:04:43','0000-00-00 00:00:00'),(98,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=chrome+view+cookies','2011-11-07 15:05:20','0000-00-00 00:00:00'),(99,2,1,'http://www.google.com/support/forum/p/Chrome/thread?tid=5f057d37c24bff6f&hl=en','2011-11-07 15:06:27','0000-00-00 00:00:00'),(100,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:06:43','0000-00-00 00:00:00'),(101,2,1,'http://www.google.com.ua/preferences?hl=uk','2011-11-07 15:06:49','0000-00-00 00:00:00'),(102,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:07:06','0000-00-00 00:00:00'),(103,2,1,'http://www.google.com.ua/preferences?hl=uk','2011-11-07 15:07:27','0000-00-00 00:00:00'),(104,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:07:41','0000-00-00 00:00:00'),(105,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:08:48','0000-00-00 00:00:00'),(106,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:13:07','0000-00-00 00:00:00'),(107,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:13:31','0000-00-00 00:00:00'),(108,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:13:48','0000-00-00 00:00:00'),(109,2,1,'http://www.google.com.ua/preferences?hl=uk','2011-11-07 15:14:12','0000-00-00 00:00:00'),(110,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:14:21','0000-00-00 00:00:00'),(111,2,1,'http://www.google.com.ua/preferences?hl=uk','2011-11-07 15:15:02','0000-00-00 00:00:00'),(112,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:15:08','0000-00-00 00:00:00'),(113,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:17:10','0000-00-00 00:00:00'),(114,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:17:18','0000-00-00 00:00:00'),(115,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:17:24','0000-00-00 00:00:00'),(116,2,1,'http://www.google.com.ua/search?gcx=c&sourceid=chrome&ie=UTF-8&q=cookies','2011-11-07 15:17:38','0000-00-00 00:00:00'),(117,2,1,'http://www.google.com.ua/preferences?hl=uk','2011-11-07 15:17:57','0000-00-00 00:00:00'),(118,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 15:40:21','0000-00-00 00:00:00'),(119,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 15:47:44','0000-00-00 00:00:00'),(120,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 15:48:44','0000-00-00 00:00:00'),(121,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 15:49:00','0000-00-00 00:00:00'),(122,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 15:49:35','0000-00-00 00:00:00'),(123,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 15:49:42','0000-00-00 00:00:00'),(124,2,1,'http://client.local.sayso.com/hellomusic/home?sayso-install=true','2011-11-07 17:26:52','0000-00-00 00:00:00'),(125,2,1,'http://client.local.sayso.com/hellomusic/home?sayso-install=true','2011-11-07 17:27:35','0000-00-00 00:00:00'),(126,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:28:40','0000-00-00 00:00:00'),(127,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:32:18','0000-00-00 00:00:00'),(128,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:35:46','0000-00-00 00:00:00'),(129,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:36:16','0000-00-00 00:00:00'),(130,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:38:07','0000-00-00 00:00:00'),(131,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:41:00','0000-00-00 00:00:00'),(132,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:48:32','0000-00-00 00:00:00'),(133,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:54:01','0000-00-00 00:00:00'),(134,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:54:44','0000-00-00 00:00:00'),(135,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-07 17:55:07','0000-00-00 00:00:00'),(136,2,1,'http://client.local.sayso.com/hellomusic/home?sayso-install=true','2011-11-08 10:04:38','0000-00-00 00:00:00'),(137,2,1,'http://www.google.com.ua/','2011-11-08 10:04:57','0000-00-00 00:00:00'),(138,2,1,'http://www.google.com.ua/','2011-11-08 10:07:35','0000-00-00 00:00:00'),(139,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-08 10:11:25','0000-00-00 00:00:00'),(140,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-08 10:12:14','0000-00-00 00:00:00'),(141,2,1,'http://twitter.com/','2011-11-08 10:36:46','0000-00-00 00:00:00'),(142,2,1,'https://twitter.com/','2011-11-08 10:50:58','0000-00-00 00:00:00'),(143,2,1,'https://twitter.com/','2011-11-08 10:52:55','0000-00-00 00:00:00'),(144,2,1,'https://twitter.com/','2011-11-08 10:56:51','0000-00-00 00:00:00'),(145,2,1,'https://twitter.com/','2011-11-08 10:59:42','0000-00-00 00:00:00'),(146,2,1,'https://twitter.com/','2011-11-08 11:04:26','0000-00-00 00:00:00'),(147,2,1,'https://twitter.com/','2011-11-08 11:10:11','0000-00-00 00:00:00'),(148,2,1,'https://twitter.com/','2011-11-08 11:29:29','0000-00-00 00:00:00'),(149,2,1,'https://twitter.com/','2011-11-08 12:32:28','0000-00-00 00:00:00'),(150,2,1,'https://twitter.com/','2011-11-08 12:32:57','0000-00-00 00:00:00'),(151,2,1,'https://twitter.com/','2011-11-08 12:34:57','0000-00-00 00:00:00'),(152,2,1,'http://www.google.com.ua/search?gcx=w&sourceid=chrome&ie=UTF-8&q=serach+test','2011-11-08 13:42:43','0000-00-00 00:00:00'),(153,2,1,'http://www.google.com.ua/search?gcx=w&sourceid=chrome&ie=UTF-8&q=serach+test','2011-11-08 13:43:38','0000-00-00 00:00:00'),(154,2,1,'http://www.google.com.ua/search?gcx=w&sourceid=chrome&ie=UTF-8&q=serach+test','2011-11-08 13:44:01','0000-00-00 00:00:00'),(155,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-08 13:48:28','0000-00-00 00:00:00'),(156,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-08 16:48:05','0000-00-00 00:00:00'),(157,2,1,'http://nakedsecurity.sophos.com/twitterinfo/','2011-11-08 16:50:43','0000-00-00 00:00:00'),(158,2,1,'http://nakedsecurity.sophos.com/twitterinfo/','2011-11-09 10:09:10','0000-00-00 00:00:00'),(159,2,1,'http://nakedsecurity.sophos.com/twitterinfo/','2011-11-09 10:10:15','0000-00-00 00:00:00'),(160,2,1,'http://nakedsecurity.sophos.com/twitterinfo/','2011-11-09 10:10:53','0000-00-00 00:00:00'),(161,2,1,'http://www.google.com.ua/','2011-11-09 10:11:13','0000-00-00 00:00:00'),(162,2,1,'http://www.google.com.ua/','2011-11-09 10:11:46','0000-00-00 00:00:00'),(163,2,1,'http://www.google.com.ua/','2011-11-09 10:12:28','0000-00-00 00:00:00'),(164,2,1,'http://www.google.com.ua/','2011-11-09 10:13:19','0000-00-00 00:00:00'),(165,2,1,'http://www.google.com.ua/','2011-11-09 10:14:18','0000-00-00 00:00:00'),(166,2,1,'http://local.sayso.com/','2011-11-09 10:18:16','0000-00-00 00:00:00'),(167,2,1,'http://local.sayso.com/','2011-11-09 10:18:24','0000-00-00 00:00:00'),(168,2,1,'http://local.sayso.com/','2011-11-09 10:18:49','0000-00-00 00:00:00'),(169,2,1,'http://local.sayso.com/','2011-11-09 10:19:03','0000-00-00 00:00:00'),(170,2,1,'http://local.sayso.com/','2011-11-09 10:21:08','0000-00-00 00:00:00'),(171,2,1,'http://local.sayso.com/','2011-11-09 10:21:25','0000-00-00 00:00:00'),(172,2,1,'http://saysollc.com/','2011-11-09 10:22:23','0000-00-00 00:00:00'),(173,2,1,'http://app-qa.saysollc.com/','2011-11-09 10:23:53','0000-00-00 00:00:00'),(174,2,1,'http://app-qa.saysollc.com/','2011-11-09 10:24:44','0000-00-00 00:00:00'),(175,2,1,'http://app-qa.saysollc.com/','2011-11-09 10:25:06','0000-00-00 00:00:00'),(176,2,1,'http://app-qa.saysollc.com/','2011-11-09 10:25:24','0000-00-00 00:00:00'),(177,2,1,'http://www.google.com.ua/?q=http://app-qa.saysollc.com/admin','2011-11-09 10:34:52','0000-00-00 00:00:00'),(178,2,1,'http://app-qa.saysollc.com/admin','2011-11-09 10:35:36','0000-00-00 00:00:00'),(179,2,1,'http://app-qa.saysollc.com/admin','2011-11-09 10:35:49','0000-00-00 00:00:00'),(180,2,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','2011-11-09 10:48:07','0000-00-00 00:00:00'),(181,2,1,'http://www.google.com.ua/search?aq=f&gcx=w&sourceid=chrome&ie=UTF-8&q=twitter','2011-11-09 11:08:35','0000-00-00 00:00:00'),(182,2,1,'http://twitter.com/','2011-11-09 11:08:40','0000-00-00 00:00:00'),(183,2,1,'https://twitter.com/#!/activity','2011-11-09 11:16:44','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `metrics_page_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metrics_search`
--

DROP TABLE IF EXISTS `metrics_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metrics_search` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `search_engine_id` int(10) DEFAULT NULL,
  `query` text,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `metrics_search_user_id` (`user_id`),
  KEY `metrics_search_starbar_id` (`starbar_id`),
  KEY `metrics_search_search_engine_id` (`search_engine_id`),
  CONSTRAINT `metrics_search_search_engine_id` FOREIGN KEY (`search_engine_id`) REFERENCES `lookup_search_engines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `metrics_search_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `metrics_search_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metrics_search`
--

LOCK TABLES `metrics_search` WRITE;
/*!40000 ALTER TABLE `metrics_search` DISABLE KEYS */;
INSERT INTO `metrics_search` VALUES (1,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:30:06','0000-00-00 00:00:00'),(2,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:30:13','0000-00-00 00:00:00'),(3,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:32:42','0000-00-00 00:00:00'),(4,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:32:45','0000-00-00 00:00:00'),(5,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:32:48','0000-00-00 00:00:00'),(6,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:33:30','0000-00-00 00:00:00'),(7,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:33:39','0000-00-00 00:00:00'),(8,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 13:33:50','0000-00-00 00:00:00'),(9,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 14:04:08','0000-00-00 00:00:00'),(10,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 14:06:28','0000-00-00 00:00:00'),(11,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 14:06:50','0000-00-00 00:00:00'),(12,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 14:07:07','0000-00-00 00:00:00'),(13,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 14:07:43','0000-00-00 00:00:00'),(14,2,1,2,'testing duduz#sclient=psy-ab','2011-11-07 14:19:50','0000-00-00 00:00:00'),(15,2,1,2,'lalla','2011-11-07 14:20:06','0000-00-00 00:00:00'),(16,2,1,2,'lalla ward','2011-11-07 14:20:18','0000-00-00 00:00:00'),(17,2,1,2,'testing duduz#pq=testing duduz','2011-11-07 14:20:39','0000-00-00 00:00:00'),(18,2,1,2,'lalla ward papapm','2011-11-07 14:20:55','0000-00-00 00:00:00'),(19,2,1,2,'lalla ward papa','2011-11-07 14:21:06','0000-00-00 00:00:00'),(20,2,1,2,'polling events','2011-11-07 14:29:46','0000-00-00 00:00:00'),(21,2,1,2,'polling events in input fields','2011-11-07 14:30:26','0000-00-00 00:00:00'),(22,2,1,2,'polling events in input fields with javascript','2011-11-07 14:30:40','0000-00-00 00:00:00'),(23,2,1,2,'polling events#pq=polling events','2011-11-07 14:39:53','0000-00-00 00:00:00'),(24,2,1,2,'lalaloopsy test','2011-11-07 14:42:59','0000-00-00 00:00:00'),(25,2,1,2,'lalaloopsy test duduz','2011-11-07 14:43:10','0000-00-00 00:00:00'),(26,2,1,2,'lalaloopsy test','2011-11-07 14:43:21','0000-00-00 00:00:00'),(27,2,1,2,'chrome view cookies','2011-11-07 15:05:20','0000-00-00 00:00:00'),(28,2,1,2,'cookies','2011-11-07 15:06:43','0000-00-00 00:00:00'),(29,2,1,2,'cookies','2011-11-07 15:07:06','0000-00-00 00:00:00'),(30,2,1,2,'cookies','2011-11-07 15:07:41','0000-00-00 00:00:00'),(31,2,1,2,'cookies','2011-11-07 15:08:48','0000-00-00 00:00:00'),(32,2,1,2,'cookies','2011-11-07 15:13:07','0000-00-00 00:00:00'),(33,2,1,2,'cookies','2011-11-07 15:13:31','0000-00-00 00:00:00'),(34,2,1,2,'cookies','2011-11-07 15:13:48','0000-00-00 00:00:00'),(35,2,1,2,'cookies','2011-11-07 15:14:21','0000-00-00 00:00:00'),(36,2,1,2,'cookies','2011-11-07 15:15:08','0000-00-00 00:00:00'),(37,2,1,2,'cookies','2011-11-07 15:17:38','0000-00-00 00:00:00'),(38,2,1,2,'serach test','2011-11-08 13:42:43','0000-00-00 00:00:00'),(39,2,1,2,'serach test','2011-11-08 13:43:38','0000-00-00 00:00:00'),(40,2,1,2,'serach test','2011-11-08 13:44:01','0000-00-00 00:00:00'),(41,2,1,2,'search test','2011-11-08 13:44:08','0000-00-00 00:00:00'),(42,2,1,2,'search test again','2011-11-08 13:44:12','0000-00-00 00:00:00'),(43,2,1,2,'twitter','2011-11-09 11:08:35','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `metrics_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metrics_social_activity`
--

DROP TABLE IF EXISTS `metrics_social_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metrics_social_activity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `social_activity_type_id` int(10) DEFAULT NULL,
  `url` text,
  `content` text,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `metrics_social_activity_user_id` (`user_id`),
  KEY `metrics_social_activity_starbar_id` (`starbar_id`),
  KEY `metrics_social_activity_social_activity_type_id` (`social_activity_type_id`),
  CONSTRAINT `metrics_social_activity_social_activity_type_id` FOREIGN KEY (`social_activity_type_id`) REFERENCES `lookup_social_activity_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `metrics_social_activity_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `metrics_social_activity_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metrics_social_activity`
--

LOCK TABLES `metrics_social_activity` WRITE;
/*!40000 ALTER TABLE `metrics_social_activity` DISABLE KEYS */;
INSERT INTO `metrics_social_activity` VALUES (1,2,1,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/',NULL,'2011-11-08 10:12:36','0000-00-00 00:00:00'),(2,2,1,2,NULL,'#php, #javascript, #mysql #freelancer available','2011-11-08 11:00:19','0000-00-00 00:00:00'),(3,2,1,2,NULL,'#php, #javascript, #mysql #freelancer available for hire','2011-11-08 11:05:00','0000-00-00 00:00:00'),(4,2,1,2,NULL,'#php and #mysql #developer','2011-11-08 11:10:30','0000-00-00 00:00:00'),(5,2,1,2,NULL,'#php and #mysql #developer, willing to relocate','2011-11-08 11:10:53','0000-00-00 00:00:00'),(6,2,1,2,NULL,'#php, #javascript, #mysql experienced #developer, willing to #relocate','2011-11-08 11:19:23','0000-00-00 00:00:00'),(7,2,1,2,'https://twitter.com/','#php, #javascript, #mysql experienced #developer, willing to #relocate to EU','2011-11-08 11:26:53','0000-00-00 00:00:00'),(8,2,1,2,'https://twitter.com/','#php, #javascript, #mysql experienced #developer available, willing to #relocate to EU','2011-11-08 11:29:44','0000-00-00 00:00:00'),(9,2,1,1,'Facebook','\"Like\" button','2011-11-08 13:48:34','0000-00-00 00:00:00'),(10,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:31:13','0000-00-00 00:00:00'),(11,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:33:48','0000-00-00 00:00:00'),(12,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:34:08','0000-00-00 00:00:00'),(13,2,1,1,'http://nakedsecurity.sophos.com/2011/08/17/stealing-atm-pins-with-thermal-cameras/','Facebook\'s \"Like\" button clicked','2011-11-08 16:48:10','0000-00-00 00:00:00'),(14,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:52:37','0000-00-00 00:00:00'),(15,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:53:58','0000-00-00 00:00:00'),(16,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:54:54','0000-00-00 00:00:00'),(17,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:55:17','0000-00-00 00:00:00'),(18,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:55:57','0000-00-00 00:00:00'),(19,1,1,1,'dfdf gfd gdgdf gf','dfgd','2011-11-08 16:56:21','0000-00-00 00:00:00'),(20,2,1,2,'https://twitter.com/#!/activity','Philosophy of freelancing and succesful client-worker relations: http://rationalplanet.com/philosophy','2011-11-09 11:16:53','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `metrics_social_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_message`
--

DROP TABLE IF EXISTS `notification_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(20) DEFAULT NULL COMMENT 'To reference in code if necessary',
  `user_id` int(10) DEFAULT NULL COMMENT 'User_id of message CREATOR',
  `survey_id` int(10) DEFAULT NULL,
  `notification_message_group_id` int(10) DEFAULT NULL,
  `ordinal` int(4) DEFAULT NULL,
  `message` varchar(200) DEFAULT NULL,
  `validate` enum('Facebook Connect','Twitter Connect','Take Survey') DEFAULT NULL,
  `notification_area` enum('alerts','promos') DEFAULT NULL,
  `popbox_to_open` varchar(20) DEFAULT NULL COMMENT 'E.g. ''polls'', ''surveys'', ''user-level'', etc.',
  `color` enum('Red','Green') NOT NULL DEFAULT 'Green',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `notifm_user_id` (`user_id`),
  KEY `notifm_notification_message_group_id` (`notification_message_group_id`),
  KEY `short_name` (`short_name`),
  CONSTRAINT `notifm_notification_message_group_id` FOREIGN KEY (`notification_message_group_id`) REFERENCES `notification_message_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notifm_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_message`
--

LOCK TABLES `notification_message` WRITE;
/*!40000 ALTER TABLE `notification_message` DISABLE KEYS */;
INSERT INTO `notification_message` VALUES (3,'FB Account Connected',1,NULL,1,NULL,'Sweet! You earned 750 Chops and 75 Notes for linking your Facebook account',NULL,'alerts',NULL,'Green','0000-00-00 00:00:00','0000-00-00 00:00:00'),(4,'TW Account Connected',1,NULL,1,NULL,'Sweet! You earned 750 Chops and 75 Notes for linking your Twitter account',NULL,'alerts',NULL,'Green','0000-00-00 00:00:00','0000-00-00 00:00:00'),(5,'Checking in',1,NULL,2,NULL,'Dedication is rewarding - Earn by checking in each day!',NULL,'alerts',NULL,'Green','0000-00-00 00:00:00','0000-00-00 00:00:00'),(6,'Welcome',1,NULL,3,10,'Welcome to the Beat Bar!\nWhere your say-so earns pay-so!',NULL,'alerts',NULL,'Green','0000-00-00 00:00:00','0000-00-00 00:00:00'),(7,'Associate your FB Ac',1,NULL,3,20,'Link your Facebook Account to earn 750 Chops and 75 Notes!','Facebook Connect','alerts','user-profile','Green','0000-00-00 00:00:00','0000-00-00 00:00:00'),(8,'Premium Survey Remin',1,1,4,10,'Take the Premium Survey and earn up to 2500 Chops and 250 Notes!','Take Survey','alerts',NULL,'Green','0000-00-00 00:00:00','0000-00-00 00:00:00'),(9,'Daily Deals',1,NULL,5,NULL,'You have 4 new Daily Deals',NULL,'promos','daily-deals','Red','0000-00-00 00:00:00','0000-00-00 00:00:00'),(10,'Update Game',1,NULL,1,NULL,'Message not shown: Update Game Info',NULL,NULL,NULL,'Green','0000-00-00 00:00:00','0000-00-00 00:00:00'),(11,'Level Up',1,NULL,1,NULL,NULL,NULL,'alerts','user-level','Green','0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `notification_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_message_group`
--

DROP TABLE IF EXISTS `notification_message_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_message_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(20) DEFAULT NULL COMMENT 'To reference in code if necessary',
  `user_id` int(10) DEFAULT NULL COMMENT 'User_id of message group CREATOR',
  `starbar_id` int(10) DEFAULT NULL,
  `repeats` int(1) DEFAULT NULL,
  `type` enum('User Actions','Scheduled') DEFAULT NULL,
  `minimum_interval` int(10) DEFAULT NULL,
  `start_after` int(10) DEFAULT NULL COMMENT 'Minimum number of seconds after a user joins before they would see messages in thie group',
  `start_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `notifmg_user_id` (`user_id`),
  KEY `notifmg_starbar_id` (`starbar_id`),
  KEY `short_name` (`short_name`),
  CONSTRAINT `notifmg_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notifmg_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_message_group`
--

LOCK TABLES `notification_message_group` WRITE;
/*!40000 ALTER TABLE `notification_message_group` DISABLE KEYS */;
INSERT INTO `notification_message_group` VALUES (1,'User Actions',1,1,NULL,'User Actions',NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(2,'Send every 12 hours ',1,1,1,'Scheduled',43205,43205,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(3,'Send once upon joini',1,1,NULL,'Scheduled',NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(4,'Send once after a we',1,1,NULL,'Scheduled',NULL,604800,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00'),(5,'Send daily immeediat',1,1,1,'Scheduled',86400,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `notification_message_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_message_user_map`
--

DROP TABLE IF EXISTS `notification_message_user_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_message_user_map` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT 'User to receive/who has received notification',
  `notification_message_id` int(10) DEFAULT NULL,
  `notified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `closed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `notifmum_user_id` (`user_id`),
  KEY `notifmum_notification_message_id` (`notification_message_id`),
  CONSTRAINT `notifmum_notification_message_id` FOREIGN KEY (`notification_message_id`) REFERENCES `notification_message` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notifmum_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_message_user_map`
--

LOCK TABLES `notification_message_user_map` WRITE;
/*!40000 ALTER TABLE `notification_message_user_map` DISABLE KEYS */;
INSERT INTO `notification_message_user_map` VALUES (1,1,9,'2011-10-25 11:16:04','2011-10-25 11:18:45','2011-10-25 14:16:04','2011-10-25 14:18:45'),(2,1,6,'2011-10-25 11:16:04','2011-10-25 11:18:45','2011-10-25 14:16:04','2011-10-25 14:18:45'),(3,1,7,'2011-10-25 11:16:16','2011-10-25 11:18:50','2011-10-25 14:16:16','2011-10-25 14:18:50'),(4,2,9,'2011-10-31 07:08:46','2011-10-31 07:13:55','2011-10-31 10:08:46','2011-10-31 10:13:55'),(5,2,6,'2011-10-31 07:08:46','2011-10-31 07:14:13','2011-10-31 10:08:46','2011-10-31 10:14:13'),(6,2,5,'2011-10-31 07:08:46','2011-10-31 07:14:16','2011-10-31 10:08:46','2011-10-31 10:14:16'),(7,2,7,'2011-10-31 07:09:56','2011-10-31 07:14:06','2011-10-31 10:09:56','2011-10-31 10:14:06'),(8,1,8,'2011-11-02 09:29:04','2011-11-04 07:26:31','2011-11-02 12:29:04','2011-11-04 10:26:31'),(9,1,5,'2011-11-02 09:29:04','2011-11-04 07:26:14','2011-11-02 12:29:04','2011-11-04 10:26:14'),(10,1,9,'2011-11-02 09:29:04','2011-11-04 07:26:31','2011-11-02 12:29:04','2011-11-04 10:26:31'),(11,2,8,'2011-11-07 06:23:23','0000-00-00 00:00:00','2011-11-07 09:23:23','0000-00-00 00:00:00'),(12,2,9,'2011-11-07 06:23:24','0000-00-00 00:00:00','2011-11-07 09:23:24','0000-00-00 00:00:00'),(13,2,5,'2011-11-07 06:23:24','0000-00-00 00:00:00','2011-11-07 09:23:24','0000-00-00 00:00:00'),(14,1,5,'2011-11-07 14:22:04','0000-00-00 00:00:00','2011-11-07 17:22:04','0000-00-00 00:00:00'),(15,1,9,'2011-11-07 14:22:04','0000-00-00 00:00:00','2011-11-07 17:22:04','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `notification_message_user_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preference_general`
--

DROP TABLE IF EXISTS `preference_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preference_general` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `poll_frequency_id` int(10) DEFAULT NULL,
  `email_frequency_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `preference_general_user_id` (`user_id`),
  KEY `preference_general_poll_frequency_id` (`poll_frequency_id`),
  KEY `preference_general_email_frequency_id` (`email_frequency_id`),
  CONSTRAINT `preference_general_email_frequency_id` FOREIGN KEY (`email_frequency_id`) REFERENCES `lookup_email_frequency` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `preference_general_poll_frequency_id` FOREIGN KEY (`poll_frequency_id`) REFERENCES `lookup_poll_frequency` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `preference_general_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preference_general`
--

LOCK TABLES `preference_general` WRITE;
/*!40000 ALTER TABLE `preference_general` DISABLE KEYS */;
INSERT INTO `preference_general` VALUES (1,3,1,3,'2011-10-31 12:36:10','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `preference_general` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preference_survey_type`
--

DROP TABLE IF EXISTS `preference_survey_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preference_survey_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `survey_type_id` int(10) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `preference_survey_type_survey_type_id` (`survey_type_id`),
  KEY `preference_survey_type_user_id` (`user_id`),
  CONSTRAINT `preference_survey_type_survey_type_id` FOREIGN KEY (`survey_type_id`) REFERENCES `lookup_survey_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `preference_survey_type_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preference_survey_type`
--

LOCK TABLES `preference_survey_type` WRITE;
/*!40000 ALTER TABLE `preference_survey_type` DISABLE KEYS */;
INSERT INTO `preference_survey_type` VALUES (1,1,3,'2011-10-31 12:36:10','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `preference_survey_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` char(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `id_auto` int(10) NOT NULL AUTO_INCREMENT,
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_auto`),
  UNIQUE KEY `unique_session_id` (`id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
INSERT INTO `session` VALUES ('pl4bev267u4uig30ifjvnv7943',10,1320424875,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752424875;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752424875;}}global|a:2:{s:8:\"timeZone\";N;s:9:\"gaming_id\";s:32:\"945ee49e2239c1b8947d2c95fd67439f\";}user|a:1:{s:2:\"id\";s:1:\"1\";}persistent_objects|a:1:{s:4:\"User\";a:1:{i:1;s:632:\"C:4:\"User\":615:{a:5:{s:8:\"_created\";s:19:\"2011-10-25 14:26:30\";s:9:\"_modified\";s:19:\"0000-00-00 00:00:00\";s:3:\"_id\";i:1;s:6:\"_idKey\";s:2:\"id\";s:5:\"_data\";a:15:{s:8:\"username\";s:5:\"david\";s:8:\"password\";s:32:\"19f9cdef4ed5870c951e72d7401a3ba5\";s:13:\"password_salt\";s:4:\"doon\";s:10:\"first_name\";s:5:\"David\";s:9:\"last_name\";s:5:\"James\";s:9:\"gender_id\";s:1:\"1\";s:12:\"ethnicity_id\";s:1:\"1\";s:15:\"income_range_id\";s:1:\"4\";s:9:\"birthdate\";s:10:\"1969-11-01\";s:3:\"url\";s:27:\"http://www.davidbjames.info\";s:8:\"timezone\";s:5:\"+1:00\";s:16:\"primary_email_id\";s:1:\"1\";s:16:\"primary_phone_id\";N;s:18:\"primary_address_id\";N;s:12:\"user_role_id\";N;}}}\";}}'),('c8f4552ba22ccb1af703ecebfaa1cb9e',11,1320920420,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752920408;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752920408;}}global|a:2:{s:8:\"timeZone\";N;s:9:\"gaming_id\";s:32:\"1489307c9ead1fd87f17ae9256ab308d\";}user|a:1:{s:2:\"id\";i:2;}'),('0202347f140d8236ff53ed4ee4a73bbc',12,1320686596,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752686596;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752686596;}}global|a:2:{s:8:\"timeZone\";N;s:9:\"gaming_id\";s:32:\"945ee49e2239c1b8947d2c95fd67439f\";}user|a:1:{s:2:\"id\";s:1:\"1\";}persistent_objects|a:1:{s:4:\"User\";a:1:{i:1;s:632:\"C:4:\"User\":615:{a:5:{s:8:\"_created\";s:19:\"2011-10-25 14:26:30\";s:9:\"_modified\";s:19:\"0000-00-00 00:00:00\";s:3:\"_id\";i:1;s:6:\"_idKey\";s:2:\"id\";s:5:\"_data\";a:15:{s:8:\"username\";s:5:\"david\";s:8:\"password\";s:32:\"19f9cdef4ed5870c951e72d7401a3ba5\";s:13:\"password_salt\";s:4:\"doon\";s:10:\"first_name\";s:5:\"David\";s:9:\"last_name\";s:5:\"James\";s:9:\"gender_id\";s:1:\"1\";s:12:\"ethnicity_id\";s:1:\"1\";s:15:\"income_range_id\";s:1:\"4\";s:9:\"birthdate\";s:10:\"1969-11-01\";s:3:\"url\";s:27:\"http://www.davidbjames.info\";s:8:\"timezone\";s:5:\"+1:00\";s:16:\"primary_email_id\";s:1:\"1\";s:16:\"primary_phone_id\";N;s:18:\"primary_address_id\";N;s:12:\"user_role_id\";N;}}}\";}}'),('45ainc61g23dpsjc4p18tliuh0',13,1320752456,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752752456;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752752456;}}global|a:1:{s:8:\"timeZone\";N;}user|a:1:{s:2:\"id\";N;}'),('2tp882u6bb39p9ta3gq8e9g7l6',14,1320755749,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752755749;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752755749;}}global|a:1:{s:8:\"timeZone\";N;}user|a:1:{s:2:\"id\";N;}'),('bsv4qg3690b4ihr5c2kp109av1',15,1320833687,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752833687;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752833687;}}global|a:1:{s:8:\"timeZone\";N;}user|a:1:{s:2:\"id\";N;}'),('ng6cl8phha70jdeh984ks4lu54',16,1320833926,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752833926;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752833926;}}global|a:1:{s:8:\"timeZone\";N;}user|a:1:{s:2:\"id\";N;}'),('t987a8s2m8j9smeii4o62jjdt1',17,1320833938,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752833938;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752833938;}}global|a:1:{s:8:\"timeZone\";N;}user|a:1:{s:2:\"id\";N;}'),('qlugfim3cdpjoboms9ncpkqaf7',18,1320834113,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752834113;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752834113;}}global|a:1:{s:8:\"timeZone\";N;}user|a:1:{s:2:\"id\";N;}'),('l33m5c88dgkpf1sd28pu5djuq3',19,1320834123,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752834123;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752834123;}}global|a:1:{s:8:\"timeZone\";N;}user|a:1:{s:2:\"id\";N;}'),('97535bd6b9cbfa22989d2f16a4acffd4',20,1320920472,31500000,'__ZF|a:2:{s:4:\"user\";a:1:{s:3:\"ENT\";i:1752920462;}s:6:\"global\";a:1:{s:3:\"ENT\";i:1752920462;}}global|a:2:{s:8:\"timeZone\";N;s:9:\"gaming_id\";s:32:\"1489307c9ead1fd87f17ae9256ab308d\";}user|a:1:{s:2:\"id\";i:2;}');
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `starbar`
--

DROP TABLE IF EXISTS `starbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `starbar` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `user_pseudonym` varchar(32) DEFAULT NULL,
  `domain` varchar(64) DEFAULT NULL,
  `auth_key` varchar(32) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `starbar_unique` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `starbar`
--

LOCK TABLES `starbar` WRITE;
/*!40000 ALTER TABLE `starbar` DISABLE KEYS */;
INSERT INTO `starbar` VALUES (1,'hellomusic','Hello Music',NULL,'Rocker','hellomusic.com','309e34632c2ca9cd5edaf2388f5fa3db','2011-10-25 11:26:34','2011-10-25 11:26:34');
/*!40000 ALTER TABLE `starbar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `starbar_content`
--

DROP TABLE IF EXISTS `starbar_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `starbar_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `content` text,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `starbar_content_user_id` (`user_id`),
  KEY `starbar_content_starbar_id` (`starbar_id`),
  CONSTRAINT `starbar_content_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `starbar_content_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `starbar_content`
--

LOCK TABLES `starbar_content` WRITE;
/*!40000 ALTER TABLE `starbar_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `starbar_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `starbar_user_map`
--

DROP TABLE IF EXISTS `starbar_user_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `starbar_user_map` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL COMMENT 'Is this Starbar ''instance'' activated for this user',
  `onboarded` tinyint(1) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `starbar_user_map_unique` (`user_id`,`starbar_id`),
  KEY `starbar_user_map_starbar_id` (`starbar_id`),
  CONSTRAINT `starbar_user_map_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `starbar_user_map_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `starbar_user_map`
--

LOCK TABLES `starbar_user_map` WRITE;
/*!40000 ALTER TABLE `starbar_user_map` DISABLE KEYS */;
INSERT INTO `starbar_user_map` VALUES (1,2,1,1,1,'2011-10-28 11:12:20','2011-11-10 10:21:02');
/*!40000 ALTER TABLE `starbar_user_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study`
--

DROP TABLE IF EXISTS `study`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `size` int(10) DEFAULT NULL,
  `size_minimum` int(10) DEFAULT NULL,
  `begin_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `click_track` tinyint(1) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `study_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study`
--

LOCK TABLES `study` WRITE;
/*!40000 ALTER TABLE `study` DISABLE KEYS */;
/*!40000 ALTER TABLE `study` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_cell`
--

DROP TABLE IF EXISTS `study_cell`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_cell` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `study_id` int(10) DEFAULT NULL,
  `description` varchar(128) DEFAULT NULL,
  `size` int(10) DEFAULT NULL,
  `cell_type` enum('test','control') DEFAULT 'test' COMMENT 'control means all ads associated are generic and not part of the study',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `study_cell_study_id` (`study_id`),
  CONSTRAINT `study_cell_study_id` FOREIGN KEY (`study_id`) REFERENCES `study` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_cell`
--

LOCK TABLES `study_cell` WRITE;
/*!40000 ALTER TABLE `study_cell` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_cell` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_cell_assignment`
--

DROP TABLE IF EXISTS `study_cell_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_cell_assignment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `study_cell_id` int(10) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `study_cell_assignment_unique` (`user_id`,`study_cell_id`),
  KEY `study_cell_assignment_study_cell_id` (`study_cell_id`),
  CONSTRAINT `study_cell_assignment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `study_cell_assignment_study_cell_id` FOREIGN KEY (`study_cell_id`) REFERENCES `study_cell` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_cell_assignment`
--

LOCK TABLES `study_cell_assignment` WRITE;
/*!40000 ALTER TABLE `study_cell_assignment` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_cell_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_cell_qualifier_browsing`
--

DROP TABLE IF EXISTS `study_cell_qualifier_browsing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_cell_qualifier_browsing` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cell_id` int(10) DEFAULT NULL,
  `exclude` tinyint(1) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `timeframe_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `study_cell_qualifier_browsing_cell_id` (`cell_id`),
  KEY `study_cell_qualifier_browsing_timeframe_id` (`timeframe_id`),
  CONSTRAINT `study_cell_qualifier_browsing_cell_id` FOREIGN KEY (`cell_id`) REFERENCES `study_cell` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `study_cell_qualifier_browsing_timeframe_id` FOREIGN KEY (`timeframe_id`) REFERENCES `lookup_timeframe` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_cell_qualifier_browsing`
--

LOCK TABLES `study_cell_qualifier_browsing` WRITE;
/*!40000 ALTER TABLE `study_cell_qualifier_browsing` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_cell_qualifier_browsing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_cell_qualifier_search`
--

DROP TABLE IF EXISTS `study_cell_qualifier_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_cell_qualifier_search` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cell_id` int(10) DEFAULT NULL,
  `exclude` tinyint(1) DEFAULT NULL,
  `term` varchar(255) DEFAULT NULL COMMENT 'search term/query',
  `timeframe_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `study_cell_qualifier_search_cell_id` (`cell_id`),
  KEY `study_cell_qualifier_search_timeframe_id` (`timeframe_id`),
  CONSTRAINT `study_cell_qualifier_search_cell_id` FOREIGN KEY (`cell_id`) REFERENCES `study_cell` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `study_cell_qualifier_search_timeframe_id` FOREIGN KEY (`timeframe_id`) REFERENCES `lookup_timeframe` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_cell_qualifier_search`
--

LOCK TABLES `study_cell_qualifier_search` WRITE;
/*!40000 ALTER TABLE `study_cell_qualifier_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_cell_qualifier_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_cell_qualifier_search_engines_map`
--

DROP TABLE IF EXISTS `study_cell_qualifier_search_engines_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_cell_qualifier_search_engines_map` (
  `cell_qualifier_search_id` int(10) NOT NULL,
  `search_engines_id` int(10) NOT NULL,
  UNIQUE KEY `map_unique` (`cell_qualifier_search_id`,`search_engines_id`),
  KEY `map_search_engine` (`search_engines_id`),
  CONSTRAINT `map_cell_qualifier_search` FOREIGN KEY (`cell_qualifier_search_id`) REFERENCES `study_cell_qualifier_search` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `map_search_engine` FOREIGN KEY (`search_engines_id`) REFERENCES `lookup_search_engines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_cell_qualifier_search_engines_map`
--

LOCK TABLES `study_cell_qualifier_search_engines_map` WRITE;
/*!40000 ALTER TABLE `study_cell_qualifier_search_engines_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_cell_qualifier_search_engines_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_cell_tag_map`
--

DROP TABLE IF EXISTS `study_cell_tag_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_cell_tag_map` (
  `cell_id` int(10) NOT NULL,
  `tag_id` int(10) NOT NULL,
  UNIQUE KEY `cell_id` (`cell_id`,`tag_id`),
  KEY `study_cell_tag_map_tag_id` (`tag_id`),
  CONSTRAINT `study_cell_tag_map_cell_id` FOREIGN KEY (`cell_id`) REFERENCES `study_cell` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `study_cell_tag_map_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `study_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_cell_tag_map`
--

LOCK TABLES `study_cell_tag_map` WRITE;
/*!40000 ALTER TABLE `study_cell_tag_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_cell_tag_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_creative`
--

DROP TABLE IF EXISTS `study_creative`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_creative` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `mime_type_id` int(10) DEFAULT NULL,
  `name` varchar(100) NOT NULL COMMENT 'label',
  `url` varchar(255) DEFAULT NULL COMMENT 'Null if binary data exists in study_creative_data',
  `target_url` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `creative_mime_type_id` (`mime_type_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `creative_mime_type_id` FOREIGN KEY (`mime_type_id`) REFERENCES `lookup_mime_type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `study_creative_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_creative`
--

LOCK TABLES `study_creative` WRITE;
/*!40000 ALTER TABLE `study_creative` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_creative` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_creative_data`
--

DROP TABLE IF EXISTS `study_creative_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_creative_data` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `study_creative_id` int(10) DEFAULT NULL,
  `data` mediumblob NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `creative_data_study_creative_id` (`study_creative_id`),
  CONSTRAINT `creative_data_study_creative_id` FOREIGN KEY (`study_creative_id`) REFERENCES `study_creative` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_creative_data`
--

LOCK TABLES `study_creative_data` WRITE;
/*!40000 ALTER TABLE `study_creative_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_creative_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_creative_map`
--

DROP TABLE IF EXISTS `study_creative_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_creative_map` (
  `study_id` int(10) NOT NULL,
  `creative_id` int(10) NOT NULL,
  UNIQUE KEY `study_creative_map_unique` (`study_id`,`creative_id`),
  KEY `study_creative_map_creative_id` (`creative_id`),
  CONSTRAINT `study_creative_map_creative_id` FOREIGN KEY (`creative_id`) REFERENCES `study_creative` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `study_creative_map_study_id` FOREIGN KEY (`study_id`) REFERENCES `study` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_creative_map`
--

LOCK TABLES `study_creative_map` WRITE;
/*!40000 ALTER TABLE `study_creative_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_creative_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_creative_tag_map`
--

DROP TABLE IF EXISTS `study_creative_tag_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_creative_tag_map` (
  `creative_id` int(10) NOT NULL,
  `tag_id` int(10) NOT NULL,
  UNIQUE KEY `creative_tag_map_unique` (`creative_id`,`tag_id`),
  KEY `creative_tag_map_tag_id` (`tag_id`),
  CONSTRAINT `creative_tag_map_creative_id` FOREIGN KEY (`creative_id`) REFERENCES `study_creative` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `creative_tag_map_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `study_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_creative_tag_map`
--

LOCK TABLES `study_creative_tag_map` WRITE;
/*!40000 ALTER TABLE `study_creative_tag_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_creative_tag_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_domain`
--

DROP TABLE IF EXISTS `study_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_domain` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `domain` varchar(100) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_unique` (`domain`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `study_domain_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_domain`
--

LOCK TABLES `study_domain` WRITE;
/*!40000 ALTER TABLE `study_domain` DISABLE KEYS */;
INSERT INTO `study_domain` VALUES (1,1,'sdlkfjsdflk.com','2011-10-31 16:32:09','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `study_domain` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_quota`
--

DROP TABLE IF EXISTS `study_quota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_quota` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `study_id` int(10) DEFAULT NULL,
  `percentile_id` int(10) DEFAULT NULL,
  `gender_id` int(10) DEFAULT NULL,
  `age_range_id` int(10) DEFAULT NULL,
  `ethnicity_id` int(10) DEFAULT NULL,
  `income_range_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `study_quota_study_id` (`study_id`),
  KEY `study_quota_percentile_id` (`percentile_id`),
  KEY `study_quota_gender_id` (`gender_id`),
  KEY `study_quota_age_range_id` (`age_range_id`),
  KEY `study_quota_ethnicity_id` (`ethnicity_id`),
  KEY `study_quota_income_range_id` (`income_range_id`),
  CONSTRAINT `study_quota_age_range_id` FOREIGN KEY (`age_range_id`) REFERENCES `lookup_age_range` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `study_quota_ethnicity_id` FOREIGN KEY (`ethnicity_id`) REFERENCES `lookup_ethnicity` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `study_quota_gender_id` FOREIGN KEY (`gender_id`) REFERENCES `lookup_gender` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `study_quota_income_range_id` FOREIGN KEY (`income_range_id`) REFERENCES `lookup_income_range` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `study_quota_percentile_id` FOREIGN KEY (`percentile_id`) REFERENCES `lookup_quota_percentile` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `study_quota_study_id` FOREIGN KEY (`study_id`) REFERENCES `study` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_quota`
--

LOCK TABLES `study_quota` WRITE;
/*!40000 ALTER TABLE `study_quota` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_quota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_search_engines_map`
--

DROP TABLE IF EXISTS `study_search_engines_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_search_engines_map` (
  `study_id` int(10) NOT NULL,
  `search_engines_id` int(10) NOT NULL,
  UNIQUE KEY `map_unique` (`study_id`,`search_engines_id`),
  KEY `search_engine_map_search_engine` (`search_engines_id`),
  CONSTRAINT `search_engine_map_search_engine` FOREIGN KEY (`search_engines_id`) REFERENCES `lookup_search_engines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `search_engine_map_study` FOREIGN KEY (`study_id`) REFERENCES `study` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_search_engines_map`
--

LOCK TABLES `study_search_engines_map` WRITE;
/*!40000 ALTER TABLE `study_search_engines_map` DISABLE KEYS */;
INSERT INTO `study_search_engines_map` VALUES (2,2);
/*!40000 ALTER TABLE `study_search_engines_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_social_activity_type_map`
--

DROP TABLE IF EXISTS `study_social_activity_type_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_social_activity_type_map` (
  `study_id` int(10) NOT NULL,
  `social_activity_type_id` int(10) NOT NULL,
  UNIQUE KEY `map_unique` (`study_id`,`social_activity_type_id`),
  KEY `social_activity_map_social_activity` (`social_activity_type_id`),
  CONSTRAINT `social_activity_map_social_activity` FOREIGN KEY (`social_activity_type_id`) REFERENCES `lookup_social_activity_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `social_activity_map_study` FOREIGN KEY (`study_id`) REFERENCES `study` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_social_activity_type_map`
--

LOCK TABLES `study_social_activity_type_map` WRITE;
/*!40000 ALTER TABLE `study_social_activity_type_map` DISABLE KEYS */;
INSERT INTO `study_social_activity_type_map` VALUES (2,2);
/*!40000 ALTER TABLE `study_social_activity_type_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_survey`
--

DROP TABLE IF EXISTS `study_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_survey` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL COMMENT 'URL to the iframe content',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_survey`
--

LOCK TABLES `study_survey` WRITE;
/*!40000 ALTER TABLE `study_survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_survey_criterion`
--

DROP TABLE IF EXISTS `study_survey_criterion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_survey_criterion` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `site` varchar(255) DEFAULT NULL COMMENT 'domain',
  `timeframe_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `site` (`site`,`timeframe_id`),
  KEY `study_survey_criterion_timeframe_id` (`timeframe_id`),
  CONSTRAINT `study_survey_criterion_timeframe_id` FOREIGN KEY (`timeframe_id`) REFERENCES `lookup_timeframe` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_survey_criterion`
--

LOCK TABLES `study_survey_criterion` WRITE;
/*!40000 ALTER TABLE `study_survey_criterion` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_survey_criterion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_survey_criterion_map`
--

DROP TABLE IF EXISTS `study_survey_criterion_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_survey_criterion_map` (
  `study_survey_map_id` int(10) NOT NULL,
  `survey_criterion_id` int(10) NOT NULL,
  UNIQUE KEY `map_unique` (`study_survey_map_id`,`survey_criterion_id`),
  KEY `survey_criterion_survey_criterion_id` (`survey_criterion_id`),
  CONSTRAINT `survey_criterion_map_study_survey_map_id` FOREIGN KEY (`study_survey_map_id`) REFERENCES `study_survey_map` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_criterion_survey_criterion_id` FOREIGN KEY (`survey_criterion_id`) REFERENCES `study_survey_criterion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_survey_criterion_map`
--

LOCK TABLES `study_survey_criterion_map` WRITE;
/*!40000 ALTER TABLE `study_survey_criterion_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_survey_criterion_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_survey_map`
--

DROP TABLE IF EXISTS `study_survey_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_survey_map` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `study_id` int(10) NOT NULL,
  `survey_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `map_unique` (`study_id`,`survey_id`),
  KEY `survey_map_survey_id` (`survey_id`),
  CONSTRAINT `survey_map_study_id` FOREIGN KEY (`study_id`) REFERENCES `study` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_map_survey_id` FOREIGN KEY (`survey_id`) REFERENCES `study_survey` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_survey_map`
--

LOCK TABLES `study_survey_map` WRITE;
/*!40000 ALTER TABLE `study_survey_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_survey_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_tag`
--

DROP TABLE IF EXISTS `study_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_tag` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL COMMENT 'label',
  `tag` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_unique` (`tag`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `study_tag_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_tag`
--

LOCK TABLES `study_tag` WRITE;
/*!40000 ALTER TABLE `study_tag` DISABLE KEYS */;
INSERT INTO `study_tag` VALUES (1,1,'Aaaa','sdd','2011-10-31 16:32:09','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `study_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_tag_domain_map`
--

DROP TABLE IF EXISTS `study_tag_domain_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_tag_domain_map` (
  `tag_id` int(10) NOT NULL,
  `domain_id` int(10) NOT NULL,
  UNIQUE KEY `tag_domain_map_unique` (`domain_id`,`tag_id`),
  KEY `tag_domain_map_tag_id` (`tag_id`),
  CONSTRAINT `tag_domain_map_domain_id` FOREIGN KEY (`domain_id`) REFERENCES `study_domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tag_domain_map_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `study_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_tag_domain_map`
--

LOCK TABLES `study_tag_domain_map` WRITE;
/*!40000 ALTER TABLE `study_tag_domain_map` DISABLE KEYS */;
INSERT INTO `study_tag_domain_map` VALUES (1,1);
/*!40000 ALTER TABLE `study_tag_domain_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey`
--

DROP TABLE IF EXISTS `survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT 'User who created the survey (not the respondent)',
  `type` enum('poll','survey') NOT NULL,
  `origin` enum('SurveyGizmo','Internal') NOT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `external_id` varchar(32) DEFAULT NULL,
  `external_key` varchar(32) DEFAULT NULL,
  `premium` tinyint(1) DEFAULT NULL,
  `number_of_answers` int(4) DEFAULT NULL,
  `number_of_questions` int(4) DEFAULT NULL,
  `ordinal` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_unique` (`external_id`),
  KEY `survey_starbar_id` (`starbar_id`),
  CONSTRAINT `survey_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey`
--

LOCK TABLES `survey` WRITE;
/*!40000 ALTER TABLE `survey` DISABLE KEYS */;
INSERT INTO `survey` VALUES (1,1,'survey','SurveyGizmo',1,'Hellomusic.com','2011-10-25 11:26:48','0000-00-00 00:00:00','699570','Hellomusic-com-V6',1,NULL,42,30),(2,1,'survey','SurveyGizmo',1,'What\'s the deal?','2011-10-25 11:26:48','0000-00-00 00:00:00','655509','Gear-Deals-survey-2',NULL,NULL,5,40),(3,1,'survey','SurveyGizmo',1,'Listening','2011-10-25 11:26:48','0000-00-00 00:00:00','655517','Listening',NULL,NULL,5,50),(4,1,'survey','SurveyGizmo',1,'Where did you get that?','2011-10-25 11:26:48','0000-00-00 00:00:00','655526','Where-did-you-get-that',NULL,NULL,5,60),(5,1,'survey','SurveyGizmo',1,'Take 1','2011-10-25 11:26:48','0000-00-00 00:00:00','655529','Take-1',NULL,NULL,5,70),(6,1,'survey','SurveyGizmo',1,'That\'s What Friends Are For','2011-10-25 11:26:49','0000-00-00 00:00:00','655531','That-s-What-Friends-Are-For',NULL,NULL,5,80),(7,1,'survey','SurveyGizmo',1,'Services','2011-10-25 11:26:49','0000-00-00 00:00:00','656924','Services',NULL,NULL,5,90),(8,1,'survey','SurveyGizmo',1,'On the Road Again','2011-10-25 11:26:49','0000-00-00 00:00:00','656935','On-the-Road-Again',NULL,NULL,5,100),(9,1,'survey','SurveyGizmo',1,'Vintage Gear','2011-10-25 11:26:49','0000-00-00 00:00:00','656939','Vintage-Gear',NULL,NULL,5,110),(10,1,'poll','SurveyGizmo',1,'What\'s your favorite type of guitar pickup?','2011-10-25 11:26:49','0000-00-00 00:00:00','655490','6CX8ILU9ED46AUR9T77SWA4LYC7SLD',NULL,4,NULL,30),(11,1,'poll','SurveyGizmo',1,'What drives you most to play your instrument?','2011-10-25 11:26:49','0000-00-00 00:00:00','655495','D16FBY7AGJWEH9D0KCD4679SAZCHLR',NULL,4,NULL,40),(12,1,'poll','SurveyGizmo',1,'Songwriters: What\'s your writing style?','2011-10-25 11:26:49','0000-00-00 00:00:00','655497','1TOEMQHSZ0T261M7EUC4EF2L68IIZM',NULL,4,NULL,50),(13,1,'poll','SurveyGizmo',1,'Do you prefer to write songs:','2011-10-25 11:26:49','0000-00-00 00:00:00','656955','98FUNHX6TV7W7PUS05ML8Q6NGEXETG',NULL,2,NULL,60),(14,1,'poll','SurveyGizmo',1,'What mic setup do you use for recording guitars?','2011-10-25 11:26:49','0000-00-00 00:00:00','656956','WN7ZEZL333HX79TS99NXHQ3FOJGE81',NULL,5,NULL,70),(15,1,'poll','SurveyGizmo',1,'Would you ever take an online class for mixing, tracking, mastering etc?','2011-10-25 11:26:49','0000-00-00 00:00:00','656962','2AG59VNFTT8HGWKTKV486MMF40L3Y3',NULL,3,NULL,80),(16,1,'poll','SurveyGizmo',1,'What level of touring have you reached?','2011-10-25 11:26:49','0000-00-00 00:00:00','656964','V7GUMWTHQQH8B75ZIAZYA8O1JZ7IQ6',NULL,5,NULL,90),(17,1,'poll','SurveyGizmo',1,'How do you get the word out about your gigs offline?','2011-10-25 11:26:49','0000-00-00 00:00:00','657103','N8H3PPR29Q9A47Z8KE1H7FD1PZRYM2',NULL,3,NULL,100),(18,1,'poll','SurveyGizmo',1,'How do you get the word out about your gigs online?','2011-10-25 11:26:49','0000-00-00 00:00:00','657105','BRW0KXPB3NEGXUDIG41OBGNL8K0QEI',NULL,5,NULL,110),(19,1,'poll','SurveyGizmo',1,'Where do you rehearse?','2011-10-25 11:26:49','0000-00-00 00:00:00','656969','DM2PKFOZ806SRVCR7KM0ZQJB5N4TF5',NULL,4,NULL,120),(20,1,'poll','SurveyGizmo',1,'China Cymbals: Great or Grating?','2011-10-25 11:26:49','0000-00-00 00:00:00','656970','RFFFF2SM05B7L59Z7ABUWHY4BAZV1F',NULL,2,NULL,130),(21,1,'poll','SurveyGizmo',1,'Do you bring a lighting rig to your gigs?','2011-10-25 11:26:49','0000-00-00 00:00:00','656971','RD484EZECWVD2P7EJIMP702KZAJIBR',NULL,2,NULL,140),(22,1,'poll','SurveyGizmo',1,'Is a great guitar or great amp more important for good tone?','2011-10-25 11:26:49','0000-00-00 00:00:00','656973','5TC4YF9M6L2JSY9MK4OKSYHAYEF1TK',NULL,2,NULL,150),(23,1,'poll','SurveyGizmo',1,'How do you release your music offline?','2011-10-25 11:26:50','0000-00-00 00:00:00','657114','GL157C4C1B9OD0DRCF478XIZ243MK4',NULL,3,NULL,160),(24,1,'poll','SurveyGizmo',1,'How do you release your music online?','2011-10-25 11:26:50','0000-00-00 00:00:00','657116','6ZZAZ8SL05C6YVIJMR6SHKR1ZHQCU8',NULL,3,NULL,170),(25,1,'poll','SurveyGizmo',1,'What kind of strings do you prefer to use on your acoustic guitar?','2011-10-25 11:26:50','0000-00-00 00:00:00','656975','6238NNZ7G9JUL2Y2MGR9CT9Q8A6UL1',NULL,4,NULL,180),(26,1,'poll','SurveyGizmo',1,'Acoustic guitar players: Do you use an acoustic guitar amp live or plug into a DI for the PA?','2011-10-25 11:26:50','0000-00-00 00:00:00','656976','3338TC6KNTTJK3FYBK1R5Y9SU6CD5Z',NULL,2,NULL,190),(27,1,'poll','SurveyGizmo',1,'Do you know how to register the songs you write with the US Copy-write Office?','2011-10-25 11:26:50','0000-00-00 00:00:00','656980','AYB0X7Z8J0EAC4LDK9XJ2X36RZ715G',NULL,2,NULL,200),(28,1,'poll','SurveyGizmo',1,'Bass players: What kind of strings do you like to use?','2011-10-25 11:26:50','0000-00-00 00:00:00','656981','ZF201H7XDJP0BGMP0WTXG4L4EEEGDX',NULL,3,NULL,210),(29,1,'poll','SurveyGizmo',1,'Bass players: How often do you change your strings?','2011-10-25 11:26:50','0000-00-00 00:00:00','656982','XIWBGV0Y0B0BK7A2MI1BR4PPX51JD1',NULL,4,NULL,220),(30,1,'poll','SurveyGizmo',1,'Do you prefer to read music magazines in print or online?','2011-10-25 11:26:50','0000-00-00 00:00:00','656983','LAQIHAPW54609YPX15V8R2ZBRE17W7',NULL,2,NULL,230),(31,1,'poll','SurveyGizmo',1,'Are you looking for a record deal?','2011-10-25 11:26:50','0000-00-00 00:00:00','656985','FK22UNRV2ZBBSYVIV9V5O2G1LMJ4IA',NULL,2,NULL,240),(32,1,'poll','SurveyGizmo',1,'Where do you most often purchase new music?','2011-10-25 11:26:50','0000-00-00 00:00:00','656987','BGAUVW75A144NUVPL6H2FMZNIDSEM2',NULL,4,NULL,250),(33,1,'poll','SurveyGizmo',1,'Would you consider hiring a service that helped you develop a marketing plan for your release?','2011-10-25 11:26:50','0000-00-00 00:00:00','656988','UFSCG9YOFD7OUMPVPD10D1T62TIE84',NULL,2,NULL,260),(34,1,'poll','SurveyGizmo',1,'Guitarists: What power tubes sound the best overdriven?','2011-10-25 11:26:50','0000-00-00 00:00:00','656990','JDBWENY61RIRUWSWJ5GI0760XTL6F2',NULL,6,NULL,270),(35,1,'poll','SurveyGizmo',1,'In a perfect world, how often would you like to gig?','2011-10-25 11:26:50','0000-00-00 00:00:00','656991','NJFGAU51YZ8EW2NXW054JJHT0UN0X3',NULL,5,NULL,280),(36,1,'poll','SurveyGizmo',1,'Bass players - do you prefer active or passive pickups?','2011-10-25 11:26:50','0000-00-00 00:00:00','657079','3SDQ1XMCPYLND52JGSV9HQHSGKI42P',NULL,2,NULL,290),(37,1,'poll','SurveyGizmo',1,'Bass players - fretted or fretless?','2011-10-25 11:26:51','0000-00-00 00:00:00','657082','P6MOV8HLDFUJCYAWR0S3P78ONVL68W',NULL,2,NULL,300),(38,1,'poll','SurveyGizmo',1,'Drummers, what drum heads do you prefer?','2011-10-25 11:26:51','0000-00-00 00:00:00','657084','R077KZ4BNMO678P2Q1OPC1N1WS2HSY',NULL,3,NULL,310),(39,1,'poll','SurveyGizmo',1,'Drummers, what sticks do you prefer?','2011-10-25 11:26:51','0000-00-00 00:00:00','657086','F99FTZ7WGVRG0AG5HAWDYQ9BBII2F0',NULL,6,NULL,320),(40,1,'poll','SurveyGizmo',1,'Keyboardists - Weighted or non-weighted keys?','2011-10-25 11:26:51','0000-00-00 00:00:00','657090','KP2OSZD56NGLXETMLPLCFC2SVH6DSE',NULL,2,NULL,330);
/*!40000 ALTER TABLE `survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_user_map`
--

DROP TABLE IF EXISTS `survey_user_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_user_map` (
  `survey_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `response_id` varchar(32) DEFAULT NULL,
  `status` enum('completed','archived','new','disqualified') NOT NULL DEFAULT 'new',
  UNIQUE KEY `survey_user_map_unique` (`survey_id`,`user_id`),
  KEY `survey_user_map_user_id` (`user_id`),
  CONSTRAINT `survey_user_map_survey_id` FOREIGN KEY (`survey_id`) REFERENCES `survey` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `survey_user_map_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_user_map`
--

LOCK TABLES `survey_user_map` WRITE;
/*!40000 ALTER TABLE `survey_user_map` DISABLE KEYS */;
INSERT INTO `survey_user_map` VALUES (1,1,'2011-10-25 14:18:58',NULL,'archived'),(1,2,'2011-10-31 10:17:17',NULL,'new'),(2,1,'2011-10-25 14:18:58',NULL,'archived'),(2,2,'2011-10-31 10:17:17',NULL,'new'),(3,1,'2011-10-25 14:18:58',NULL,'archived'),(3,2,'2011-10-31 10:17:17',NULL,'new'),(4,1,'2011-10-25 14:18:58',NULL,'archived'),(4,2,'2011-10-31 10:17:17',NULL,'new'),(5,1,'2011-10-25 14:18:58',NULL,'archived'),(6,1,'2011-10-25 14:18:58',NULL,'archived'),(7,1,'2011-10-25 14:18:58',NULL,'archived'),(8,1,'2011-10-25 14:18:58',NULL,'archived'),(9,1,'2011-10-25 14:18:58',NULL,'archived'),(10,1,'2011-10-25 14:18:44',NULL,'archived'),(10,2,'2011-10-31 10:15:30',NULL,'completed'),(11,1,'2011-10-25 14:18:44',NULL,'archived'),(11,2,'2011-10-31 10:15:30',NULL,'new'),(12,1,'2011-10-25 14:18:44',NULL,'archived'),(12,2,'2011-10-31 10:15:30',NULL,'new'),(13,1,'2011-10-25 14:18:44',NULL,'archived'),(13,2,'2011-10-31 10:15:30',NULL,'new'),(14,1,'2011-10-25 14:18:44',NULL,'archived'),(14,2,'2011-10-31 10:15:30',NULL,'new'),(15,1,'2011-10-25 14:18:44',NULL,'archived'),(16,1,'2011-10-25 14:18:44',NULL,'archived'),(17,1,'2011-10-25 14:18:44',NULL,'archived'),(18,1,'2011-10-25 14:18:44',NULL,'archived'),(19,1,'2011-10-25 14:18:44',NULL,'archived'),(20,1,'2011-10-25 14:18:44',NULL,'archived'),(21,1,'2011-10-25 14:18:44',NULL,'archived'),(22,1,'2011-10-25 14:18:44',NULL,'archived'),(23,1,'2011-10-25 14:18:44',NULL,'archived'),(24,1,'2011-10-25 14:18:44',NULL,'archived'),(25,1,'2011-10-25 14:18:44',NULL,'archived'),(26,1,'2011-10-25 14:18:44',NULL,'archived'),(27,1,'2011-10-25 14:18:44',NULL,'archived'),(28,1,'2011-10-25 14:18:44',NULL,'archived'),(29,1,'2011-10-25 14:18:44',NULL,'archived'),(30,1,'2011-10-25 14:18:44',NULL,'archived'),(31,1,'2011-10-25 14:18:44',NULL,'archived'),(32,1,'2011-10-25 14:18:44',NULL,'archived'),(33,1,'2011-10-25 14:18:44',NULL,'archived'),(34,1,'2011-10-25 14:18:44',NULL,'archived'),(35,1,'2011-10-25 14:18:44',NULL,'archived'),(36,1,'2011-10-25 14:18:44',NULL,'archived'),(37,1,'2011-10-25 14:18:44',NULL,'archived'),(38,1,'2011-10-25 14:18:44',NULL,'archived'),(39,1,'2011-10-25 14:18:44',NULL,'archived'),(40,1,'2011-10-25 14:18:44',NULL,'archived');
/*!40000 ALTER TABLE `survey_user_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `password_salt` varchar(32) DEFAULT NULL,
  `first_name` varchar(64) DEFAULT NULL,
  `last_name` varchar(64) DEFAULT NULL,
  `gender_id` int(10) DEFAULT NULL,
  `ethnicity_id` int(10) DEFAULT NULL,
  `income_range_id` int(10) DEFAULT NULL,
  `birthdate` date DEFAULT NULL COMMENT 'Use this in relation to lookup_age_range',
  `url` varchar(100) DEFAULT NULL,
  `timezone` varchar(16) DEFAULT NULL,
  `primary_email_id` int(10) DEFAULT NULL,
  `primary_phone_id` int(10) DEFAULT NULL,
  `primary_address_id` int(10) DEFAULT NULL,
  `user_role_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_user_role_id` (`user_role_id`),
  KEY `user_gender_id` (`gender_id`),
  KEY `user_ethnicity_id` (`ethnicity_id`),
  KEY `user_income_range_id` (`income_range_id`),
  KEY `user_primary_email_id` (`primary_email_id`),
  KEY `user_primary_phone_id` (`primary_phone_id`),
  KEY `user_primary_address_id` (`primary_address_id`),
  CONSTRAINT `user_ethnicity_id` FOREIGN KEY (`ethnicity_id`) REFERENCES `lookup_ethnicity` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `user_gender_id` FOREIGN KEY (`gender_id`) REFERENCES `lookup_gender` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `user_income_range_id` FOREIGN KEY (`income_range_id`) REFERENCES `lookup_income_range` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `user_primary_address_id` FOREIGN KEY (`primary_address_id`) REFERENCES `user_address` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `user_primary_email_id` FOREIGN KEY (`primary_email_id`) REFERENCES `user_email` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `user_primary_phone_id` FOREIGN KEY (`primary_phone_id`) REFERENCES `user_phone` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `user_user_role_id` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'david','19f9cdef4ed5870c951e72d7401a3ba5','doon','David','James',1,1,4,'1969-11-01','http://www.davidbjames.info','+1:00',1,NULL,NULL,NULL,'2011-10-25 11:26:30','0000-00-00 00:00:00'),(2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,'2011-10-28 11:12:19','2011-11-10 10:21:02'),(3,'alec','ab2159c2d50dbde7a511c1757c75951b','dgus','Alec','Belek',1,1,NULL,'2000-00-00',NULL,'+1:00',3,NULL,NULL,NULL,'2011-10-31 12:36:10','2011-10-31 12:36:10');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_address`
--

DROP TABLE IF EXISTS `user_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_address` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `street1` varchar(100) DEFAULT NULL,
  `street2` varchar(100) DEFAULT NULL,
  `locality` varchar(64) DEFAULT NULL COMMENT 'city',
  `region` varchar(64) DEFAULT NULL COMMENT 'state/province',
  `postalCode` varchar(64) DEFAULT NULL COMMENT 'zip',
  `country` varchar(64) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_address_user_id` (`user_id`),
  CONSTRAINT `user_address_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_address`
--

LOCK TABLES `user_address` WRITE;
/*!40000 ALTER TABLE `user_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_avatar`
--

DROP TABLE IF EXISTS `user_avatar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_avatar` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `data` mediumblob,
  `file_type` enum('jpg','png','gif') DEFAULT 'jpg',
  `file_size` int(6) DEFAULT NULL,
  `width` int(4) DEFAULT NULL,
  `height` int(4) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_avatar_user_id` (`user_id`),
  CONSTRAINT `user_avatar_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_avatar`
--

LOCK TABLES `user_avatar` WRITE;
/*!40000 ALTER TABLE `user_avatar` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_avatar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_child`
--

DROP TABLE IF EXISTS `user_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_child` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_child_user_id` (`user_id`),
  CONSTRAINT `user_child_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_child`
--

LOCK TABLES `user_child` WRITE;
/*!40000 ALTER TABLE `user_child` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_child` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_email`
--

DROP TABLE IF EXISTS `user_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_email` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email_unique` (`user_id`,`email`),
  CONSTRAINT `user_email_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_email`
--

LOCK TABLES `user_email` WRITE;
/*!40000 ALTER TABLE `user_email` DISABLE KEYS */;
INSERT INTO `user_email` VALUES (1,1,'david@saysollc.com','2011-10-25 11:26:30','0000-00-00 00:00:00'),(2,2,'rationalplanet@gmail.com','2011-10-28 11:12:20','2011-11-10 10:21:02'),(3,3,'alec@email.com','2011-10-31 12:36:10','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `user_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_gaming`
--

DROP TABLE IF EXISTS `user_gaming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_gaming` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gaming_id` varchar(64) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `starbar_id` int(10) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_gaming_unique_gaming_id` (`gaming_id`),
  UNIQUE KEY `user_gaming_unique_user` (`user_id`,`starbar_id`),
  KEY `user_gaming_starbar_id` (`starbar_id`),
  CONSTRAINT `user_gaming_starbar_id` FOREIGN KEY (`starbar_id`) REFERENCES `starbar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_gaming_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_gaming`
--

LOCK TABLES `user_gaming` WRITE;
/*!40000 ALTER TABLE `user_gaming` DISABLE KEYS */;
INSERT INTO `user_gaming` VALUES (1,'945ee49e2239c1b8947d2c95fd67439f',1,1,'2011-10-25 14:03:24','0000-00-00 00:00:00'),(2,'1489307c9ead1fd87f17ae9256ab308d',2,1,'2011-10-28 11:12:21','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `user_gaming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_phone`
--

DROP TABLE IF EXISTS `user_phone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_phone` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_phone_user_id` (`user_id`),
  CONSTRAINT `user_phone_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_phone`
--

LOCK TABLES `user_phone` WRITE;
/*!40000 ALTER TABLE `user_phone` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_phone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `id` int(10) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role`
--

LOCK TABLES `user_role` WRITE;
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_social`
--

DROP TABLE IF EXISTS `user_social`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_social` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `provider` enum('facebook','twitter') DEFAULT NULL,
  `identifier` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_social_user_id` (`user_id`),
  CONSTRAINT `user_social_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_social`
--

LOCK TABLES `user_social` WRITE;
/*!40000 ALTER TABLE `user_social` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_social` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sayso'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-11-10 16:00:16
