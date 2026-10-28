<?php
require_once __DIR__ . '/auth.php';

$employee = require_json_access('chef_access');

$item_id = (int) ($_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid item ID.'], 400);
}

$stmt = db()->prepare(
    "SELECT oi.item_id, oi.item_status, oi.claimed_by
     FROM order_items oi
     WHERE oi.item_id = :item_id"
);
$stmt->execute([':item_id' => $item_id]);
$item = $stmt->fetch();

if (!$item) {
    json_out(['success' => false, 'message' => 'Item not found.'], 404);
}

if ($item['item_status'] === 'ready') {
    json_out(['success' => false, 'message' => 'This item is already ready.'], 409);
}

if ($item['item_status'] === 'claimed') {
    $claimer = employee_full_name_by_id((int) $item['claimed_by']);
    json_out([
        'success' => false,
        'message' => 'This item is already taken by: ' . ($claimer ?? 'another chef') . '.',
    ], 409);
}

$stmt = db()->prepare(
    "UPDATE order_items SET item_status = 'claimed', claimed_by = :employee_id
     WHERE item_id = :item_id AND item_status = 'new'"
);
$stmt->execute([
    ':employee_id' => $employee['employee_id'],
    ':item_id' => $item_id,
]);

if ($stmt->rowCount() === 0) {
    json_out(['success' => false, 'message' => 'Someone else already took this item.'], 409);
}

// Save to kitchen history
$stmt2 = db()->prepare("SELECT dish_name, quantity FROM order_items WHERE item_id = :item_id");
$stmt2->execute([':item_id' => $item_id]);
$dish = $stmt2->fetch();

if ($dish) {
    $stmt3 = db()->prepare(
        "INSERT INTO kitchen_history (order_item_id, dish_name, quantity, action_type, employee_id, employee_name)
         VALUES (:item_id, :dish_name, :quantity, 'claim', :employee_id, :employee_name)"
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
    'message' => 'Item taken to work.',
    'item_id' => $item_id,
    'claimed_by_name' => employee_full_name($employee),
]);
