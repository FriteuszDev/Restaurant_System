<?php
require_once __DIR__ . '/auth.php';

$employee = require_json_access('waiter_access');

$item_id = (int) ($_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid item ID.'], 400);
}

$stmt = db()->prepare("SELECT oi.item_id, oi.item_status, ts.session_id, ts.status AS session_status
     FROM order_items oi
     JOIN orders o ON o.order_id = oi.order_id
     JOIN table_sessions ts ON ts.session_id = o.session_id
     WHERE oi.item_id = :item_id");
$stmt->execute([':item_id' => $item_id]);
$item = $stmt->fetch();

if (!$item) {
    json_out(['success' => false, 'message' => 'Item not found.'], 404);
}

if ($item['item_status'] !== 'ready') {
    json_out(['success' => false, 'message' => 'Only ready items can be taken from the kitchen.'], 409);
}

if (!in_array($item['session_status'], ['open', 'to_pay'], true)) {
    json_out(['success' => false, 'message' => 'Table session is already closed.'], 409);
}

$stmt = db()->prepare(
    "UPDATE order_items SET item_status = 'served' WHERE item_id = :item_id AND item_status = 'ready'"
);
$stmt->execute([':item_id' => $item_id]);

if ($stmt->rowCount() === 0) {
    json_out(['success' => false, 'message' => 'Item already served by someone else.'], 409);
}

// Save to kitchen history
$stmt2 = db()->prepare("SELECT dish_name, quantity FROM order_items WHERE item_id = :item_id");
$stmt2->execute([':item_id' => $item_id]);
$dish = $stmt2->fetch();

if ($dish) {
    $stmt3 = db()->prepare(
        "INSERT INTO kitchen_history (order_item_id, dish_name, quantity, action_type, employee_id, employee_name)
         VALUES (:item_id, :dish_name, :quantity, 'serve', :employee_id, :employee_name)"
    );
    $stmt3->execute([
        ':item_id' => $item_id,
        ':dish_name' => $dish['dish_name'],
        ':quantity' => (int) $dish['quantity'],
        ':employee_id' => $employee['employee_id'],
        ':employee_name' => employee_full_name($employee),
    ]);
}

json_out([
    'success' => true,
    'message' => 'Item taken from kitchen and delivered to table.',
    'item_id' => $item_id,
]);