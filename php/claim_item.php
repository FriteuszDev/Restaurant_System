<?php
require_once __DIR__ . '/auth.php';

$employee = require_json_access('chef_access');

$item_id = (int) ($_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    json_out(['success' => false, 'message' => 'Nieprawidlowe ID pozycji.'], 400);
}

$stmt = db()->prepare(
    "SELECT oi.item_id, oi.item_status, oi.claimed_by
     FROM order_items oi
     WHERE oi.item_id = :item_id"
);
$stmt->execute([':item_id' => $item_id]);
$item = $stmt->fetch();

if (!$item) {
    json_out(['success' => false, 'message' => 'Nie znaleziono pozycji.'], 404);
}

if ($item['item_status'] === 'ready') {
    json_out(['success' => false, 'message' => 'Ta pozycja jest juz gotowa.'], 409);
}

if ($item['item_status'] === 'claimed') {
    $claimer = employee_full_name_by_id((int) $item['claimed_by']);
    json_out([
        'success' => false,
        'message' => 'Ta pozycja jest juz zajeta przez: ' . ($claimer ?? 'inny kucharz') . '.',
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
    json_out(['success' => false, 'message' => 'Ktos inny zdazyl zabrac tla pozycje.'], 409);
}

json_out([
    'success' => true,
    'message' => 'Zabales pozycje do roboty.',
    'item_id' => $item_id,
    'claimed_by_name' => employee_full_name($employee),
]);
