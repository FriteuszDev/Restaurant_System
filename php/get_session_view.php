<?php
require_once __DIR__ . '/auth.php';

require_json_access('waiter_access');

$session_id = (int) ($_POST['session_id'] ?? 0);

if ($session_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid table session ID.'], 400);
}

$stmt = db()->prepare(
    "SELECT ts.session_id, ts.status, ts.opened_at, ts.paid_at, ts.paid_by,
            t.table_id, t.table_number, t.chairs, f.floor_name
     FROM table_sessions ts
     JOIN restaurant_tables t ON t.table_id = ts.table_id
     JOIN floors f ON f.floor_id = t.floor_id
     WHERE ts.session_id = :session_id"
);
$stmt->execute([':session_id' => $session_id]);
$session = $stmt->fetch();

if (!$session) {
    json_out(['success' => false, 'message' => 'Table session not found.'], 404);
}

$stmt = db()->prepare(
    "SELECT o.order_id, o.created_at,
            oi.item_id, oi.dish_name, oi.dish_price, oi.quantity, oi.note,
            oi.item_status, oi.ready_at, oi.claimed_by,
            CONCAT(e.employee_name, ' ', e.employee_surname) AS claimed_by_name
     FROM orders o
     JOIN order_items oi ON oi.order_id = o.order_id
     LEFT JOIN employees e ON e.employee_id = oi.claimed_by
     WHERE o.session_id = :session_id
     ORDER BY o.order_id, oi.item_id"
);
$stmt->execute([':session_id' => $session_id]);

$orders = [];
$total = 0.0;
$items_count = 0;

while ($row = $stmt->fetch()) {
    $order_id = (int) $row['order_id'];

    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'order_id' => $order_id,
            'created_at' => $row['created_at'],
            'items' => [],
        ];
    }

    $orders[$order_id]['items'][] = [
        'item_id' => (int) $row['item_id'],
        'dish_name' => $row['dish_name'],
        'dish_price' => $row['dish_price'],
        'quantity' => (int) $row['quantity'],
        'note' => $row['note'],
        'item_status' => $row['item_status'],
        'claimed_by_name' => $row['claimed_by_name'],
        'ready_at' => $row['ready_at'],
    ];

    $total += (float) $row['dish_price'] * (int) $row['quantity'];
    $items_count += (int) $row['quantity'];
}

json_out([
    'success' => true,
    'session' => [
        'session_id' => (int) $session['session_id'],
        'status' => $session['status'],
        'opened_at' => $session['opened_at'],
        'paid_at' => $session['paid_at'],
        'paid_by_name' => $session['paid_by'] ? employee_full_name_by_id((int) $session['paid_by']) : null,
        'table' => [
            'table_id' => (int) $session['table_id'],
            'table_number' => $session['table_number'],
            'chairs' => (int) $session['chairs'],
            'floor_name' => $session['floor_name'],
        ],
    ],
    'orders' => array_values($orders),
    'summary' => [
        'total' => number_format($total, 2, '.', ''),
        'items_count' => $items_count,
    ],
]);
