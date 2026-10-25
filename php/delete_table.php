<?php
require_once __DIR__ . '/auth.php';

require_json_access('edit_tables');

$table_id = (int) ($_POST['table_id'] ?? 0);

if ($table_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid table ID.'], 400);
}

$stmt = db()->prepare("SELECT table_id FROM restaurant_tables WHERE table_id = :table_id");
$stmt->execute([':table_id' => $table_id]);

if (!$stmt->fetch()) {
    json_out(['success' => false, 'message' => 'Table not found.'], 404);
}

$stmt = db()->prepare(
    "SELECT COUNT(*) AS active_count
     FROM table_sessions
     WHERE table_id = :table_id AND status IN ('open', 'to_pay')"
);
$stmt->execute([':table_id' => $table_id]);

if ((int) $stmt->fetch()['active_count'] > 0) {
    json_out([
        'success' => false,
        'message' => 'Nie mozna usunac stolika - trwa na nim zamowienie lub czeka na plate.',
    ], 409);
}

$stmt = db()->prepare("DELETE FROM restaurant_tables WHERE table_id = :table_id");
$stmt->execute([':table_id' => $table_id]);

json_out(['success' => true, 'message' => 'Table deleted (with history).']);
