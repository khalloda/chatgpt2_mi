/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.4-MariaDB, for Win64 (AMD64)
--
-- Host: p3nlmysql13plsk.secureserver.net    Database: chatgpt2_mi
-- ------------------------------------------------------
-- Server version	8.0.37-29

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `actor` varchar(191) NOT NULL,
  `action` varchar(64) NOT NULL,
  `entity_type` varchar(64) NOT NULL,
  `entity_id` int unsigned NOT NULL,
  `meta` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES
(1,'admin@example.com','ap.add','purchase_invoice',1,'{\"payment_id\":1,\"amount\":650,\"method\":\"bank\",\"reference\":\"TR56775\"}','2025-08-31 08:10:00'),
(2,'admin@example.com','ap.add','purchase_invoice',1,'{\"payment_id\":2,\"amount\":1000,\"method\":\"bank\",\"reference\":\"TR89885\"}','2025-08-31 08:13:06');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  KEY `idx_categories_parent` (`parent_id`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,NULL,'Body Parts','body-parts','2025-08-29 15:28:25',NULL),
(2,NULL,'Paints','paints','2025-08-29 15:55:21',NULL),
(3,NULL,'Electronics','electronics','2025-08-29 15:55:38',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cogs_entries`
--

DROP TABLE IF EXISTS `cogs_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cogs_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int NOT NULL,
  `unit_cost` decimal(12,4) NOT NULL,
  `line_cost` decimal(14,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `product_id` (`product_id`),
  KEY `warehouse_id` (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cogs_entries`
--

LOCK TABLES `cogs_entries` WRITE;
/*!40000 ALTER TABLE `cogs_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `cogs_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(1,'Khaled Mohamed Helmy','01007847333','khelmy@sarieldin.com','KM 28 Cairo Alex Desert Road B 19 - Smart Village\r\nSarieldin & Partners','2025-08-29 15:41:56',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doc_sequences`
--

DROP TABLE IF EXISTS `doc_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc_sequences` (
  `prefix` varchar(10) NOT NULL,
  `y` int NOT NULL,
  `last_no` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`prefix`,`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_sequences`
--

LOCK TABLES `doc_sequences` WRITE;
/*!40000 ALTER TABLE `doc_sequences` DISABLE KEYS */;
INSERT INTO `doc_sequences` VALUES
('po',2025,19),
('q',2025,9);
/*!40000 ALTER TABLE `doc_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_ledger`
--

DROP TABLE IF EXISTS `inventory_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_ledger` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `doc_type` enum('receipt','transfer_out','transfer_in','adjustment','sale','sales_return','purchase_return') NOT NULL,
  `doc_id` int unsigned NOT NULL,
  `qty_delta` int NOT NULL,
  `unit_cost` decimal(12,4) NOT NULL,
  `value_delta` decimal(14,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `doc_type` (`doc_type`),
  KEY `doc_id` (`doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_ledger`
--

LOCK TABLES `inventory_ledger` WRITE;
/*!40000 ALTER TABLE `inventory_ledger` DISABLE KEYS */;
INSERT INTO `inventory_ledger` VALUES
(1,3,1,'receipt',8,5,1250.0000,6250.0000,'2025-09-02 07:23:35'),
(2,3,2,'receipt',9,1,2000.0000,2000.0000,'2025-09-02 08:15:30'),
(3,1,2,'receipt',10,1,1500.0000,1500.0000,'2025-09-02 08:15:55');
/*!40000 ALTER TABLE `inventory_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice` (`invoice_id`),
  CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES
(1,1,2,1,3,650.00,1950.00),
(2,1,3,1,1,1050.00,1050.00),
(3,1,1,1,2,1500.00,3000.00),
(4,2,2,1,1,650.00,650.00);
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_payments`
--

DROP TABLE IF EXISTS `invoice_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int unsigned NOT NULL,
  `paid_at` datetime NOT NULL,
  `method` varchar(50) NOT NULL,
  `reference` varchar(191) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_invoice` (`invoice_id`),
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_payments`
--

LOCK TABLES `invoice_payments` WRITE;
/*!40000 ALTER TABLE `invoice_payments` DISABLE KEYS */;
INSERT INTO `invoice_payments` VALUES
(1,1,'2025-08-30 12:46:00','cash','',1000.00,'','2025-08-30 09:46:46'),
(2,1,'2025-08-29 12:50:00','cash','',1000.00,'','2025-08-30 10:00:10'),
(3,1,'2025-08-30 13:00:00','Wire','',4600.00,'','2025-08-30 10:00:51');
/*!40000 ALTER TABLE `invoice_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `inv_no` varchar(32) NOT NULL,
  `sales_order_id` int unsigned NOT NULL,
  `customer_id` int unsigned NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('unpaid','partial','paid','void') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `cogs_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `inv_no` (`inv_no`),
  KEY `idx_invoices_order` (`sales_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES
(1,'INV2025-0001',1,1,10.00,6000.00,600.00,6600.00,6600.00,'paid','2025-08-30 09:35:44','2025-08-30 10:00:51',0.00),
(2,'INV2025-0006',7,1,10.00,650.00,65.00,715.00,0.00,'unpaid','2025-09-02 11:37:20','2025-09-02 12:26:59',0.00);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `makes`
--

DROP TABLE IF EXISTS `makes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `makes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_makes_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `makes`
--

LOCK TABLES `makes` WRITE;
/*!40000 ALTER TABLE `makes` DISABLE KEYS */;
INSERT INTO `makes` VALUES
(1,'Fiat','fiat','2025-08-29 15:12:05',NULL),
(2,'Toyota','toyota','2025-08-29 15:12:18',NULL);
/*!40000 ALTER TABLE `makes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` enum('quote','sales_order','sales_invoice','purchase_order','purchase_invoice','customer','category','warehouse','product') NOT NULL,
  `entity_id` int unsigned NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `body` text NOT NULL,
  `created_by` varchar(191) NOT NULL,
  `created_by_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_entity` (`entity_type`,`entity_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES
(1,'quote',1,0,'This order will be late.','',NULL,'2025-08-30 06:21:59'),
(2,'quote',1,1,'Transfer will be on HSBC','',NULL,'2025-08-30 06:22:18');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_stocks`
--

DROP TABLE IF EXISTS `product_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_stocks` (
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty_on_hand` int unsigned NOT NULL DEFAULT '0',
  `qty_reserved` int unsigned NOT NULL DEFAULT '0',
  `avg_cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`product_id`,`warehouse_id`),
  UNIQUE KEY `ux_product_warehouse` (`product_id`,`warehouse_id`),
  UNIQUE KEY `uq_prod_wh` (`product_id`,`warehouse_id`),
  KEY `fk_ps_warehouse` (`warehouse_id`),
  CONSTRAINT `fk_ps_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_stocks`
--

LOCK TABLES `product_stocks` WRITE;
/*!40000 ALTER TABLE `product_stocks` DISABLE KEYS */;
INSERT INTO `product_stocks` VALUES
(1,1,9,1,0.0000),
(1,2,1,0,1500.0000),
(2,1,9,1,0.0000),
(2,2,1,0,0.0000),
(3,1,14,1,446.4286),
(3,2,1,0,2000.0000);
/*!40000 ALTER TABLE `product_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(191) NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `make_id` int unsigned DEFAULT NULL,
  `model_id` int unsigned DEFAULT NULL,
  `cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_make` (`make_id`),
  KEY `idx_products_model` (`model_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_products_make` FOREIGN KEY (`make_id`) REFERENCES `makes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_products_model` FOREIGN KEY (`model_id`) REFERENCES `vehicle_models` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,'PRD0001','Spoiler',1,1,1,1000.00,1500.00,'2025-08-29 15:28:48',NULL),
(2,'PRD0002','Coil',3,1,1,500.00,650.00,'2025-08-29 15:56:03',NULL),
(3,'PRD0003','Red Polish',2,1,1,850.00,1050.00,'2025-08-29 15:56:33',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_invoices`
--

DROP TABLE IF EXISTS `purchase_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pi_no` varchar(32) NOT NULL,
  `purchase_order_id` int unsigned NOT NULL,
  `supplier_id` int unsigned NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pi_no` (`pi_no`),
  KEY `idx_pi_po` (`purchase_order_id`),
  KEY `idx_pi_supplier` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_invoices`
--

LOCK TABLES `purchase_invoices` WRITE;
/*!40000 ALTER TABLE `purchase_invoices` DISABLE KEYS */;
INSERT INTO `purchase_invoices` VALUES
(1,'PI2025-0001',2,1,1500.00,10.00,150.00,1650.00,'2025-08-30 11:12:24',1650.00,'paid'),
(2,'PI2025-0006',3,1,14250.00,10.00,1425.00,15675.00,'2025-09-02 07:23:06',0.00,'unpaid'),
(4,'PI2025-0019',10,1,2000.00,10.00,200.00,2200.00,'2025-09-02 08:15:02',0.00,'unpaid'),
(5,'PI2025-0018',9,1,1500.00,10.00,150.00,1650.00,'2025-09-02 08:15:50',0.00,'unpaid');
/*!40000 ALTER TABLE `purchase_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `received_qty` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `price` decimal(12,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_poi_po` (`purchase_order_id`),
  KEY `idx_poi_product` (`product_id`),
  KEY `idx_poi_warehouse` (`warehouse_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
INSERT INTO `purchase_order_items` VALUES
(1,2,1,1,10,0.0000,150.00,1500.00),
(2,3,3,1,5,0.0000,1250.00,6250.00),
(3,3,3,1,5,0.0000,1250.00,6250.00),
(4,9,1,2,1,0.0000,1500.00,1500.00),
(5,10,3,2,1,0.0000,2000.00,2000.00);
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `po_no` varchar(32) NOT NULL,
  `supplier_id` int unsigned NOT NULL,
  `status` enum('draft','ordered','received','closed') NOT NULL DEFAULT 'draft',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_no` (`po_no`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
INSERT INTO `purchase_orders` VALUES
(2,'PO2025-0001',1,'closed',10.00,1500.00,150.00,1650.00,'2025-08-30 10:59:34'),
(3,'PO2025-0006',1,'received',10.00,14250.00,1425.00,15675.00,'2025-09-02 07:22:47'),
(6,'PO2025-0015',1,'draft',10.00,1300.00,130.00,1430.00,'2025-09-02 07:59:09'),
(9,'PO2025-0018',1,'received',10.00,1500.00,150.00,1650.00,'2025-09-02 08:09:52'),
(10,'PO2025-0019',1,'received',10.00,2000.00,200.00,2200.00,'2025-09-02 08:10:22');
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_return_items`
--

DROP TABLE IF EXISTS `purchase_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_return_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `purchase_return_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pri_pr` (`purchase_return_id`),
  KEY `idx_pri_prod` (`product_id`),
  KEY `idx_pri_wh` (`warehouse_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_return_items`
--

LOCK TABLES `purchase_return_items` WRITE;
/*!40000 ALTER TABLE `purchase_return_items` DISABLE KEYS */;
INSERT INTO `purchase_return_items` VALUES
(1,1,1,1,8,150.00,1200.00);
/*!40000 ALTER TABLE `purchase_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_returns`
--

DROP TABLE IF EXISTS `purchase_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_returns` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pr_no` varchar(32) NOT NULL,
  `purchase_invoice_id` int unsigned NOT NULL,
  `supplier_id` int unsigned NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pr_no` (`pr_no`),
  KEY `idx_pr_pi` (`purchase_invoice_id`),
  KEY `idx_pr_sup` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_returns`
--

LOCK TABLES `purchase_returns` WRITE;
/*!40000 ALTER TABLE `purchase_returns` DISABLE KEYS */;
INSERT INTO `purchase_returns` VALUES
(1,'PR2025-0001',1,1,1200.00,10.00,120.00,1320.00,'2025-08-31 09:50:42');
/*!40000 ALTER TABLE `purchase_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quote_items`
--

DROP TABLE IF EXISTS `quote_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quote_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_qi_quote` (`quote_id`),
  KEY `fk_qi_product` (`product_id`),
  KEY `fk_qi_wh` (`warehouse_id`),
  CONSTRAINT `fk_qi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_qi_quote` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_qi_wh` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quote_items`
--

LOCK TABLES `quote_items` WRITE;
/*!40000 ALTER TABLE `quote_items` DISABLE KEYS */;
INSERT INTO `quote_items` VALUES
(1,1,2,1,3,650.00,1950.00),
(2,1,3,1,1,1050.00,1050.00),
(3,1,1,1,2,1500.00,3000.00),
(5,3,2,1,1,650.00,650.00),
(6,3,3,1,1,1050.00,1050.00),
(7,3,1,1,1,1500.00,1500.00),
(8,6,1,1,1,1500.00,1500.00),
(9,6,2,2,1,650.00,650.00),
(10,7,1,1,1,1500.00,1500.00),
(11,7,2,1,1,650.00,650.00),
(12,8,1,1,1,1500.00,1500.00),
(13,9,2,1,1,650.00,650.00),
(14,10,2,1,1,650.00,650.00),
(15,11,3,1,1,1050.00,1050.00),
(16,12,3,1,1,1050.00,1050.00),
(17,13,3,1,1,1050.00,1050.00);
/*!40000 ALTER TABLE `quote_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotes`
--

DROP TABLE IF EXISTS `quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `quote_no` varchar(32) NOT NULL,
  `customer_id` int unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quote_no` (`quote_no`),
  KEY `idx_quotes_customer` (`customer_id`),
  CONSTRAINT `fk_quotes_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotes`
--

LOCK TABLES `quotes` WRITE;
/*!40000 ALTER TABLE `quotes` DISABLE KEYS */;
INSERT INTO `quotes` VALUES
(1,'Q2025-0001',1,'ordered',10.00,6000.00,600.00,6600.00,'2025-09-04','2025-08-29 16:21:31','2025-08-29 17:26:28'),
(3,'Q2025-1844',1,'sent',10.00,3200.00,320.00,3520.00,'2025-09-05','2025-09-02 08:39:30','2025-09-02 10:06:54'),
(6,'Q2025-0002',1,'sent',10.00,2150.00,215.00,2365.00,NULL,'2025-09-02 09:31:04','2025-09-02 09:58:03'),
(7,'Q2025-0003',1,'sent',10.00,2150.00,215.00,2365.00,NULL,'2025-09-02 09:41:18','2025-09-02 09:58:12'),
(8,'Q2025-0004',1,'sent',10.00,1500.00,150.00,1650.00,'2025-09-10','2025-09-02 09:49:27','2025-09-02 09:58:16'),
(9,'Q2025-0005',1,'sent',10.00,650.00,65.00,715.00,'2025-09-18','2025-09-02 09:58:58','2025-09-02 10:05:50'),
(10,'Q2025-0006',1,'accepted',10.00,650.00,65.00,715.00,'2025-09-27','2025-09-02 10:04:01','2025-09-02 11:37:16'),
(11,'Q2025-0007',1,'expired',0.00,1050.00,0.00,1050.00,NULL,'2025-09-02 10:09:39','2025-09-02 10:23:09'),
(12,'Q2025-0008',1,'cancelled',0.00,1050.00,0.00,1050.00,NULL,'2025-09-02 10:10:43','2025-09-02 10:16:42'),
(13,'Q2025-0009',1,'accepted',0.00,1050.00,0.00,1050.00,NULL,'2025-09-02 10:23:54','2025-09-02 11:01:22');
/*!40000 ALTER TABLE `quotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receipts`
--

DROP TABLE IF EXISTS `receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `receipts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `purchase_invoice_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_receipts_pi` (`purchase_invoice_id`),
  KEY `idx_receipts_product` (`product_id`),
  KEY `idx_receipts_wh` (`warehouse_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receipts`
--

LOCK TABLES `receipts` WRITE;
/*!40000 ALTER TABLE `receipts` DISABLE KEYS */;
INSERT INTO `receipts` VALUES
(6,1,1,1,5,150.00,'2025-08-30 11:22:54'),
(7,1,1,1,5,150.00,'2025-08-31 07:32:28'),
(8,2,3,1,5,1250.00,'2025-09-02 07:23:35'),
(9,4,3,2,1,2000.00,'2025-09-02 08:15:30'),
(10,5,1,2,1,1500.00,'2025-09-02 08:15:55');
/*!40000 ALTER TABLE `receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_items`
--

DROP TABLE IF EXISTS `sales_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sales_order_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_soi_so` (`sales_order_id`),
  CONSTRAINT `fk_soi_so` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_items`
--

LOCK TABLES `sales_order_items` WRITE;
/*!40000 ALTER TABLE `sales_order_items` DISABLE KEYS */;
INSERT INTO `sales_order_items` VALUES
(1,1,2,1,3,650.00,1950.00),
(2,1,3,1,1,1050.00,1050.00),
(3,1,1,1,2,1500.00,3000.00),
(4,6,3,1,1,1050.00,1050.00),
(5,7,2,1,1,650.00,650.00);
/*!40000 ALTER TABLE `sales_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_orders`
--

DROP TABLE IF EXISTS `sales_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `so_no` varchar(32) NOT NULL,
  `quote_id` int unsigned NOT NULL,
  `customer_id` int unsigned NOT NULL,
  `status` enum('open','closed','cancelled') NOT NULL DEFAULT 'open',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `so_no` (`so_no`),
  KEY `idx_so_quote` (`quote_id`),
  CONSTRAINT `fk_so_quote` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_orders`
--

LOCK TABLES `sales_orders` WRITE;
/*!40000 ALTER TABLE `sales_orders` DISABLE KEYS */;
INSERT INTO `sales_orders` VALUES
(1,'SO2025-0001',1,1,'closed',10.00,6000.00,600.00,6600.00,'2025-08-29 17:26:28','2025-09-02 12:28:50'),
(6,'SO2025-0009',13,1,'',0.00,1050.00,0.00,1050.00,'2025-09-02 11:01:22',NULL),
(7,'SO2025-0006',10,1,'',10.00,650.00,65.00,715.00,'2025-09-02 11:37:16',NULL);
/*!40000 ALTER TABLE `sales_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_return_items`
--

DROP TABLE IF EXISTS `sales_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_return_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sales_return_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sri_sr` (`sales_return_id`),
  KEY `idx_sri_product` (`product_id`),
  KEY `idx_sri_warehouse` (`warehouse_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_return_items`
--

LOCK TABLES `sales_return_items` WRITE;
/*!40000 ALTER TABLE `sales_return_items` DISABLE KEYS */;
INSERT INTO `sales_return_items` VALUES
(1,1,2,1,3,650.00,1950.00);
/*!40000 ALTER TABLE `sales_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_returns`
--

DROP TABLE IF EXISTS `sales_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_returns` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sr_no` varchar(32) NOT NULL,
  `sales_invoice_id` int unsigned NOT NULL,
  `client_id` int unsigned NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sr_no` (`sr_no`),
  KEY `idx_sr_invoice` (`sales_invoice_id`),
  KEY `idx_sr_client` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_returns`
--

LOCK TABLES `sales_returns` WRITE;
/*!40000 ALTER TABLE `sales_returns` DISABLE KEYS */;
INSERT INTO `sales_returns` VALUES
(1,'SR2025-0001',1,1,1950.00,10.00,195.00,2145.00,'2025-08-31 08:56:33');
/*!40000 ALTER TABLE `sales_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustment_items`
--

DROP TABLE IF EXISTS `stock_adjustment_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_adjustment_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `stock_adjustment_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `qty_change` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `stock_adjustment_id` (`stock_adjustment_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustment_items`
--

LOCK TABLES `stock_adjustment_items` WRITE;
/*!40000 ALTER TABLE `stock_adjustment_items` DISABLE KEYS */;
INSERT INTO `stock_adjustment_items` VALUES
(1,2,1,-1,'2025-09-01 11:19:17');
/*!40000 ALTER TABLE `stock_adjustment_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustments`
--

DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_adjustments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adj_no` varchar(32) NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `reason` enum('count','damage','shrink','other') NOT NULL DEFAULT 'count',
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adj_no` (`adj_no`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustments`
--

LOCK TABLES `stock_adjustments` WRITE;
/*!40000 ALTER TABLE `stock_adjustments` DISABLE KEYS */;
INSERT INTO `stock_adjustments` VALUES
(2,'AD250901-0002',1,'damage','','2025-09-01 11:19:17');
/*!40000 ALTER TABLE `stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_items`
--

DROP TABLE IF EXISTS `stock_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `stock_transfer_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `stock_transfer_id` (`stock_transfer_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_items`
--

LOCK TABLES `stock_transfer_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_items` DISABLE KEYS */;
INSERT INTO `stock_transfer_items` VALUES
(1,2,2,1,'2025-09-01 11:19:57');
/*!40000 ALTER TABLE `stock_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tr_no` varchar(32) NOT NULL,
  `from_warehouse_id` int unsigned NOT NULL,
  `to_warehouse_id` int unsigned NOT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tr_no` (`tr_no`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
INSERT INTO `stock_transfers` VALUES
(2,'TR250901-0002',1,2,'','2025-09-01 11:19:57');
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_payments`
--

DROP TABLE IF EXISTS `supplier_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` int unsigned NOT NULL,
  `purchase_invoice_id` int unsigned NOT NULL,
  `paid_at` datetime NOT NULL,
  `method` varchar(50) NOT NULL,
  `reference` varchar(64) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sp_supplier` (`supplier_id`),
  KEY `idx_sp_pi` (`purchase_invoice_id`),
  KEY `idx_sp_paid_at` (`paid_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_payments`
--

LOCK TABLES `supplier_payments` WRITE;
/*!40000 ALTER TABLE `supplier_payments` DISABLE KEYS */;
INSERT INTO `supplier_payments` VALUES
(1,1,1,'2025-08-30 11:09:00','bank','TR56775',650.00,'','2025-08-31 08:10:00'),
(2,1,1,'2025-08-31 11:12:00','bank','TR89885',1000.00,'','2025-08-31 08:13:06');
/*!40000 ALTER TABLE `supplier_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES
(1,'Ahmed Abdel Salam','01120121343','ahmedfuture445@gmail.com','Nozha');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','2025-08-29 13:37:55');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_models`
--

DROP TABLE IF EXISTS `vehicle_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicle_models` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `make_id` int unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vm_make_slug` (`make_id`,`slug`),
  KEY `idx_vm_make` (`make_id`),
  CONSTRAINT `fk_vm_make` FOREIGN KEY (`make_id`) REFERENCES `makes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_models`
--

LOCK TABLES `vehicle_models` WRITE;
/*!40000 ALTER TABLE `vehicle_models` DISABLE KEYS */;
INSERT INTO `vehicle_models` VALUES
(1,1,'Punto 2008','punto-2008','2025-08-29 15:12:42',NULL),
(2,2,'Corolla 2019','corolla-2019','2025-08-29 15:13:00',NULL);
/*!40000 ALTER TABLE `vehicle_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(191) NOT NULL,
  `location` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouses`
--

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
INSERT INTO `warehouses` VALUES
(1,'WH01','Main Store','Nasr City - Industrial Zone','2025-08-29 15:27:39','2025-09-01 11:07:46'),
(2,'WH02','Ramsis Warehouse','Ramsis Square','2025-09-01 11:07:34',NULL);
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'chatgpt2_mi'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-09-03  4:27:55
