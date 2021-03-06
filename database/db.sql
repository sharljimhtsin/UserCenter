-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: rm-uf6y34nm9525fyf01o.mysql.rds.aliyuncs.com    Database: user_center
-- ------------------------------------------------------
-- Server version	5.7.15-log

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup
--

SET @@GLOBAL.GTID_PURGED='ea3e2271-66a8-11e7-bed5-00163e1c4676:1-1162275';

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account` (
  `account_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_key` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `account_type` tinyint(5) DEFAULT NULL,
  `union_user_id` bigint(20) DEFAULT NULL,
  `status` tinyint(5) DEFAULT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `channel`
--

DROP TABLE IF EXISTS `channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channel` (
  `channel_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(45) DEFAULT NULL,
  `channel_key` varchar(45) DEFAULT NULL,
  `channel_secret` varchar(45) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `pay_callback_url` varchar(100) DEFAULT NULL,
  `is_test` tinyint(5) DEFAULT NULL,
  `owner` bigint(20) DEFAULT NULL,
  `modify_time` datetime DEFAULT NULL,
  `alias` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`channel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mapping`
--

DROP TABLE IF EXISTS `mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mapping` (
  `mapping_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `channel_id` bigint(20) DEFAULT NULL,
  `channel_uid` varchar(45) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `modify_time` datetime DEFAULT NULL,
  PRIMARY KEY (`mapping_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pay_order`
--

DROP TABLE IF EXISTS `pay_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_order` (
  `order_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `channel_id` bigint(20) NOT NULL,
  `channel_order_id` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `money` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `role_id` varchar(255) DEFAULT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `server_id` varchar(255) DEFAULT NULL,
  `server_name` varchar(255) DEFAULT NULL,
  `product_id` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_desc` varchar(255) DEFAULT NULL,
  `notify_url` varchar(2048) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `modify_time` datetime DEFAULT NULL,
  `complete_time` datetime DEFAULT NULL,
  `order_no` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms_code`
--

DROP TABLE IF EXISTS `sms_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_code` (
  `telephone` bigint(20) NOT NULL,
  `code` int(11) DEFAULT NULL,
  `expire_time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`telephone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `token`
--

DROP TABLE IF EXISTS `token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `token` (
  `user_id` bigint(20) NOT NULL,
  `token` varchar(45) DEFAULT NULL,
  `expire_time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` bigint(20) NOT NULL,
  `telephone` varchar(45) DEFAULT NULL,
  `nickname` varchar(45) DEFAULT NULL,
  `avatar` varchar(45) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `sex` tinyint(5) DEFAULT NULL,
  `signature` varchar(45) DEFAULT NULL,
  `user_source` varchar(45) DEFAULT NULL,
  `role` tinyint(5) DEFAULT NULL,
  `status` tinyint(5) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `modify_time` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `variable`
--

DROP TABLE IF EXISTS `variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variable` (
  `variable_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `modify_time` datetime DEFAULT NULL,
  PRIMARY KEY (`variable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-12-08 12:53:12
