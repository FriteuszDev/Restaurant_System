<?php
require_once __DIR__ . '/auth.php';

require_json_access('edit_tables');

$name = trim($_POST['floor_name'] ?? '');

if ($name === '' || mb_strlen($name) > 30) {
    json_out(['success' => false, 'message' => 'Nazwa pietra musi miec 1-30 znakow.'], 400);
}

$stmt = db()->prepare("SELECT floor_id FROM floors WHERE floor_name = :floor_name");
$stmt->execute([':floor_name' => $name]);

if ($stmt->fetch()) {
    json_out(['success' => false, 'message' => 'Pietro o takiej nazwie juz istnieje.'], 409);
}

$stmt = db()->prepare("INSERT INTO floors (floor_name) VALUES (:floor_name)");
$stmt->execute([':floor_name' => $name]);

json_out([
    'success' => true,
    'message' => 'Dodano pietro.',
    'floor' => [
        'floor_id' => (int) db()->lastInsertId(),
        'floor_name' => $name,
    ],
]);
