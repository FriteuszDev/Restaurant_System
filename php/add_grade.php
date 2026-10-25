<?php
require_once __DIR__ . '/auth.php';

$current_employee = require_json_access('add_grades');

$name = trim($_POST['name'] ?? '');

if ($name === '' || mb_strlen($name) > 15) {
    json_out(['success' => false, 'message' => 'Position name must be 1-15 characters.'], 400);
}

$requested_flags = [];
foreach (grade_permission_flags() as $flag) {
    $requested_flags[$flag] = isset($_POST[$flag]) && (string) $_POST[$flag] !== '0' ? 1 : 0;
}

foreach ($requested_flags as $flag => $value) {
    if ($value === 1 && !can_grant_permission($current_employee, $flag)) {
        json_out([
            'success' => false,
            'message' => 'Nie mozesz przyznac uprawnienia, ktorego sam nie posiadasz.',
        ], 403);
    }
}

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_name = :grade_name");
    $stmt->execute([':grade_name' => $name]);

    if ($stmt->fetch()) {
        json_out(['success' => false, 'message' => 'A position with this name already exists.'], 409);
    }

    $columns = array_merge(['grade_name'], grade_permission_flags());
    $placeholders = [];
    $params = [':grade_name' => $name];
    foreach ($columns as $column) {
        $placeholders[] = ':' . $column;
        if ($column !== 'grade_name') {
            $params[':' . $column] = $requested_flags[$column];
        }
    }

    $sql = 'INSERT INTO grades (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $grade = array_merge([
        'grade_id' => (int) $pdo->lastInsertId(),
        'grade_name' => $name,
    ], $requested_flags);

    if (is_ajax_request()) {
        json_out(['success' => true, 'message' => 'Position added.', 'grade' => $grade]);
    }

    echo "added grade";
} catch (PDOException $e) {
    if (is_ajax_request()) {
        json_out(['success' => false, 'message' => 'Error saving position.'], 500);
    }

    die("Connection error " . $e->getMessage());
}

header("Location: ../client/config_panel/config_panel.php");
exit();
