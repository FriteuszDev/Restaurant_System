<?php
require_once __DIR__ . '/auth.php';

require_json_access('chef_access');

$stmt = db()->query(
    "SELECT kh.history_id, kh.order_item_id, kh.dish_name, kh.quantity,
            kh.action_type, kh.employee_id, kh.employee_name, kh.created_at
     FROM kitchen_history kh
     ORDER BY kh.created_at DESC
     LIMIT 200"
);

$history = [];

while ($row = $stmt->fetch()) {
    $history[] = [
        'history_id' => (int) $row['history_id'],
        'order_item_id' => $row['order_item_id'] ? (int) $row['order_item_id'] : null,
        'dish_name' => $row['dish_name'],
        'quantity' => (int) $row['quantity'],
        'action_type' => $row['action_type'],
        'employee_id' => $row['employee_id'] ? (int) $row['employee_id'] : null,
        'employee_name' => $row['employee_name'],
        'created_at' => $row['created_at'],
    ];
}

json_out([
    'success' => true,
    'history' => $history,
]);