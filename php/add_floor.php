<?php
require_once __DIR__ . '/auth.php';

require_json_access('edit_tables');

$name = trim($_POST['floor_name'] ?? '');

if ($name === '' || mb_strlen($name) > 30) {
    json_out(['success' => false, 'message' => 'Floor name must be 1-30 characters.'], 400);
}

$stmt = db()->prepare("SELECT floor_id FROM floors WHERE floor_name = :floor_name");
$stmt->execute([':floor_name' => $name]);

if ($stmt->fetch()) {
    json_out(['success' => false, 'message' => 'A floor with this name already exists.'], 409);
}

$stmt = db()->prepare("INSERT INTO floors (floor_name) VALUES (:floor_name)");
$stmt->execute([':floor_name' => $name]);

json_out([
    'success' => true,
    'message' => 'Floor added.',
    'floor' => [
        'floor_id' => (int) db()->lastInsertId(),
        'floor_name' => $name,
    ],
]);
