-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: travian_clone
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alliance_members`
--

DROP TABLE IF EXISTS `alliance_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alliance_members` (
  `alliance_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rank` varchar(50) DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`alliance_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `alliance_members_ibfk_1` FOREIGN KEY (`alliance_id`) REFERENCES `alliances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alliance_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alliance_members`
--

LOCK TABLES `alliance_members` WRITE;
/*!40000 ALTER TABLE `alliance_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `alliance_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alliance_messages`
--

DROP TABLE IF EXISTS `alliance_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alliance_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alliance_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `alliance_id` (`alliance_id`),
  KEY `from_user_id` (`from_user_id`),
  CONSTRAINT `alliance_messages_ibfk_1` FOREIGN KEY (`alliance_id`) REFERENCES `alliances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alliance_messages_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alliance_messages`
--

LOCK TABLES `alliance_messages` WRITE;
/*!40000 ALTER TABLE `alliance_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `alliance_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `alliance_rankings`
--

DROP TABLE IF EXISTS `alliance_rankings`;
/*!50001 DROP VIEW IF EXISTS `alliance_rankings`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `alliance_rankings` (
  `id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `tag` tinyint NOT NULL,
  `total_members` tinyint NOT NULL,
  `total_villages` tinyint NOT NULL,
  `total_population` tinyint NOT NULL,
  `created_at` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `alliances`
--

DROP TABLE IF EXISTS `alliances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alliances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `tag` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `leader_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `tag` (`tag`),
  KEY `leader_id` (`leader_id`),
  CONSTRAINT `alliances_ibfk_1` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alliances`
--

LOCK TABLES `alliances` WRITE;
/*!40000 ALTER TABLE `alliances` DISABLE KEYS */;
/*!40000 ALTER TABLE `alliances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `battles`
--

DROP TABLE IF EXISTS `battles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `battles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movement_id` int(11) DEFAULT NULL,
  `attacker_village_id` int(11) DEFAULT NULL,
  `defender_village_id` int(11) DEFAULT NULL,
  `attacker_units` text DEFAULT NULL,
  `defender_units` text DEFAULT NULL,
  `attacker_losses` text DEFAULT NULL,
  `defender_losses` text DEFAULT NULL,
  `resources_looted` text DEFAULT NULL,
  `battle_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `winner` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movement_id` (`movement_id`),
  KEY `idx_battle_time` (`battle_time`),
  CONSTRAINT `battles_ibfk_1` FOREIGN KEY (`movement_id`) REFERENCES `troop_movements` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `battles`
--

LOCK TABLES `battles` WRITE;
/*!40000 ALTER TABLE `battles` DISABLE KEYS */;
/*!40000 ALTER TABLE `battles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `building_queue`
--

DROP TABLE IF EXISTS `building_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `building_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `village_id` int(11) NOT NULL,
  `building_type` varchar(50) NOT NULL,
  `target_level` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime NOT NULL,
  `wood_cost` int(11) DEFAULT NULL,
  `clay_cost` int(11) DEFAULT NULL,
  `iron_cost` int(11) DEFAULT NULL,
  `crop_cost` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_village` (`village_id`),
  CONSTRAINT `building_queue_ibfk_1` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `building_queue`
--

LOCK TABLES `building_queue` WRITE;
/*!40000 ALTER TABLE `building_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `building_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buildings`
--

DROP TABLE IF EXISTS `buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buildings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `village_id` int(11) NOT NULL,
  `building_type` varchar(50) NOT NULL,
  `level` int(11) DEFAULT 0,
  `position` int(11) DEFAULT NULL,
  `is_upgrading` tinyint(1) DEFAULT 0,
  `upgrade_complete_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_building` (`village_id`,`building_type`),
  KEY `idx_village_building` (`village_id`,`building_type`),
  CONSTRAINT `buildings_ibfk_1` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
INSERT INTO `buildings` VALUES (1,1,'main_building',3,NULL,0,NULL),(2,1,'woodcutter',6,NULL,0,NULL),(3,1,'clay_pit',4,NULL,0,NULL),(4,1,'iron_mine',4,NULL,0,NULL),(5,1,'farm',3,NULL,0,NULL),(6,1,'warehouse',2,NULL,0,NULL),(7,1,'granary',1,NULL,0,NULL),(8,1,'wall',1,NULL,0,NULL),(9,1,'smithy',0,NULL,0,NULL),(10,1,'academy',0,NULL,0,NULL),(11,1,'barracks',1,NULL,0,NULL),(12,1,'stable',0,NULL,0,NULL),(13,1,'marketplace',0,NULL,0,NULL),(14,1,'embassy',0,NULL,0,NULL);
/*!40000 ALTER TABLE `buildings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `map_cells`
--

DROP TABLE IF EXISTS `map_cells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map_cells` (
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `village_id` int(11) DEFAULT NULL,
  `terrain_type` varchar(20) DEFAULT 'plains',
  `oasis_type` varchar(50) DEFAULT NULL,
  `bonus_wood` int(11) DEFAULT 0,
  `bonus_clay` int(11) DEFAULT 0,
  `bonus_iron` int(11) DEFAULT 0,
  `bonus_crop` int(11) DEFAULT 0,
  PRIMARY KEY (`x`,`y`),
  KEY `idx_village` (`village_id`),
  CONSTRAINT `map_cells_ibfk_1` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `map_cells`
--

LOCK TABLES `map_cells` WRITE;
/*!40000 ALTER TABLE `map_cells` DISABLE KEYS */;
INSERT INTO `map_cells` VALUES (-100,-100,1,'plains',NULL,0,0,0,0),(-100,-99,NULL,'plains',NULL,0,0,0,0),(-100,-98,NULL,'plains',NULL,0,0,0,0),(-100,-97,NULL,'plains',NULL,0,0,0,0),(-100,-96,NULL,'plains',NULL,0,0,0,0),(-100,-95,NULL,'plains',NULL,0,0,0,0),(-100,-94,NULL,'plains',NULL,0,0,0,0),(-100,-93,NULL,'plains',NULL,0,0,0,0),(-100,-92,NULL,'plains',NULL,0,0,0,0),(-100,-91,NULL,'plains',NULL,0,0,0,0),(-100,-90,NULL,'plains',NULL,0,0,0,0),(-100,-89,NULL,'plains',NULL,0,0,0,0),(-100,-88,NULL,'plains',NULL,0,0,0,0),(-100,-87,NULL,'plains',NULL,0,0,0,0),(-100,-86,NULL,'plains',NULL,0,0,0,0),(-100,-85,NULL,'plains',NULL,0,0,0,0),(-100,-84,NULL,'plains',NULL,0,0,0,0),(-100,-83,NULL,'plains',NULL,0,0,0,0),(-100,-82,NULL,'plains',NULL,0,0,0,0),(-100,-81,NULL,'plains',NULL,0,0,0,0),(-100,-80,NULL,'plains',NULL,0,0,0,0),(-100,-79,NULL,'plains',NULL,0,0,0,0),(-100,-78,NULL,'plains',NULL,0,0,0,0),(-100,-77,NULL,'plains',NULL,0,0,0,0),(-100,-76,NULL,'plains',NULL,0,0,0,0),(-99,-100,NULL,'plains',NULL,0,0,0,0),(-99,-99,NULL,'plains',NULL,0,0,0,0),(-99,-98,NULL,'plains',NULL,0,0,0,0),(-99,-97,NULL,'plains',NULL,0,0,0,0),(-99,-96,NULL,'plains',NULL,0,0,0,0),(-99,-95,NULL,'plains',NULL,0,0,0,0),(-99,-94,NULL,'plains',NULL,0,0,0,0),(-99,-93,NULL,'plains',NULL,0,0,0,0),(-99,-92,NULL,'plains',NULL,0,0,0,0),(-99,-91,NULL,'plains',NULL,0,0,0,0),(-99,-90,NULL,'plains',NULL,0,0,0,0),(-99,-89,NULL,'plains',NULL,0,0,0,0),(-99,-88,NULL,'plains',NULL,0,0,0,0),(-99,-87,NULL,'plains',NULL,0,0,0,0),(-99,-86,NULL,'plains',NULL,0,0,0,0),(-99,-85,NULL,'plains',NULL,0,0,0,0),(-99,-84,NULL,'plains',NULL,0,0,0,0),(-99,-83,NULL,'plains',NULL,0,0,0,0),(-99,-82,NULL,'plains',NULL,0,0,0,0),(-99,-81,NULL,'plains',NULL,0,0,0,0),(-99,-80,NULL,'plains',NULL,0,0,0,0),(-99,-79,NULL,'plains',NULL,0,0,0,0),(-99,-78,NULL,'plains',NULL,0,0,0,0),(-99,-77,NULL,'plains',NULL,0,0,0,0),(-99,-76,NULL,'plains',NULL,0,0,0,0),(-98,-100,NULL,'plains',NULL,0,0,0,0),(-98,-99,NULL,'plains',NULL,0,0,0,0),(-98,-98,NULL,'plains',NULL,0,0,0,0),(-98,-97,NULL,'plains',NULL,0,0,0,0),(-98,-96,NULL,'plains',NULL,0,0,0,0),(-98,-95,NULL,'plains',NULL,0,0,0,0),(-98,-94,NULL,'plains',NULL,0,0,0,0),(-98,-93,NULL,'plains',NULL,0,0,0,0),(-98,-92,NULL,'plains',NULL,0,0,0,0),(-98,-91,NULL,'plains',NULL,0,0,0,0),(-98,-90,NULL,'plains',NULL,0,0,0,0),(-98,-89,NULL,'plains',NULL,0,0,0,0),(-98,-88,NULL,'plains',NULL,0,0,0,0),(-98,-87,NULL,'plains',NULL,0,0,0,0),(-98,-86,NULL,'plains',NULL,0,0,0,0),(-98,-85,NULL,'plains',NULL,0,0,0,0),(-98,-84,NULL,'plains',NULL,0,0,0,0),(-98,-83,NULL,'plains',NULL,0,0,0,0),(-98,-82,NULL,'plains',NULL,0,0,0,0),(-98,-81,NULL,'plains',NULL,0,0,0,0),(-98,-80,NULL,'plains',NULL,0,0,0,0),(-98,-79,NULL,'plains',NULL,0,0,0,0),(-98,-78,NULL,'plains',NULL,0,0,0,0),(-98,-77,NULL,'plains',NULL,0,0,0,0),(-98,-76,NULL,'plains',NULL,0,0,0,0),(-97,-100,NULL,'plains',NULL,0,0,0,0),(-97,-99,NULL,'plains',NULL,0,0,0,0),(-97,-98,NULL,'plains',NULL,0,0,0,0),(-97,-97,NULL,'plains',NULL,0,0,0,0),(-97,-96,NULL,'plains',NULL,0,0,0,0),(-97,-95,NULL,'plains',NULL,0,0,0,0),(-97,-94,NULL,'plains',NULL,0,0,0,0),(-97,-93,NULL,'plains',NULL,0,0,0,0),(-97,-92,NULL,'plains',NULL,0,0,0,0),(-97,-91,NULL,'plains',NULL,0,0,0,0),(-97,-90,NULL,'plains',NULL,0,0,0,0),(-97,-89,NULL,'plains',NULL,0,0,0,0),(-97,-88,NULL,'plains',NULL,0,0,0,0),(-97,-87,NULL,'plains',NULL,0,0,0,0),(-97,-86,NULL,'plains',NULL,0,0,0,0),(-97,-85,NULL,'plains',NULL,0,0,0,0),(-97,-84,NULL,'plains',NULL,0,0,0,0),(-97,-83,NULL,'plains',NULL,0,0,0,0),(-97,-82,NULL,'plains',NULL,0,0,0,0),(-97,-81,NULL,'plains',NULL,0,0,0,0),(-97,-80,NULL,'plains',NULL,0,0,0,0),(-97,-79,NULL,'plains',NULL,0,0,0,0),(-97,-78,NULL,'plains',NULL,0,0,0,0),(-97,-77,NULL,'plains',NULL,0,0,0,0),(-97,-76,NULL,'plains',NULL,0,0,0,0),(-96,-100,NULL,'plains',NULL,0,0,0,0),(-96,-99,NULL,'plains',NULL,0,0,0,0),(-96,-98,NULL,'plains',NULL,0,0,0,0),(-96,-97,NULL,'plains',NULL,0,0,0,0),(-96,-96,NULL,'plains',NULL,0,0,0,0),(-96,-95,NULL,'plains',NULL,0,0,0,0),(-96,-94,NULL,'plains',NULL,0,0,0,0),(-96,-93,NULL,'plains',NULL,0,0,0,0),(-96,-92,NULL,'plains',NULL,0,0,0,0),(-96,-91,NULL,'plains',NULL,0,0,0,0),(-96,-90,NULL,'plains',NULL,0,0,0,0),(-96,-89,NULL,'plains',NULL,0,0,0,0),(-96,-88,NULL,'plains',NULL,0,0,0,0),(-96,-87,NULL,'plains',NULL,0,0,0,0),(-96,-86,NULL,'plains',NULL,0,0,0,0),(-96,-85,NULL,'plains',NULL,0,0,0,0),(-96,-84,NULL,'plains',NULL,0,0,0,0),(-96,-83,NULL,'plains',NULL,0,0,0,0),(-96,-82,NULL,'plains',NULL,0,0,0,0),(-96,-81,NULL,'plains',NULL,0,0,0,0),(-96,-80,NULL,'plains',NULL,0,0,0,0),(-96,-79,NULL,'plains',NULL,0,0,0,0),(-96,-78,NULL,'plains',NULL,0,0,0,0),(-96,-77,NULL,'plains',NULL,0,0,0,0),(-96,-76,NULL,'plains',NULL,0,0,0,0),(-95,-100,NULL,'plains',NULL,0,0,0,0),(-95,-99,NULL,'plains',NULL,0,0,0,0),(-95,-98,NULL,'plains',NULL,0,0,0,0),(-95,-97,NULL,'plains',NULL,0,0,0,0),(-95,-96,NULL,'plains',NULL,0,0,0,0),(-95,-95,NULL,'plains',NULL,0,0,0,0),(-95,-94,NULL,'plains',NULL,0,0,0,0),(-95,-93,NULL,'plains',NULL,0,0,0,0),(-95,-92,NULL,'plains',NULL,0,0,0,0),(-95,-91,NULL,'plains',NULL,0,0,0,0),(-95,-90,NULL,'plains',NULL,0,0,0,0),(-95,-89,NULL,'plains',NULL,0,0,0,0),(-95,-88,NULL,'plains',NULL,0,0,0,0),(-95,-87,NULL,'plains',NULL,0,0,0,0),(-95,-86,NULL,'plains',NULL,0,0,0,0),(-95,-85,NULL,'plains',NULL,0,0,0,0),(-95,-84,NULL,'plains',NULL,0,0,0,0),(-95,-83,NULL,'plains',NULL,0,0,0,0),(-95,-82,NULL,'plains',NULL,0,0,0,0),(-95,-81,NULL,'plains',NULL,0,0,0,0),(-95,-80,NULL,'plains',NULL,0,0,0,0),(-95,-79,NULL,'plains',NULL,0,0,0,0),(-95,-78,NULL,'plains',NULL,0,0,0,0),(-95,-77,NULL,'plains',NULL,0,0,0,0),(-95,-76,NULL,'plains',NULL,0,0,0,0),(-94,-100,NULL,'plains',NULL,0,0,0,0),(-94,-99,NULL,'plains',NULL,0,0,0,0),(-94,-98,NULL,'plains',NULL,0,0,0,0),(-94,-97,NULL,'plains',NULL,0,0,0,0),(-94,-96,NULL,'plains',NULL,0,0,0,0),(-94,-95,NULL,'plains',NULL,0,0,0,0),(-94,-94,NULL,'plains',NULL,0,0,0,0),(-94,-93,NULL,'plains',NULL,0,0,0,0),(-94,-92,NULL,'plains',NULL,0,0,0,0),(-94,-91,NULL,'plains',NULL,0,0,0,0),(-94,-90,NULL,'plains',NULL,0,0,0,0),(-94,-89,NULL,'plains',NULL,0,0,0,0),(-94,-88,NULL,'plains',NULL,0,0,0,0),(-94,-87,NULL,'plains',NULL,0,0,0,0),(-94,-86,NULL,'plains',NULL,0,0,0,0),(-94,-85,NULL,'plains',NULL,0,0,0,0),(-94,-84,NULL,'plains',NULL,0,0,0,0),(-94,-83,NULL,'plains',NULL,0,0,0,0),(-94,-82,NULL,'plains',NULL,0,0,0,0),(-94,-81,NULL,'plains',NULL,0,0,0,0),(-94,-80,NULL,'plains',NULL,0,0,0,0),(-94,-79,NULL,'plains',NULL,0,0,0,0),(-94,-78,NULL,'plains',NULL,0,0,0,0),(-94,-77,NULL,'plains',NULL,0,0,0,0),(-94,-76,NULL,'plains',NULL,0,0,0,0),(-93,-100,NULL,'plains',NULL,0,0,0,0),(-93,-99,NULL,'plains',NULL,0,0,0,0),(-93,-98,NULL,'plains',NULL,0,0,0,0),(-93,-97,NULL,'plains',NULL,0,0,0,0),(-93,-96,NULL,'plains',NULL,0,0,0,0),(-93,-95,NULL,'plains',NULL,0,0,0,0),(-93,-94,NULL,'plains',NULL,0,0,0,0),(-93,-93,NULL,'plains',NULL,0,0,0,0),(-93,-92,NULL,'plains',NULL,0,0,0,0),(-93,-91,NULL,'plains',NULL,0,0,0,0),(-93,-90,NULL,'plains',NULL,0,0,0,0),(-93,-89,NULL,'plains',NULL,0,0,0,0),(-93,-88,NULL,'plains',NULL,0,0,0,0),(-93,-87,NULL,'plains',NULL,0,0,0,0),(-93,-86,NULL,'plains',NULL,0,0,0,0),(-93,-85,NULL,'plains',NULL,0,0,0,0),(-93,-84,NULL,'plains',NULL,0,0,0,0),(-93,-83,NULL,'plains',NULL,0,0,0,0),(-93,-82,NULL,'plains',NULL,0,0,0,0),(-93,-81,NULL,'plains',NULL,0,0,0,0),(-93,-80,NULL,'plains',NULL,0,0,0,0),(-93,-79,NULL,'plains',NULL,0,0,0,0),(-93,-78,NULL,'plains',NULL,0,0,0,0),(-93,-77,NULL,'plains',NULL,0,0,0,0),(-93,-76,NULL,'plains',NULL,0,0,0,0),(-92,-100,NULL,'plains',NULL,0,0,0,0),(-92,-99,NULL,'plains',NULL,0,0,0,0),(-92,-98,NULL,'plains',NULL,0,0,0,0),(-92,-97,NULL,'plains',NULL,0,0,0,0),(-92,-96,NULL,'plains',NULL,0,0,0,0),(-92,-95,NULL,'plains',NULL,0,0,0,0),(-92,-94,NULL,'plains',NULL,0,0,0,0),(-92,-93,NULL,'plains',NULL,0,0,0,0),(-92,-92,NULL,'plains',NULL,0,0,0,0),(-92,-91,NULL,'plains',NULL,0,0,0,0),(-92,-90,NULL,'plains',NULL,0,0,0,0),(-92,-89,NULL,'plains',NULL,0,0,0,0),(-92,-88,NULL,'plains',NULL,0,0,0,0),(-92,-87,NULL,'plains',NULL,0,0,0,0),(-92,-86,NULL,'plains',NULL,0,0,0,0),(-92,-85,NULL,'plains',NULL,0,0,0,0),(-92,-84,NULL,'plains',NULL,0,0,0,0),(-92,-83,NULL,'plains',NULL,0,0,0,0),(-92,-82,NULL,'plains',NULL,0,0,0,0),(-92,-81,NULL,'plains',NULL,0,0,0,0),(-92,-80,NULL,'plains',NULL,0,0,0,0),(-92,-79,NULL,'plains',NULL,0,0,0,0),(-92,-78,NULL,'plains',NULL,0,0,0,0),(-92,-77,NULL,'plains',NULL,0,0,0,0),(-92,-76,NULL,'plains',NULL,0,0,0,0),(-91,-100,NULL,'plains',NULL,0,0,0,0),(-91,-99,NULL,'plains',NULL,0,0,0,0),(-91,-98,NULL,'plains',NULL,0,0,0,0),(-91,-97,NULL,'plains',NULL,0,0,0,0),(-91,-96,NULL,'plains',NULL,0,0,0,0),(-91,-95,NULL,'plains',NULL,0,0,0,0),(-91,-94,NULL,'plains',NULL,0,0,0,0),(-91,-93,NULL,'plains',NULL,0,0,0,0),(-91,-92,NULL,'plains',NULL,0,0,0,0),(-91,-91,NULL,'plains',NULL,0,0,0,0),(-91,-90,NULL,'plains',NULL,0,0,0,0),(-91,-89,NULL,'plains',NULL,0,0,0,0),(-91,-88,NULL,'plains',NULL,0,0,0,0),(-91,-87,NULL,'plains',NULL,0,0,0,0),(-91,-86,NULL,'plains',NULL,0,0,0,0),(-91,-85,NULL,'plains',NULL,0,0,0,0),(-91,-84,NULL,'plains',NULL,0,0,0,0),(-91,-83,NULL,'plains',NULL,0,0,0,0),(-91,-82,NULL,'plains',NULL,0,0,0,0),(-91,-81,NULL,'plains',NULL,0,0,0,0),(-91,-80,NULL,'plains',NULL,0,0,0,0),(-91,-79,NULL,'plains',NULL,0,0,0,0),(-91,-78,NULL,'plains',NULL,0,0,0,0),(-91,-77,NULL,'plains',NULL,0,0,0,0),(-91,-76,NULL,'plains',NULL,0,0,0,0),(-90,-100,NULL,'plains',NULL,0,0,0,0),(-90,-99,NULL,'plains',NULL,0,0,0,0),(-90,-98,NULL,'plains',NULL,0,0,0,0),(-90,-97,NULL,'plains',NULL,0,0,0,0),(-90,-96,NULL,'plains',NULL,0,0,0,0),(-90,-95,NULL,'plains',NULL,0,0,0,0),(-90,-94,NULL,'plains',NULL,0,0,0,0),(-90,-93,NULL,'plains',NULL,0,0,0,0),(-90,-92,NULL,'plains',NULL,0,0,0,0),(-90,-91,NULL,'plains',NULL,0,0,0,0),(-90,-90,NULL,'plains',NULL,0,0,0,0),(-90,-89,NULL,'plains',NULL,0,0,0,0),(-90,-88,NULL,'plains',NULL,0,0,0,0),(-90,-87,NULL,'plains',NULL,0,0,0,0),(-90,-86,NULL,'plains',NULL,0,0,0,0),(-90,-85,NULL,'plains',NULL,0,0,0,0),(-90,-84,NULL,'plains',NULL,0,0,0,0),(-90,-83,NULL,'plains',NULL,0,0,0,0),(-90,-82,NULL,'plains',NULL,0,0,0,0),(-90,-81,NULL,'plains',NULL,0,0,0,0),(-90,-80,NULL,'plains',NULL,0,0,0,0),(-90,-79,NULL,'plains',NULL,0,0,0,0),(-90,-78,NULL,'plains',NULL,0,0,0,0),(-90,-77,NULL,'plains',NULL,0,0,0,0),(-90,-76,NULL,'plains',NULL,0,0,0,0),(-89,-100,NULL,'plains',NULL,0,0,0,0),(-89,-99,NULL,'plains',NULL,0,0,0,0),(-89,-98,NULL,'plains',NULL,0,0,0,0),(-89,-97,NULL,'plains',NULL,0,0,0,0),(-89,-96,NULL,'plains',NULL,0,0,0,0),(-89,-95,NULL,'plains',NULL,0,0,0,0),(-89,-94,NULL,'plains',NULL,0,0,0,0),(-89,-93,NULL,'plains',NULL,0,0,0,0),(-89,-92,NULL,'plains',NULL,0,0,0,0),(-89,-91,NULL,'plains',NULL,0,0,0,0),(-89,-90,NULL,'plains',NULL,0,0,0,0),(-89,-89,NULL,'plains',NULL,0,0,0,0),(-89,-88,NULL,'plains',NULL,0,0,0,0),(-89,-87,NULL,'plains',NULL,0,0,0,0),(-89,-86,NULL,'plains',NULL,0,0,0,0),(-89,-85,NULL,'plains',NULL,0,0,0,0),(-89,-84,NULL,'plains',NULL,0,0,0,0),(-89,-83,NULL,'plains',NULL,0,0,0,0),(-89,-82,NULL,'plains',NULL,0,0,0,0),(-89,-81,NULL,'plains',NULL,0,0,0,0),(-89,-80,NULL,'plains',NULL,0,0,0,0),(-89,-79,NULL,'plains',NULL,0,0,0,0),(-89,-78,NULL,'plains',NULL,0,0,0,0),(-89,-77,NULL,'plains',NULL,0,0,0,0),(-89,-76,NULL,'plains',NULL,0,0,0,0),(-88,-100,NULL,'plains',NULL,0,0,0,0),(-88,-99,NULL,'plains',NULL,0,0,0,0),(-88,-98,NULL,'plains',NULL,0,0,0,0),(-88,-97,NULL,'plains',NULL,0,0,0,0),(-88,-96,NULL,'plains',NULL,0,0,0,0),(-88,-95,NULL,'plains',NULL,0,0,0,0),(-88,-94,NULL,'plains',NULL,0,0,0,0),(-88,-93,NULL,'plains',NULL,0,0,0,0),(-88,-92,NULL,'plains',NULL,0,0,0,0),(-88,-91,NULL,'plains',NULL,0,0,0,0),(-88,-90,NULL,'plains',NULL,0,0,0,0),(-88,-89,NULL,'plains',NULL,0,0,0,0),(-88,-88,NULL,'plains',NULL,0,0,0,0),(-88,-87,NULL,'plains',NULL,0,0,0,0),(-88,-86,NULL,'plains',NULL,0,0,0,0),(-88,-85,NULL,'plains',NULL,0,0,0,0),(-88,-84,NULL,'plains',NULL,0,0,0,0),(-88,-83,NULL,'plains',NULL,0,0,0,0),(-88,-82,NULL,'plains',NULL,0,0,0,0),(-88,-81,NULL,'plains',NULL,0,0,0,0),(-88,-80,NULL,'plains',NULL,0,0,0,0),(-88,-79,NULL,'plains',NULL,0,0,0,0),(-88,-78,NULL,'plains',NULL,0,0,0,0),(-88,-77,NULL,'plains',NULL,0,0,0,0),(-88,-76,NULL,'plains',NULL,0,0,0,0),(-87,-100,NULL,'plains',NULL,0,0,0,0),(-87,-99,NULL,'plains',NULL,0,0,0,0),(-87,-98,NULL,'plains',NULL,0,0,0,0),(-87,-97,NULL,'plains',NULL,0,0,0,0),(-87,-96,NULL,'plains',NULL,0,0,0,0),(-87,-95,NULL,'plains',NULL,0,0,0,0),(-87,-94,NULL,'plains',NULL,0,0,0,0),(-87,-93,NULL,'plains',NULL,0,0,0,0),(-87,-92,NULL,'plains',NULL,0,0,0,0),(-87,-91,NULL,'plains',NULL,0,0,0,0),(-87,-90,NULL,'plains',NULL,0,0,0,0),(-87,-89,NULL,'plains',NULL,0,0,0,0),(-87,-88,NULL,'plains',NULL,0,0,0,0),(-87,-87,NULL,'plains',NULL,0,0,0,0),(-87,-86,NULL,'plains',NULL,0,0,0,0),(-87,-85,NULL,'plains',NULL,0,0,0,0),(-87,-84,NULL,'plains',NULL,0,0,0,0),(-87,-83,NULL,'plains',NULL,0,0,0,0),(-87,-82,NULL,'plains',NULL,0,0,0,0),(-87,-81,NULL,'plains',NULL,0,0,0,0),(-87,-80,NULL,'plains',NULL,0,0,0,0),(-87,-79,NULL,'plains',NULL,0,0,0,0),(-87,-78,NULL,'plains',NULL,0,0,0,0),(-87,-77,NULL,'plains',NULL,0,0,0,0),(-87,-76,NULL,'plains',NULL,0,0,0,0),(-86,-100,NULL,'plains',NULL,0,0,0,0),(-86,-99,NULL,'plains',NULL,0,0,0,0),(-86,-98,NULL,'plains',NULL,0,0,0,0),(-86,-97,NULL,'plains',NULL,0,0,0,0),(-86,-96,NULL,'plains',NULL,0,0,0,0),(-86,-95,NULL,'plains',NULL,0,0,0,0),(-86,-94,NULL,'plains',NULL,0,0,0,0),(-86,-93,NULL,'plains',NULL,0,0,0,0),(-86,-92,NULL,'plains',NULL,0,0,0,0),(-86,-91,NULL,'plains',NULL,0,0,0,0),(-86,-90,NULL,'plains',NULL,0,0,0,0),(-86,-89,NULL,'plains',NULL,0,0,0,0),(-86,-88,NULL,'plains',NULL,0,0,0,0),(-86,-87,NULL,'plains',NULL,0,0,0,0),(-86,-86,NULL,'plains',NULL,0,0,0,0),(-86,-85,NULL,'plains',NULL,0,0,0,0),(-86,-84,NULL,'plains',NULL,0,0,0,0),(-86,-83,NULL,'plains',NULL,0,0,0,0),(-86,-82,NULL,'plains',NULL,0,0,0,0),(-86,-81,NULL,'plains',NULL,0,0,0,0),(-86,-80,NULL,'plains',NULL,0,0,0,0),(-86,-79,NULL,'plains',NULL,0,0,0,0),(-86,-78,NULL,'plains',NULL,0,0,0,0),(-86,-77,NULL,'plains',NULL,0,0,0,0),(-86,-76,NULL,'plains',NULL,0,0,0,0),(-85,-100,NULL,'plains',NULL,0,0,0,0),(-85,-99,NULL,'plains',NULL,0,0,0,0),(-85,-98,NULL,'plains',NULL,0,0,0,0),(-85,-97,NULL,'plains',NULL,0,0,0,0),(-85,-96,NULL,'plains',NULL,0,0,0,0),(-85,-95,NULL,'plains',NULL,0,0,0,0),(-85,-94,NULL,'plains',NULL,0,0,0,0),(-85,-93,NULL,'plains',NULL,0,0,0,0),(-85,-92,NULL,'plains',NULL,0,0,0,0),(-85,-91,NULL,'plains',NULL,0,0,0,0),(-85,-90,NULL,'plains',NULL,0,0,0,0),(-85,-89,NULL,'plains',NULL,0,0,0,0),(-85,-88,NULL,'plains',NULL,0,0,0,0),(-85,-87,NULL,'plains',NULL,0,0,0,0),(-85,-86,NULL,'plains',NULL,0,0,0,0),(-85,-85,NULL,'plains',NULL,0,0,0,0),(-85,-84,NULL,'plains',NULL,0,0,0,0),(-85,-83,NULL,'plains',NULL,0,0,0,0),(-85,-82,NULL,'plains',NULL,0,0,0,0),(-85,-81,NULL,'plains',NULL,0,0,0,0),(-85,-80,NULL,'plains',NULL,0,0,0,0),(-85,-79,NULL,'plains',NULL,0,0,0,0),(-85,-78,NULL,'plains',NULL,0,0,0,0),(-85,-77,NULL,'plains',NULL,0,0,0,0),(-85,-76,NULL,'plains',NULL,0,0,0,0),(-84,-100,NULL,'plains',NULL,0,0,0,0),(-84,-99,NULL,'plains',NULL,0,0,0,0),(-84,-98,NULL,'plains',NULL,0,0,0,0),(-84,-97,NULL,'plains',NULL,0,0,0,0),(-84,-96,NULL,'plains',NULL,0,0,0,0),(-84,-95,NULL,'plains',NULL,0,0,0,0),(-84,-94,NULL,'plains',NULL,0,0,0,0),(-84,-93,NULL,'plains',NULL,0,0,0,0),(-84,-92,NULL,'plains',NULL,0,0,0,0),(-84,-91,NULL,'plains',NULL,0,0,0,0),(-84,-90,NULL,'plains',NULL,0,0,0,0),(-84,-89,NULL,'plains',NULL,0,0,0,0),(-84,-88,NULL,'plains',NULL,0,0,0,0),(-84,-87,NULL,'plains',NULL,0,0,0,0),(-84,-86,NULL,'plains',NULL,0,0,0,0),(-84,-85,NULL,'plains',NULL,0,0,0,0),(-84,-84,NULL,'plains',NULL,0,0,0,0),(-84,-83,NULL,'plains',NULL,0,0,0,0),(-84,-82,NULL,'plains',NULL,0,0,0,0),(-84,-81,NULL,'plains',NULL,0,0,0,0),(-84,-80,NULL,'plains',NULL,0,0,0,0),(-84,-79,NULL,'plains',NULL,0,0,0,0),(-84,-78,NULL,'plains',NULL,0,0,0,0),(-84,-77,NULL,'plains',NULL,0,0,0,0),(-84,-76,NULL,'plains',NULL,0,0,0,0),(-83,-100,NULL,'plains',NULL,0,0,0,0),(-83,-99,NULL,'plains',NULL,0,0,0,0),(-83,-98,NULL,'plains',NULL,0,0,0,0),(-83,-97,NULL,'plains',NULL,0,0,0,0),(-83,-96,NULL,'plains',NULL,0,0,0,0),(-83,-95,NULL,'plains',NULL,0,0,0,0),(-83,-94,NULL,'plains',NULL,0,0,0,0),(-83,-93,NULL,'plains',NULL,0,0,0,0),(-83,-92,NULL,'plains',NULL,0,0,0,0),(-83,-91,NULL,'plains',NULL,0,0,0,0),(-83,-90,NULL,'plains',NULL,0,0,0,0),(-83,-89,NULL,'plains',NULL,0,0,0,0),(-83,-88,NULL,'plains',NULL,0,0,0,0),(-83,-87,NULL,'plains',NULL,0,0,0,0),(-83,-86,NULL,'plains',NULL,0,0,0,0),(-83,-85,NULL,'plains',NULL,0,0,0,0),(-83,-84,NULL,'plains',NULL,0,0,0,0),(-83,-83,NULL,'plains',NULL,0,0,0,0),(-83,-82,NULL,'plains',NULL,0,0,0,0),(-83,-81,NULL,'plains',NULL,0,0,0,0),(-83,-80,NULL,'plains',NULL,0,0,0,0),(-83,-79,NULL,'plains',NULL,0,0,0,0),(-83,-78,NULL,'plains',NULL,0,0,0,0),(-83,-77,NULL,'plains',NULL,0,0,0,0),(-83,-76,NULL,'plains',NULL,0,0,0,0),(-82,-100,NULL,'plains',NULL,0,0,0,0),(-82,-99,NULL,'plains',NULL,0,0,0,0),(-82,-98,NULL,'plains',NULL,0,0,0,0),(-82,-97,NULL,'plains',NULL,0,0,0,0),(-82,-96,NULL,'plains',NULL,0,0,0,0),(-82,-95,NULL,'plains',NULL,0,0,0,0),(-82,-94,NULL,'plains',NULL,0,0,0,0),(-82,-93,NULL,'plains',NULL,0,0,0,0),(-82,-92,NULL,'plains',NULL,0,0,0,0),(-82,-91,NULL,'plains',NULL,0,0,0,0),(-82,-90,NULL,'plains',NULL,0,0,0,0),(-82,-89,NULL,'plains',NULL,0,0,0,0),(-82,-88,NULL,'plains',NULL,0,0,0,0),(-82,-87,NULL,'plains',NULL,0,0,0,0),(-82,-86,NULL,'plains',NULL,0,0,0,0),(-82,-85,NULL,'plains',NULL,0,0,0,0),(-82,-84,NULL,'plains',NULL,0,0,0,0),(-82,-83,NULL,'plains',NULL,0,0,0,0),(-82,-82,NULL,'plains',NULL,0,0,0,0),(-82,-81,NULL,'plains',NULL,0,0,0,0),(-82,-80,NULL,'plains',NULL,0,0,0,0),(-82,-79,NULL,'plains',NULL,0,0,0,0),(-82,-78,NULL,'plains',NULL,0,0,0,0),(-82,-77,NULL,'plains',NULL,0,0,0,0),(-82,-76,NULL,'plains',NULL,0,0,0,0),(-81,-100,NULL,'plains',NULL,0,0,0,0),(-81,-99,NULL,'plains',NULL,0,0,0,0),(-81,-98,NULL,'plains',NULL,0,0,0,0),(-81,-97,NULL,'plains',NULL,0,0,0,0),(-81,-96,NULL,'plains',NULL,0,0,0,0),(-81,-95,NULL,'plains',NULL,0,0,0,0),(-81,-94,NULL,'plains',NULL,0,0,0,0),(-81,-93,NULL,'plains',NULL,0,0,0,0),(-81,-92,NULL,'plains',NULL,0,0,0,0),(-81,-91,NULL,'plains',NULL,0,0,0,0),(-81,-90,NULL,'plains',NULL,0,0,0,0),(-81,-89,NULL,'plains',NULL,0,0,0,0),(-81,-88,NULL,'plains',NULL,0,0,0,0),(-81,-87,NULL,'plains',NULL,0,0,0,0),(-81,-86,NULL,'plains',NULL,0,0,0,0),(-81,-85,NULL,'plains',NULL,0,0,0,0),(-81,-84,NULL,'plains',NULL,0,0,0,0),(-81,-83,NULL,'plains',NULL,0,0,0,0),(-81,-82,NULL,'plains',NULL,0,0,0,0),(-81,-81,NULL,'plains',NULL,0,0,0,0),(-81,-80,NULL,'plains',NULL,0,0,0,0),(-81,-79,NULL,'plains',NULL,0,0,0,0),(-81,-78,NULL,'plains',NULL,0,0,0,0),(-81,-77,NULL,'plains',NULL,0,0,0,0),(-81,-76,NULL,'plains',NULL,0,0,0,0),(-80,-100,NULL,'plains',NULL,0,0,0,0),(-80,-99,NULL,'plains',NULL,0,0,0,0),(-80,-98,NULL,'plains',NULL,0,0,0,0),(-80,-97,NULL,'plains',NULL,0,0,0,0),(-80,-96,NULL,'plains',NULL,0,0,0,0),(-80,-95,NULL,'plains',NULL,0,0,0,0),(-80,-94,NULL,'plains',NULL,0,0,0,0),(-80,-93,NULL,'plains',NULL,0,0,0,0),(-80,-92,NULL,'plains',NULL,0,0,0,0),(-80,-91,NULL,'plains',NULL,0,0,0,0),(-80,-90,NULL,'plains',NULL,0,0,0,0),(-80,-89,NULL,'plains',NULL,0,0,0,0),(-80,-88,NULL,'plains',NULL,0,0,0,0),(-80,-87,NULL,'plains',NULL,0,0,0,0),(-80,-86,NULL,'plains',NULL,0,0,0,0),(-80,-85,NULL,'plains',NULL,0,0,0,0),(-80,-84,NULL,'plains',NULL,0,0,0,0),(-80,-83,NULL,'plains',NULL,0,0,0,0),(-80,-82,NULL,'plains',NULL,0,0,0,0),(-80,-81,NULL,'plains',NULL,0,0,0,0),(-80,-80,NULL,'plains',NULL,0,0,0,0),(-80,-79,NULL,'plains',NULL,0,0,0,0),(-80,-78,NULL,'plains',NULL,0,0,0,0),(-80,-77,NULL,'plains',NULL,0,0,0,0),(-80,-76,NULL,'plains',NULL,0,0,0,0),(-79,-100,NULL,'plains',NULL,0,0,0,0),(-79,-99,NULL,'plains',NULL,0,0,0,0),(-79,-98,NULL,'plains',NULL,0,0,0,0),(-79,-97,NULL,'plains',NULL,0,0,0,0),(-79,-96,NULL,'plains',NULL,0,0,0,0),(-79,-95,NULL,'plains',NULL,0,0,0,0),(-79,-94,NULL,'plains',NULL,0,0,0,0),(-79,-93,NULL,'plains',NULL,0,0,0,0),(-79,-92,NULL,'plains',NULL,0,0,0,0),(-79,-91,NULL,'plains',NULL,0,0,0,0),(-79,-90,NULL,'plains',NULL,0,0,0,0),(-79,-89,NULL,'plains',NULL,0,0,0,0),(-79,-88,NULL,'plains',NULL,0,0,0,0),(-79,-87,NULL,'plains',NULL,0,0,0,0),(-79,-86,NULL,'plains',NULL,0,0,0,0),(-79,-85,NULL,'plains',NULL,0,0,0,0),(-79,-84,NULL,'plains',NULL,0,0,0,0),(-79,-83,NULL,'plains',NULL,0,0,0,0),(-79,-82,NULL,'plains',NULL,0,0,0,0),(-79,-81,NULL,'plains',NULL,0,0,0,0),(-79,-80,NULL,'plains',NULL,0,0,0,0),(-79,-79,NULL,'plains',NULL,0,0,0,0),(-79,-78,NULL,'plains',NULL,0,0,0,0),(-79,-77,NULL,'plains',NULL,0,0,0,0),(-79,-76,NULL,'plains',NULL,0,0,0,0),(-78,-100,NULL,'plains',NULL,0,0,0,0),(-78,-99,NULL,'plains',NULL,0,0,0,0),(-78,-98,NULL,'plains',NULL,0,0,0,0),(-78,-97,NULL,'plains',NULL,0,0,0,0),(-78,-96,NULL,'plains',NULL,0,0,0,0),(-78,-95,NULL,'plains',NULL,0,0,0,0),(-78,-94,NULL,'plains',NULL,0,0,0,0),(-78,-93,NULL,'plains',NULL,0,0,0,0),(-78,-92,NULL,'plains',NULL,0,0,0,0),(-78,-91,NULL,'plains',NULL,0,0,0,0),(-78,-90,NULL,'plains',NULL,0,0,0,0),(-78,-89,NULL,'plains',NULL,0,0,0,0),(-78,-88,NULL,'plains',NULL,0,0,0,0),(-78,-87,NULL,'plains',NULL,0,0,0,0),(-78,-86,NULL,'plains',NULL,0,0,0,0),(-78,-85,NULL,'plains',NULL,0,0,0,0),(-78,-84,NULL,'plains',NULL,0,0,0,0),(-78,-83,NULL,'plains',NULL,0,0,0,0),(-78,-82,NULL,'plains',NULL,0,0,0,0),(-78,-81,NULL,'plains',NULL,0,0,0,0),(-78,-80,NULL,'plains',NULL,0,0,0,0),(-78,-79,NULL,'plains',NULL,0,0,0,0),(-78,-78,NULL,'plains',NULL,0,0,0,0),(-78,-77,NULL,'plains',NULL,0,0,0,0),(-78,-76,NULL,'plains',NULL,0,0,0,0),(-77,-100,NULL,'plains',NULL,0,0,0,0),(-77,-99,NULL,'plains',NULL,0,0,0,0),(-77,-98,NULL,'plains',NULL,0,0,0,0),(-77,-97,NULL,'plains',NULL,0,0,0,0),(-77,-96,NULL,'plains',NULL,0,0,0,0),(-77,-95,NULL,'plains',NULL,0,0,0,0),(-77,-94,NULL,'plains',NULL,0,0,0,0),(-77,-93,NULL,'plains',NULL,0,0,0,0),(-77,-92,NULL,'plains',NULL,0,0,0,0),(-77,-91,NULL,'plains',NULL,0,0,0,0),(-77,-90,NULL,'plains',NULL,0,0,0,0),(-77,-89,NULL,'plains',NULL,0,0,0,0),(-77,-88,NULL,'plains',NULL,0,0,0,0),(-77,-87,NULL,'plains',NULL,0,0,0,0),(-77,-86,NULL,'plains',NULL,0,0,0,0),(-77,-85,NULL,'plains',NULL,0,0,0,0),(-77,-84,NULL,'plains',NULL,0,0,0,0),(-77,-83,NULL,'plains',NULL,0,0,0,0),(-77,-82,NULL,'plains',NULL,0,0,0,0),(-77,-81,NULL,'plains',NULL,0,0,0,0),(-77,-80,NULL,'plains',NULL,0,0,0,0),(-77,-79,NULL,'plains',NULL,0,0,0,0),(-77,-78,NULL,'plains',NULL,0,0,0,0),(-77,-77,NULL,'plains',NULL,0,0,0,0),(-77,-76,NULL,'plains',NULL,0,0,0,0),(-76,-100,NULL,'plains',NULL,0,0,0,0),(-76,-99,NULL,'plains',NULL,0,0,0,0),(-76,-98,NULL,'plains',NULL,0,0,0,0),(-76,-97,NULL,'plains',NULL,0,0,0,0),(-76,-96,NULL,'plains',NULL,0,0,0,0),(-76,-95,NULL,'plains',NULL,0,0,0,0),(-76,-94,NULL,'plains',NULL,0,0,0,0),(-76,-93,NULL,'plains',NULL,0,0,0,0),(-76,-92,NULL,'plains',NULL,0,0,0,0),(-76,-91,NULL,'plains',NULL,0,0,0,0),(-76,-90,NULL,'plains',NULL,0,0,0,0),(-76,-89,NULL,'plains',NULL,0,0,0,0),(-76,-88,NULL,'plains',NULL,0,0,0,0),(-76,-87,NULL,'plains',NULL,0,0,0,0),(-76,-86,NULL,'plains',NULL,0,0,0,0),(-76,-85,NULL,'plains',NULL,0,0,0,0),(-76,-84,NULL,'plains',NULL,0,0,0,0),(-76,-83,NULL,'plains',NULL,0,0,0,0),(-76,-82,NULL,'plains',NULL,0,0,0,0),(-76,-81,NULL,'plains',NULL,0,0,0,0),(-76,-80,NULL,'plains',NULL,0,0,0,0),(-76,-79,NULL,'plains',NULL,0,0,0,0),(-76,-78,NULL,'plains',NULL,0,0,0,0),(-76,-77,NULL,'plains',NULL,0,0,0,0),(-76,-76,NULL,'plains',NULL,0,0,0,0);
/*!40000 ALTER TABLE `map_cells` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_user_id` (`from_user_id`),
  KEY `idx_to_user` (`to_user_id`,`is_read`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `military_rankings`
--

DROP TABLE IF EXISTS `military_rankings`;
/*!50001 DROP VIEW IF EXISTS `military_rankings`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `military_rankings` (
  `id` tinyint NOT NULL,
  `username` tinyint NOT NULL,
  `tribe` tinyint NOT NULL,
  `total_troops` tinyint NOT NULL,
  `infantry` tinyint NOT NULL,
  `cavalry` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `player_rankings`
--

DROP TABLE IF EXISTS `player_rankings`;
/*!50001 DROP VIEW IF EXISTS `player_rankings`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `player_rankings` (
  `id` tinyint NOT NULL,
  `username` tinyint NOT NULL,
  `tribe` tinyint NOT NULL,
  `total_villages` tinyint NOT NULL,
  `total_population` tinyint NOT NULL,
  `avg_loyalty` tinyint NOT NULL,
  `total_attacks` tinyint NOT NULL,
  `total_wins` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `player_stats`
--

DROP TABLE IF EXISTS `player_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_stats` (
  `user_id` int(11) NOT NULL,
  `total_population` int(11) DEFAULT 0,
  `total_villages` int(11) DEFAULT 1,
  `total_attacks` int(11) DEFAULT 0,
  `total_wins` int(11) DEFAULT 0,
  `total_losses` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `player_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_stats`
--

LOCK TABLES `player_stats` WRITE;
/*!40000 ALTER TABLE `player_stats` DISABLE KEYS */;
INSERT INTO `player_stats` VALUES (1,43,1,0,0,0,'2026-03-30 22:33:01');
/*!40000 ALTER TABLE `player_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quests`
--

DROP TABLE IF EXISTS `quests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quest_key` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `quest_type` enum('building','troop','resource','attack') NOT NULL,
  `required_building_type` varchar(50) DEFAULT NULL,
  `required_building_level` int(11) DEFAULT NULL,
  `required_troop_type` varchar(50) DEFAULT NULL,
  `required_troop_count` int(11) DEFAULT NULL,
  `reward_wood` int(11) DEFAULT 0,
  `reward_clay` int(11) DEFAULT 0,
  `reward_iron` int(11) DEFAULT 0,
  `reward_crop` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quest_key` (`quest_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quests`
--

LOCK TABLES `quests` WRITE;
/*!40000 ALTER TABLE `quests` DISABLE KEYS */;
INSERT INTO `quests` VALUES (1,'build_main_1','Build Main Building','Construct your Main Building','building','main_building',1,NULL,NULL,100,100,100,50),(2,'build_woodcutter_1','Build Woodcutter','Build a Woodcutter to produce wood','building','woodcutter',1,NULL,NULL,150,50,50,50),(3,'train_troops_1','Train Your First Troops','Train 10 soldiers','troop',NULL,NULL,NULL,NULL,200,200,200,100);
/*!40000 ALTER TABLE `quests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources` (
  `village_id` int(11) NOT NULL,
  `wood` decimal(12,2) DEFAULT 500.00,
  `clay` decimal(12,2) DEFAULT 500.00,
  `iron` decimal(12,2) DEFAULT 500.00,
  `crop` decimal(12,2) DEFAULT 500.00,
  `wood_production` int(11) DEFAULT 10,
  `clay_production` int(11) DEFAULT 10,
  `iron_production` int(11) DEFAULT 10,
  `crop_production` int(11) DEFAULT 5,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`village_id`),
  CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources`
--

LOCK TABLES `resources` WRITE;
/*!40000 ALTER TABLE `resources` DISABLE KEYS */;
INSERT INTO `resources` VALUES (1,4435.19,4263.66,4395.66,4803.66,40,30,30,20,'2026-03-31 05:10:01');
/*!40000 ALTER TABLE `resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `server_settings`
--

DROP TABLE IF EXISTS `server_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `server_settings`
--

LOCK TABLES `server_settings` WRITE;
/*!40000 ALTER TABLE `server_settings` DISABLE KEYS */;
INSERT INTO `server_settings` VALUES ('beginner_protection_days','7','2026-03-30 05:30:13'),('game_start_date','2026-03-30 07:30:13','2026-03-30 05:30:13'),('server_name','Travian Classic','2026-03-30 05:30:13'),('server_speed','1','2026-03-30 05:30:13');
/*!40000 ALTER TABLE `server_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_queue`
--

DROP TABLE IF EXISTS `training_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `training_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `village_id` int(11) NOT NULL,
  `unit_type` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime NOT NULL,
  `processed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_processed` (`processed`),
  KEY `idx_village` (`village_id`),
  CONSTRAINT `training_queue_ibfk_1` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_queue`
--

LOCK TABLES `training_queue` WRITE;
/*!40000 ALTER TABLE `training_queue` DISABLE KEYS */;
INSERT INTO `training_queue` VALUES (1,1,'legionnaire',1,'2026-03-30 06:10:53','2026-03-30 08:40:53',1),(2,1,'praetorian',1,'2026-03-30 22:01:45','2026-03-31 00:36:45',1);
/*!40000 ALTER TABLE `training_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `troop_movements`
--

DROP TABLE IF EXISTS `troop_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `troop_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movement_type` enum('attack','support','return','raid') NOT NULL,
  `from_village_id` int(11) NOT NULL,
  `to_village_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `arrival_time` datetime NOT NULL,
  `return_time` datetime DEFAULT NULL,
  `units` text NOT NULL,
  `resources_carried` text DEFAULT NULL,
  `travel_time` int(11) DEFAULT 0,
  `processed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `from_village_id` (`from_village_id`),
  KEY `to_village_id` (`to_village_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_arrival_time` (`arrival_time`),
  KEY `idx_processed` (`processed`),
  CONSTRAINT `troop_movements_ibfk_1` FOREIGN KEY (`from_village_id`) REFERENCES `villages` (`id`),
  CONSTRAINT `troop_movements_ibfk_2` FOREIGN KEY (`to_village_id`) REFERENCES `villages` (`id`),
  CONSTRAINT `troop_movements_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `troop_movements`
--

LOCK TABLES `troop_movements` WRITE;
/*!40000 ALTER TABLE `troop_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `troop_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `troops`
--

DROP TABLE IF EXISTS `troops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `troops` (
  `village_id` int(11) NOT NULL,
  `unit_type` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  PRIMARY KEY (`village_id`,`unit_type`),
  KEY `idx_village` (`village_id`),
  CONSTRAINT `troops_ibfk_1` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `troops`
--

LOCK TABLES `troops` WRITE;
/*!40000 ALTER TABLE `troops` DISABLE KEYS */;
INSERT INTO `troops` VALUES (1,'club_swinger',0),(1,'legionnaire',1),(1,'phalanx',0),(1,'praetorian',1),(1,'settler',0),(1,'spearman',0),(1,'swordsman',0);
/*!40000 ALTER TABLE `troops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_quests`
--

DROP TABLE IF EXISTS `user_quests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_quests` (
  `user_id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`quest_id`),
  KEY `quest_id` (`quest_id`),
  CONSTRAINT `user_quests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_quests_ibfk_2` FOREIGN KEY (`quest_id`) REFERENCES `quests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_quests`
--

LOCK TABLES `user_quests` WRITE;
/*!40000 ALTER TABLE `user_quests` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_quests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tribe` enum('roman','teuton','gaul') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `beginner_protection_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_banned` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Monuta','$2y$10$L/WyTuPHZZF4WTfrrxZWf.Jw6iyIBKIiHfZXC4xDiE9XwNqFDr/6O','hshjs@hhokof.nl','roman','2026-03-30 05:41:43','2026-03-30 07:41:51','2026-04-06 07:41:43',1,0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER create_player_stats_after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO player_stats (user_id, total_population, total_villages)
    VALUES (NEW.id, 10, 1);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `villages`
--

DROP TABLE IF EXISTS `villages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `villages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `population` int(11) DEFAULT 10,
  `loyalty` int(11) DEFAULT 100,
  `is_capital` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_coordinates` (`x`,`y`),
  KEY `idx_coordinates` (`x`,`y`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `villages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `villages`
--

LOCK TABLES `villages` WRITE;
/*!40000 ALTER TABLE `villages` DISABLE KEYS */;
INSERT INTO `villages` VALUES (1,1,'Monuta\'s Village',-100,-100,43,100,0,'2026-03-30 05:41:43');
/*!40000 ALTER TABLE `villages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `alliance_rankings`
--

/*!50001 DROP TABLE IF EXISTS `alliance_rankings`*/;
/*!50001 DROP VIEW IF EXISTS `alliance_rankings`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `alliance_rankings` AS select `a`.`id` AS `id`,`a`.`name` AS `name`,`a`.`tag` AS `tag`,count(distinct `am`.`user_id`) AS `total_members`,count(`v`.`id`) AS `total_villages`,sum(`v`.`population`) AS `total_population`,`a`.`created_at` AS `created_at` from (((`alliances` `a` left join `alliance_members` `am` on(`a`.`id` = `am`.`alliance_id`)) left join `users` `u` on(`am`.`user_id` = `u`.`id`)) left join `villages` `v` on(`u`.`id` = `v`.`user_id`)) where `u`.`is_active` = 1 and `u`.`is_banned` = 0 group by `a`.`id`,`a`.`name`,`a`.`tag`,`a`.`created_at` order by sum(`v`.`population`) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `military_rankings`
--

/*!50001 DROP TABLE IF EXISTS `military_rankings`*/;
/*!50001 DROP VIEW IF EXISTS `military_rankings`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `military_rankings` AS select `u`.`id` AS `id`,`u`.`username` AS `username`,`u`.`tribe` AS `tribe`,sum(`t`.`quantity`) AS `total_troops`,sum(case when `t`.`unit_type` in ('legionnaire','praetorian','club_swinger','spearman','phalanx','swordsman') then `t`.`quantity` else 0 end) AS `infantry`,sum(case when `t`.`unit_type` in ('stable','paladin','haeduan','equites_legati') then `t`.`quantity` else 0 end) AS `cavalry` from ((`users` `u` left join `villages` `v` on(`u`.`id` = `v`.`user_id`)) left join `troops` `t` on(`v`.`id` = `t`.`village_id`)) where `u`.`is_active` = 1 and `u`.`is_banned` = 0 group by `u`.`id`,`u`.`username`,`u`.`tribe` order by sum(`t`.`quantity`) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `player_rankings`
--

/*!50001 DROP TABLE IF EXISTS `player_rankings`*/;
/*!50001 DROP VIEW IF EXISTS `player_rankings`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `player_rankings` AS select `u`.`id` AS `id`,`u`.`username` AS `username`,`u`.`tribe` AS `tribe`,count(`v`.`id`) AS `total_villages`,sum(`v`.`population`) AS `total_population`,sum(`v`.`loyalty`) / count(`v`.`id`) AS `avg_loyalty`,(select count(0) from `troop_movements` where `troop_movements`.`user_id` = `u`.`id` and `troop_movements`.`movement_type` = 'attack') AS `total_attacks`,(select count(0) from `battles` where `battles`.`winner` = 'attacker' and `battles`.`attacker_village_id` in (select `villages`.`id` from `villages` where `villages`.`user_id` = `u`.`id`)) AS `total_wins` from (`users` `u` left join `villages` `v` on(`u`.`id` = `v`.`user_id`)) where `u`.`is_active` = 1 and `u`.`is_banned` = 0 group by `u`.`id`,`u`.`username`,`u`.`tribe` order by sum(`v`.`population`) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-31  7:10:09
