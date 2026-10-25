<?php
require_once __DIR__ . '/auth.php';

require_json_access('waiter_access');

$stmt = db()->query(
    "SELECT ts.session_id, ts.status, ts.opened_at,
            t.table_id, t.table_number, t.chairs, f.floor_name
     FROM table_sessions ts
     JOIN restaurant_tables t ON t.table_id = ts.table_id
     JOIN floors f ON f.floor_id = t.floor_id
     WHERE ts.status IN ('open', 'to_pay')
     ORDER BY f.floor_name, t.table_number"
);
$sessions = [];

while ($row = $stmt->fetch()) {
    $sessions[] = [
        'session_id' => (int) $row['session_id'],
        'status' => $row['status'],
        'opened_at' => $row['opened_at'],
        'table_id' => (int) $row['table_id'],
        'table_number' => $row['table_number'],
        'chairs' => (int) $row['chairs'],
        'floor_name' => $row['floor_name'],
    ];
}

$stmt = db()->query(
    "SELECT oi.item_id, oi.order_id, ts.session_id, t.table_id,
            oi.dish_name, oi.quantity, oi.note, oi.item_status, oi.ready_at,
            CONCAT(e.employee_name, ' ', e.employee_surname) AS claimed_by_name
     FROM order_items oi
     JOIN orders o ON o.order_id = oi.order_id
     JOIN table_sessions ts ON ts.session_id = o.session_id
     JOIN restaurant_tables t ON t.table_id = ts.table_id
     LEFT JOIN employees e ON e.employee_id = oi.claimed_by
     WHERE ts.status IN ('open', 'to_pay')
     ORDER BY oi.item_id"
);
$items = [];

while ($row = $stmt->fetch()) {
    $items[] = [
        'item_id' => (int) $row['item_id'],
        'order_id' => (int) $row['order_id'],
        'session_id' => (int) $row['session_id'],
        'table_id' => (int) $row['table_id'],
        'dish_name' => $row['dish_name'],
        'quantity' => (int) $row['quantity'],
        'note' => $row['note'],
        'item_status' => $row['item_status'],
        'claimed_by_name' => $row['claimed_by_name'],
        'ready_at' => $row['ready_at'],
    ];
}

json_out([
    'success' => true,
    'sessions' => $sessions,
    'items' => $items,
    'server_time' => date('Y-m-d H:i:s'),
]);
