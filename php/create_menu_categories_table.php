<?php
require_once __DIR__ . '/db.php'; $pdo = db(); $pdo->exec("
    CREATE TABLE IF NOT EXISTS `menu_categories` (
        `category_id` int(11) NOT NULL AUTO_INCREMENT,
        `category_name` varchar(50) NOT NULL,
        PRIMARY KEY (`category_id`),
        UNIQUE KEY `category_name` (`category_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
"); $pdo->prepare("INSERT IGNORE INTO menu_categories (category_name) VALUES ('General')")->execute(); $columns = $pdo->query("SHOW COLUMNS FROM menu LIKE 'category_id'")->fetchAll(); if (!$columns) { try { $pdo->exec("ALTER TABLE menu ADD COLUMN category_id int(11) DEFAULT NULL"); } catch (Throwable $e) {} try { $pdo->exec("ALTER TABLE menu ADD KEY category_id (category_id)"); } catch (Throwable $e) {} try { $pdo->exec("ALTER TABLE menu ADD CONSTRAINT menu_category_fk FOREIGN KEY (category_id) REFERENCES menu_categories (category_id) ON DELETE SET NULL"); } catch (Throwable $e) {} } $defaultId = (int) $pdo->query("SELECT category_id FROM menu_categories WHERE category_name = 'General' LIMIT 1")->fetchColumn(); if ($defaultId > 0) { $pdo->prepare("UPDATE menu SET category_id = :cid WHERE category_id IS NULL")->execute([':cid' => $defaultId]); } echo "Menu categories table ready.\n"; 