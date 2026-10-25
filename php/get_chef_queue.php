<?php
require_once __DIR__ . '/auth.php';

require_json_access('chef_access');

$stmt = db()->query(
    "SELECT oi.item_id, oi.order_id, o.session_id,
            t.table_id, t.table_number, f.floor_name,
            oi.dish_name, oi.quantity, oi.note, oi.item_status, oi.ready_at,
            CONCAT(e.employee_name, ' ', e.employee_surname) AS claimed_by_name,
            CONCAT(w.employee_name, ' ', w.employee_surname) AS waiter_name,
            o.created_at
     FROM order_items oi
     JOIN orders o ON o.order_id = oi.order_id
     JOIN table_sessions ts ON ts.session_id = o.session_id
     JOIN restaurant_tables t ON t.table_id = ts.table_id
     JOIN floors f ON f.floor_id = t.floor_id
     LEFT JOIN employees e ON e.employee_id = oi.claimed_by
     LEFT JOIN employees w ON w.employee_id = o.waiter_id
     WHERE ts.status = 'open'
       AND (oi.item_status IN ('new', 'claimed')
            OR (oi.item_status = 'ready' AND oi.ready_at >= NOW() - INTERVAL 10 MINUTE))
     ORDER BY o.order_id, oi.item_id"
);

$items = [];

while ($row = $stmt->fetch()) {
    $items[] = [
        'item_id' => (int) $row['item_id'],
        'order_id' => (int) $row['order_id'],
        'session_id' => (int) $row['session_id'],
        'table_id' => (int) $row['table_id'],
        'table_number' => $row['table_number'],
        'floor_name' => $row['floor_name'],
        'dish_name' => $row['dish_name'],
        'quantity' => (int) $row['quantity'],
        'note' => $row['note'],
        'item_status' => $row['item_status'],
        'claimed_by_name' => $row['claimed_by_name'],
        'waiter_name' => $row['waiter_name'],
        'created_at' => $row['created_at'],
        'ready_at' => $row['ready_at'],
    ];
}

json_out([
    'success' => true,
    'items' => $items,
    'server_time' => date('Y-m-d H:i:s'),
]);
