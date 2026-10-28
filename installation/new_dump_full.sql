-- MariaDB dump - Restaurant_System (pelny, poprawiony)
-- Wygenerowano automatycznie przez installation/generate_full_dump.php
-- Host: localhost    Database: restaurant_system
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `current_logo`
--

DROP TABLE IF EXISTS `current_logo`;
CREATE TABLE `current_logo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_size` int(10) unsigned DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `settings_key` varchar(50) DEFAULT 'restaurant_logo',
  PRIMARY KEY (`id`),
  KEY `idx_current_logo_settings` (`settings_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `current_logo`
--


--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_name` varchar(50) NOT NULL,
  `employee_surname` varchar(50) NOT NULL,
  `employee_password` varchar(255) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` (`employee_id`, `employee_name`, `employee_surname`, `employee_password`, `grade_id`) VALUES
('9','Code Cloud','asd','$2y$10$ujUybt/QX8q4p7a.4WiloO.lKw4KURa3k3Jpr8YR8VObrwG4QqnH.','5'),
('10','Marshal IT','asd','$2y$10$ujUybt/QX8q4p7a.4WiloO.lKw4KURa3k3Jpr8YR8VObrwG4QqnH.','5'),
('11','asd dsa','asd','$2y$10$ujUybt/QX8q4p7a.4WiloO.lKw4KURa3k3Jpr8YR8VObrwG4QqnH.','1'),
('12','CS2 Case Opener','asd','$2y$10$ujUybt/QX8q4p7a.4WiloO.lKw4KURa3k3Jpr8YR8VObrwG4QqnH.','5'),
('13','Kazik','Nowak','$2y$10$UlkruuPpPNbEMfdKaTWA/u1WeQsqBhqIFY8HecB4SNOTrUbJWvv8q','1');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `floors`
--

DROP TABLE IF EXISTS `floors`;
CREATE TABLE `floors` (
  `floor_id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_name` varchar(50) NOT NULL,
  PRIMARY KEY (`floor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `floors`
--

LOCK TABLES `floors` WRITE;
/*!40000 ALTER TABLE `floors` DISABLE KEYS */;
INSERT INTO `floors` (`floor_id`, `floor_name`) VALUES
('1','Ground Floor'),
('2','First Floor');
/*!40000 ALTER TABLE `floors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `grade_name` varchar(50) NOT NULL,
  `config_access` tinyint(4) DEFAULT 0,
  `chef_access` tinyint(4) DEFAULT 0,
  `waiter_access` tinyint(4) DEFAULT 0,
  `delete_dishes` tinyint(1) DEFAULT 0,
  `edit_dishes` tinyint(1) DEFAULT 0,
  `add_dishes` tinyint(1) DEFAULT 0,
  `delete_employees` tinyint(1) DEFAULT 0,
  `edit_employees` tinyint(1) DEFAULT 0,
  `add_employees` tinyint(1) DEFAULT 0,
  `delete_grades` tinyint(1) DEFAULT 0,
  `edit_grades` tinyint(1) DEFAULT 0,
  `add_grades` tinyint(1) DEFAULT 0,
  `edit_tables` tinyint(1) DEFAULT 0,
  `manage_settings` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`grade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

LOCK TABLES `grades` WRITE;
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
INSERT INTO `grades` (`grade_id`, `grade_name`, `config_access`, `chef_access`, `waiter_access`, `delete_dishes`, `edit_dishes`, `add_dishes`, `delete_employees`, `edit_employees`, `add_employees`, `delete_grades`, `edit_grades`, `add_grades`, `edit_tables`, `manage_settings`) VALUES
('1','Manager','1','1','1','1','1','1','1','1','1','1','1','1','1','1'),
('2','Chef','1','1','0','0','0','0','0','0','0','0','0','0','0','1'),
('3','Waiter','0','0','1','0','0','0','0','0','0','0','0','0','0','0'),
('4','Cashier','1','0','1','0','0','0','0','0','0','0','0','0','0','1'),
('5','waiter','1','1','0','0','0','0','0','0','0','0','0','0','1','1'),
('6','boss','1','1','1','1','1','1','1','1','1','1','1','1','1','0');
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `dish_id` int(11) NOT NULL AUTO_INCREMENT,
  `dish_name` varchar(100) NOT NULL,
  `dish_desc` text DEFAULT NULL,
  `dish_price` decimal(10,2) NOT NULL,
  `dish_img` varchar(255) DEFAULT '',
  PRIMARY KEY (`dish_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` (`dish_id`, `dish_name`, `dish_desc`, `dish_price`, `dish_img`) VALUES
('1','Beef Tenderloin','Juicy steak. Herb butter. Roasted potatoes.','89.99','beef_tenderloin.jpg'),
('5','Tomato Cream Soup with Mozzarella','Tomato cream soup. Mini mozzarella. Basil oil.','24.00','tomato_cream_soup.jpg'),
('6','Classic Beef Burger','100% beef. Cheddar and bacon. Homemade fries.','46.50','classic_beef_burger.jpg'),
('8','Roasted Duck with Apples','Roasted duck. Apples and marjoram. Dumplings.','76.00','roasted_duck.jpg'),
('9','Veal Dumplings','Handmade dumplings. Veal filling. Onion and sour cream.','36.00','veal_dumplings.jpg'),
('10','Traditional Tiramisu','Italian dessert. Mascarpone espresso. Dark cocoa.','28.00','tiramisu.jpg');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_tables`
--

DROP TABLE IF EXISTS `restaurant_tables`;
CREATE TABLE `restaurant_tables` (
  `table_id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_id` int(11) DEFAULT NULL,
  `table_number` varchar(10) NOT NULL,
  `chairs` int(11) NOT NULL DEFAULT 4,
  `status` varchar(20) NOT NULL DEFAULT 'free',
  PRIMARY KEY (`table_id`),
  KEY `floor_id` (`floor_id`),
  CONSTRAINT `restaurant_tables_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`floor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_tables`
--

LOCK TABLES `restaurant_tables` WRITE;
/*!40000 ALTER TABLE `restaurant_tables` DISABLE KEYS */;
INSERT INTO `restaurant_tables` (`table_id`, `floor_id`, `table_number`, `chairs`, `status`) VALUES
('1','1','1','4','free'),
('2','1','2','2','free'),
('3','1','3','4','free'),
('4','2','1','4','free');
/*!40000 ALTER TABLE `restaurant_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_sessions`
--

DROP TABLE IF EXISTS `table_sessions`;
CREATE TABLE `table_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `waiter_id` int(11) DEFAULT NULL,
  `paid_by` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `opened_at` datetime NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `table_id` (`table_id`),
  KEY `waiter_id` (`waiter_id`),
  KEY `paid_by` (`paid_by`),
  CONSTRAINT `table_sessions_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`table_id`) ON DELETE CASCADE,
  CONSTRAINT `table_sessions_ibfk_2` FOREIGN KEY (`waiter_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  CONSTRAINT `table_sessions_ibfk_3` FOREIGN KEY (`paid_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_sessions`
--


--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `waiter_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `session_id` (`session_id`),
  KEY `waiter_id` (`waiter_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `table_sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`waiter_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--


--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `dish_id` int(11) DEFAULT NULL,
  `dish_name` varchar(30) NOT NULL,
  `dish_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `note` varchar(255) DEFAULT NULL,
  `item_status` varchar(20) NOT NULL DEFAULT 'new',
  `claimed_by` int(11) DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `dish_id` (`dish_id`),
  KEY `claimed_by` (`claimed_by`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `menu` (`dish_id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`claimed_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--


--
-- Table structure for table `kitchen_history`
--

DROP TABLE IF EXISTS `kitchen_history`;
CREATE TABLE `kitchen_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_item_id` int(11) DEFAULT NULL,
  `dish_name` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `action_type` varchar(20) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `employee_id` (`employee_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `kitchen_history_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`item_id`) ON DELETE SET NULL,
  CONSTRAINT `kitchen_history_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kitchen_history`
--

LOCK TABLES `kitchen_history` WRITE;
/*!40000 ALTER TABLE `kitchen_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `kitchen_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `restaurant_logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`setting_key`),
  KEY `idx_settings_logo` (`restaurant_logo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`setting_key`, `setting_value`, `restaurant_logo`) VALUES
('restaurant_name','Restauracja',NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Restore foreign key checks
--

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

