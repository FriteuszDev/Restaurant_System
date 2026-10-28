<?php
require_once __DIR__ . '/auth.php'; require_json_access('waiter_access'); $stmt = db()->query( "SELECT ts.session_id, ts.status, ts.opened_at, ts.paid_at, ts.total,
            t.table_number, f.floor_name,
            CONCAT(w.employee_name, ' ', w.employee_surname) AS waiter_name,
            CONCAT(p.employee_name, ' ', p.employee_surname) AS paid_by_name
     FROM table_sessions ts
     JOIN restaurant_tables t ON t.table_id = ts.table_id
     JOIN floors f ON f.floor_id = t.floor_id
     LEFT JOIN employees w ON w.employee_id = ts.waiter_id
     LEFT JOIN employees p ON p.employee_id = ts.paid_by
     WHERE ts.status = 'paid'
     ORDER BY ts.paid_at DESC, ts.session_id DESC
     LIMIT 100" ); $sessions = []; while ($row = $stmt->fetch()) { $sessions[(int) $row['session_id']] = [ 'session_id' => (int) $row['session_id'], 'status' => $row['status'], 'opened_at' => $row['opened_at'], 'paid_at' => $row['paid_at'], 'total' => $row['total'], 'table_number' => $row['table_number'], 'floor_name' => $row['floor_name'], 'waiter_name' => $row['waiter_name'], 'paid_by_name' => $row['paid_by_name'], 'items' => [], ]; } if ($sessions) { $session_ids = array_keys($sessions); $placeholders = implode(',', array_fill(0, count($session_ids), '?')); $stmt = db()->prepare( "SELECT o.session_id, oi.dish_name, oi.quantity, oi.note, oi.dish_price, oi.item_status
         FROM order_items oi
         JOIN orders o ON o.order_id = oi.order_id
         WHERE o.session_id IN ($placeholders)
         ORDER BY o.session_id, oi.item_id" ); $stmt->execute($session_ids); while ($row = $stmt->fetch()) { $sessions[(int) $row['session_id']]['items'][] = [ 'dish_name' => $row['dish_name'], 'quantity' => (int) $row['quantity'], 'note' => $row['note'], 'dish_price' => $row['dish_price'], 'item_status' => $row['item_status'], ]; } } json_out([ 'success' => true, 'history' => array_values($sessions), ]); 