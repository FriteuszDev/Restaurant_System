<?php
require_once __DIR__ . '/auth.php';

$employee = require_json_access('chef_access');

$item_id = (int) ($_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid item ID.'], 400);
}

$stmt = db()->prepare("SELECT item_id, item_status FROM order_items WHERE item_id = :item_id");
$stmt->execute([':item_id' => $item_id]);
$item = $stmt->fetch();

if (!$item) {
    json_out(['success' => false, 'message' => 'Item not found.'], 404);
}

if ($item['item_status'] === 'ready') {
    json_out(['success' => false, 'message' => 'Ta pozycja jest juz oznaczona jako gotowa.'], 409);
}

$stmt = db()->prepare(
    "UPDATE order_items
     SET item_status = 'ready', ready_at = NOW(),
         claimed_by = COALESCE(claimed_by, :employee_id)
     WHERE item_id = :item_id"
);
$stmt->execute([
    ':employee_id' => $employee['employee_id'],
    ':item_id' => $item_id,
]);

json_out([
    'success' => true,
    'message' => 'Item marked as ready - waiter notified.',
    'item_id' => $item_id,
]);
