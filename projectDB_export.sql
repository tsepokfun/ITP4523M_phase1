-- MySQL dump 10.13  Distrib 8.0.46, for macos26.4 (arm64)
--
-- Host: 127.0.0.1    Database: projectDB
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `projectDB`
--

/*!40000 DROP DATABASE IF EXISTS `projectDB`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `projectDB` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;

USE `projectDB`;

--
-- Table structure for table `Customer`
--

DROP TABLE IF EXISTS `Customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Customer`
--

LOCK TABLES `Customer` WRITE;
/*!40000 ALTER TABLE `Customer` DISABLE KEYS */;
INSERT INTO `Customer` VALUES (1,'Alice Chan','alice123','98001111','10 Nathan Road, Kowloon'),(2,'Bob Lee','bob123','97002222','20 Queen Road, HK Island'),(3,'Carol Wong','carol123','96003333','30 Tuen Mun Road, NT'),(4,'David Cheung','david123','95004444','40 Waterloo Road, Kowloon'),(5,'Eva Ho','eva123','94005555','50 King\'s Road, HK Island'),(6,'Frank Lam','frank123','93006666','60 Castle Peak Road, NT'),(7,'Grace Ng','grace123','92007777','70 Canton Road, Kowloon'),(8,'Henry Yip','henry123','91008888','80 Hennessy Road, HK Island'),(9,'Ivy Cheng','ivy123','90009999','90 Sha Tsui Road, NT'),(10,'Jacky Lau','jacky123','68001010','100 Argyle Street, Kowloon');
/*!40000 ALTER TABLE `Customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Furniture`
--

DROP TABLE IF EXISTS `Furniture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Furniture` (
  `furniture_id` int(11) NOT NULL AUTO_INCREMENT,
  `furniture_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`furniture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Furniture`
--

LOCK TABLES `Furniture` WRITE;
/*!40000 ALTER TABLE `Furniture` DISABLE KEYS */;
INSERT INTO `Furniture` VALUES (1,'Oak Dining Table','Solid oak dining table for 6 persons',1200.00,9),(2,'Steel Bookshelf','5-tier steel bookshelf, modern design',450.00,0),(3,'Fabric Sofa','3-seater fabric sofa with foam padding',2500.00,6),(4,'Oak Bed Frame','Queen-size solid oak bed frame',1800.00,5),(5,'Study Chair','Ergonomic study chair with fabric cover',350.00,0),(6,'a point','',2000.00,2),(7,'test','a test image, cute',100.00,18),(10,'test face','23',23.00,1);
/*!40000 ALTER TABLE `Furniture` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Furniture_Material`
--

DROP TABLE IF EXISTS `Furniture_Material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Furniture_Material` (
  `furniture_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `material_quantity` decimal(10,2) NOT NULL,
  PRIMARY KEY (`furniture_id`,`material_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `furniture_material_ibfk_1` FOREIGN KEY (`furniture_id`) REFERENCES `Furniture` (`furniture_id`),
  CONSTRAINT `furniture_material_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `Material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Furniture_Material`
--

LOCK TABLES `Furniture_Material` WRITE;
/*!40000 ALTER TABLE `Furniture_Material` DISABLE KEYS */;
INSERT INTO `Furniture_Material` VALUES (1,1,30.00),(1,5,20.00),(2,2,5.00),(2,5,15.00),(3,3,10.00),(3,4,3.00),(4,1,25.00),(4,5,30.00),(5,3,2.00),(5,4,1.50),(6,3,123.00),(7,1,12.00),(10,3,23.00);
/*!40000 ALTER TABLE `Furniture_Material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Material`
--

DROP TABLE IF EXISTS `Material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Material` (
  `material_id` int(11) NOT NULL AUTO_INCREMENT,
  `material_name` varchar(100) NOT NULL,
  `physical_quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) NOT NULL,
  PRIMARY KEY (`material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Material`
--

LOCK TABLES `Material` WRITE;
/*!40000 ALTER TABLE `Material` DISABLE KEYS */;
INSERT INTO `Material` VALUES (1,'Oak Wood',146,'kg'),(2,'Steel Frame',75,'pcs'),(3,'Foam Padding',-266,'kg'),(4,'Fabric Cover',64,'meter'),(5,'Screws',255,'pcs');
/*!40000 ALTER TABLE `Material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Orders`
--

DROP TABLE IF EXISTS `Orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `furniture_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `order_quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `delivery_date` date NOT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Open',
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `furniture_id` (`furniture_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `Customer` (`customer_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`furniture_id`) REFERENCES `Furniture` (`furniture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Orders`
--

LOCK TABLES `Orders` WRITE;
/*!40000 ALTER TABLE `Orders` DISABLE KEYS */;
INSERT INTO `Orders` VALUES (1,1,1,'2026-06-01',1,1200.00,'10 Nathan Road, Kowloon','2026-06-15','Approved'),(2,2,3,'2026-06-05',2,5000.00,'20 Queen Road, HK Island','2026-06-20','Open'),(3,3,5,'2026-06-10',3,1050.00,'30 Tuen Mun Road, NT','2026-06-25','Open'),(4,1,2,'2026-06-12',1,450.00,'10 Nathan Road, Kowloon','2026-06-28','Rejected'),(5,2,4,'2026-06-15',1,1800.00,'20 Queen Road, HK Island','2026-06-30','Open'),(6,1,5,'2026-06-25',1,350.00,'hbgh','2026-07-02','Open'),(7,1,5,'2026-06-25',19,6650.00,'hg','2026-07-26','Open'),(8,1,3,'2026-06-25',1,2500.00,'jyg','2026-07-02','Approved'),(9,1,7,'2026-06-25',1,100.00,'wq','2026-07-02','Open'),(10,1,7,'2026-06-25',1,100.00,'ive','2026-06-25','Open'),(11,1,1,'2026-06-25',1,1200.00,'we2qew','2026-07-02','Open'),(12,1,3,'2026-06-25',1,2500.00,'qwda','2026-07-02','Open'),(13,1,2,'2026-06-25',15,6750.00,'hhhh','2026-07-02','Open'),(14,1,10,'2026-06-27',20,460.00,'qw','2026-07-04','Open'),(15,1,10,'2026-06-27',1,23.00,'lkm','2026-07-04','Open'),(16,1,10,'2026-06-27',1,23.00,'lkm','2026-07-04','Open');
/*!40000 ALTER TABLE `Orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Staff`
--

DROP TABLE IF EXISTS `Staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Staff` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Staff`
--

LOCK TABLES `Staff` WRITE;
/*!40000 ALTER TABLE `Staff` DISABLE KEYS */;
INSERT INTO `Staff` VALUES (1,'Admin Staff','admin123'),(2,'Manager','manager123');
/*!40000 ALTER TABLE `Staff` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-27 22:59:48
