<?php
require_once __DIR__ . '/auth.php';

$employee = require_json_access('waiter_access');

$item_id = (int) ($_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    json_out(['success' => false, 'message' => 'Nieprawidlowe ID pozycji.'], 400);
}

$stmt = db()->prepare("SELECT oi.item_id, oi.item_status, ts.session_id, ts.status AS session_status
     FROM order_items oi
     JOIN orders o ON o.order_id = oi.order_id
     JOIN table_sessions ts ON ts.session_id = o.session_id
     WHERE oi.item_id = :item_id");
$stmt->execute([':item_id' => $item_id]);
$item = $stmt->fetch();

if (!$item) {
    json_out(['success' => false, 'message' => 'Nie znaleziono pozycji.'], 404);
}

if ($item['item_status'] !== 'ready') {
    json_out(['success' => false, 'message' => 'Tylko pozycje gotowe moga zostac zabrane z kuchni.'], 409);
}

if (!in_array($item['session_status'], ['open', 'to_pay'], true)) {
    json_out(['success' => false, 'message' => 'Sesja stolika jest juz zamknieta.'], 409);
}

$stmt = db()->prepare(
    "UPDATE order_items SET item_status = 'served' WHERE item_id = :item_id AND item_status = 'ready'"
);
$stmt->execute([':item_id' => $item_id]);

if ($stmt->rowCount() === 0) {
    json_out(['success' => false, 'message' => 'Pozycja zostala juz obsłużona przez kogo innego.'], 409);
}

json_out([
    'success' => true,
    'message' => 'Pozycja zabrana z kuchni i dostarczona do stolika.',
    'item_id' => $item_id,
]);