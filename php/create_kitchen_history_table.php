<?php
require_once __DIR__ . '/db.php';

$pdo = db();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS `kitchen_history` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

echo "Table kitchen_history created or already exists.\n";