<?php
require_once __DIR__ . '/auth.php';

require_json_access('edit_tables');

$floor_id = (int) ($_POST['floor_id'] ?? 0);
$table_number = trim($_POST['table_number'] ?? '');
$chairs = (int) ($_POST['chairs'] ?? 0);
if ($chairs <= 0) {
    $chairs = 4;
}

if ($floor_id <= 0) {
    json_out(['success' => false, 'message' => 'Wybierz pietro.'], 400);
}

if ($table_number === '' || mb_strlen($table_number) > 10) {
    json_out(['success' => false, 'message' => 'Numer stolika musi miec 1-10 znakow.'], 400);
}

if ($chairs < 1 || $chairs > 50) {
    json_out(['success' => false, 'message' => 'Liczba krzesel musi byc z zakresu 1-50.'], 400);
}

$stmt = db()->prepare("SELECT floor_id FROM floors WHERE floor_id = :floor_id");
$stmt->execute([':floor_id' => $floor_id]);

if (!$stmt->fetch()) {
    json_out(['success' => false, 'message' => 'Nie znaleziono pietra.'], 404);
}

$stmt = db()->prepare(
    "SELECT table_id FROM restaurant_tables WHERE floor_id = :floor_id AND table_number = :table_number"
);
$stmt->execute([':floor_id' => $floor_id, ':table_number' => $table_number]);

if ($stmt->fetch()) {
    json_out(['success' => false, 'message' => 'Stolik o tym numerze juz istnieje na tym pietrze.'], 409);
}

$stmt = db()->prepare(
    "INSERT INTO restaurant_tables (floor_id, table_number, chairs, status)
     VALUES (:floor_id, :table_number, :chairs, 'free')"
);
$stmt->execute([
    ':floor_id' => $floor_id,
    ':table_number' => $table_number,
    ':chairs' => $chairs,
]);

json_out([
    'success' => true,
    'message' => 'Dodano stolik.',
    'table' => [
        'table_id' => (int) db()->lastInsertId(),
        'floor_id' => $floor_id,
        'table_number' => $table_number,
        'chairs' => $chairs,
        'status' => 'free',
    ],
]);
