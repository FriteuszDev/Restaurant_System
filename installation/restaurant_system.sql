-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Paź 28, 2026 at 09:37 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_system`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `current_logo`
--

CREATE TABLE `current_logo` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `settings_key` varchar(50) DEFAULT 'restaurant_logo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `current_logo`
--

INSERT INTO `current_logo` (`id`, `filename`, `original_name`, `upload_date`, `file_size`, `mime_type`, `settings_key`) VALUES
(8, 'uploads/logo_7087c92f55ded8ea68e28459.jpg', 'your_restaurant_logo.jpg', '2026-10-28 20:36:58', 42266, 'image/jpeg', 'restaurant_logo');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(50) NOT NULL,
  `employee_surname` varchar(50) NOT NULL,
  `employee_password` varchar(255) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_name`, `employee_surname`, `employee_password`, `grade_id`) VALUES
(1, 'test', 'test', '$2y$10$QO2Se3K0B5hj87vVutq6DeU..qfrv1A1g5M5SVuHDHDPNB3.yLcO6', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `floors`
--

CREATE TABLE `floors` (
  `floor_id` int(11) NOT NULL,
  `floor_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
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
  `manage_settings` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `grade_name`, `config_access`, `chef_access`, `waiter_access`, `delete_dishes`, `edit_dishes`, `add_dishes`, `delete_employees`, `edit_employees`, `add_employees`, `delete_grades`, `edit_grades`, `add_grades`, `edit_tables`, `manage_settings`) VALUES
(1, 'boss', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `kitchen_history`
--

CREATE TABLE `kitchen_history` (
  `history_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `dish_name` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `action_type` varchar(20) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `menu`
--

CREATE TABLE `menu` (
  `dish_id` int(11) NOT NULL,
  `dish_name` varchar(100) NOT NULL,
  `dish_desc` text DEFAULT NULL,
  `dish_price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `menu_categories`
--

CREATE TABLE `menu_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `waiter_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `dish_id` int(11) DEFAULT NULL,
  `dish_name` varchar(100) NOT NULL,
  `dish_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `note` varchar(255) DEFAULT NULL,
  `item_status` varchar(20) NOT NULL DEFAULT 'new',
  `claimed_by` int(11) DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `table_id` int(11) NOT NULL,
  `floor_id` int(11) DEFAULT NULL,
  `table_number` varchar(10) NOT NULL,
  `chairs` int(11) NOT NULL DEFAULT 4,
  `status` varchar(20) NOT NULL DEFAULT 'free'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `restaurant_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `restaurant_logo`) VALUES
('restaurant_logo', 'uploads/logo_7087c92f55ded8ea68e28459.jpg', 'uploads/logo_7087c92f55ded8ea68e28459.jpg'),
('restaurant_name', 'Your Restaurant', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `table_sessions`
--

CREATE TABLE `table_sessions` (
  `session_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `waiter_id` int(11) DEFAULT NULL,
  `paid_by` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `opened_at` datetime NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `current_logo`
--
ALTER TABLE `current_logo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_current_logo_settings` (`settings_key`);

--
-- Indeksy dla tabeli `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indeksy dla tabeli `floors`
--
ALTER TABLE `floors`
  ADD PRIMARY KEY (`floor_id`);

--
-- Indeksy dla tabeli `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`);

--
-- Indeksy dla tabeli `kitchen_history`
--
ALTER TABLE `kitchen_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indeksy dla tabeli `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`dish_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeksy dla tabeli `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indeksy dla tabeli `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `waiter_id` (`waiter_id`);

--
-- Indeksy dla tabeli `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `dish_id` (`dish_id`),
  ADD KEY `claimed_by` (`claimed_by`);

--
-- Indeksy dla tabeli `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`table_id`),
  ADD KEY `floor_id` (`floor_id`);

--
-- Indeksy dla tabeli `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`),
  ADD KEY `idx_settings_logo` (`restaurant_logo`);

--
-- Indeksy dla tabeli `table_sessions`
--
ALTER TABLE `table_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `waiter_id` (`waiter_id`),
  ADD KEY `paid_by` (`paid_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `current_logo`
--
ALTER TABLE `current_logo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `floors`
--
ALTER TABLE `floors`
  MODIFY `floor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kitchen_history`
--
ALTER TABLE `kitchen_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `dish_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `table_sessions`
--
ALTER TABLE `table_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kitchen_history`
--
ALTER TABLE `kitchen_history`
  ADD CONSTRAINT `kitchen_history_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`item_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `kitchen_history_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_category_fk` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `table_sessions` (`session_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`waiter_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `menu` (`dish_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`claimed_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD CONSTRAINT `restaurant_tables_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`floor_id`) ON DELETE CASCADE;

--
-- Constraints for table `table_sessions`
--
ALTER TABLE `table_sessions`
  ADD CONSTRAINT `table_sessions_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`table_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `table_sessions_ibfk_2` FOREIGN KEY (`waiter_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `table_sessions_ibfk_3` FOREIGN KEY (`paid_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
