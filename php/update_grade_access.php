<?php
require_once __DIR__ . '/auth.php';

$current_employee = require_login();

if (!has_grade_management($current_employee)) {
    json_out(['success' => false, 'message' => 'Access denied to this operation.'], 403);
}

$grade_id = (int) ($_POST['grade_id'] ?? 0);
$flag = trim($_POST['flag'] ?? '');
$value = (int) ($_POST['value'] ?? 0);

$allowed_flags = grade_permission_flags();

if ($grade_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid position ID.'], 400);
}

if (!in_array($flag, $allowed_flags, true)) {
    json_out(['success' => false, 'message' => 'Unknown permission.'], 400);
}

$value = $value === 1 ? 1 : 0;

if ($value === 1 && !can_grant_permission($current_employee, $flag)) {
    json_out([
        'success' => false,
        'message' => 'You cannot grant a permission you do not have.',
    ], 403);
}

if ($flag === 'config_access' && $value === 0 && (int) ($current_employee['grade_id'] ?? 0) === $grade_id) {
    json_out([
        'success' => false,
        'message' => 'Cannot revoke access to configuration panel.',
    ], 409);
}

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_id = :grade_id");
    $stmt->execute([':grade_id' => $grade_id]);

    if (!$stmt->fetch()) {
        json_out(['success' => false, 'message' => 'Position not found.'], 404);
    }

        if ($flag === 'config_access' && $value === 0) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM grades WHERE config_access = 1");
        if ((int) $stmt->fetchColumn() <= 1) {
            json_out([
                'success' => false,
                'message' => 'There must be at least one position with configuration access.',
            ], 409);
        }
    }

    $sql = "UPDATE grades SET {$flag} = :value WHERE grade_id = :grade_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':value' => $value,
        ':grade_id' => $grade_id,
    ]);

    json_out([
        'success' => true,
        'message' => 'Position permissions saved.',
        'grade_id' => $grade_id,
        'flag' => $flag,
        'value' => $value,
    ]);
} catch (PDOException $e) {
    json_out(['success' => false, 'message' => 'Error saving permissions.'], 500);
}

exit();