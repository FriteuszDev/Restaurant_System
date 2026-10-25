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
    json_out(['success' => false, 'message' => 'Nie znaleziono pietra.'], 404);
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
        'message' => 'Nie mozna usunac pietra - na ktoryms stoliku trwa aktualne zamowienie lub czeka na plate.',
    ], 409);
}

$stmt = db()->prepare("DELETE FROM floors WHERE floor_id = :floor_id");
$stmt->execute([':floor_id' => $floor_id]);

json_out(['success' => true, 'message' => 'Usunieto pietro (razem ze stolikami i historia).']);
