<?php
require_once __DIR__ . '/auth.php';

require_json_access('edit_tables');

$floor_id = (int) ($_POST['floor_id'] ?? 0);

if ($floor_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid floor ID.'], 400);
}

$stmt = db()->prepare("SELECT floor_id FROM floors WHERE floor_id = :floor_id");
$stmt->execute([':floor_id' => $floor_id]);

if (!$stmt->fetch()) {
    json_out(['success' => false, 'message' => 'Floor not found.'], 404);
}

$stmt = db()->prepare(
    "SELECT COUNT(*) AS active_count
     FROM table_sessions ts
     JOIN restaurant_tables t ON t.table_id = ts.table_id
     WHERE t.floor_id = :floor_id AND ts.status IN ('open', 'to_pay')"
);
$stmt->execute([':floor_id' => $floor_id]);

if ((int) $stmt->fetch()['active_count'] > 0) {
    json_out([
        'success' => false,
        'message' => 'Cannot delete floor - an active order is in progress on one of its tables or waiting for payment.',
    ], 409);
}

$stmt = db()->prepare("DELETE FROM floors WHERE floor_id = :floor_id");
$stmt->execute([':floor_id' => $floor_id]);

json_out(['success' => true, 'message' => 'Floor deleted (with tables and history).']);
