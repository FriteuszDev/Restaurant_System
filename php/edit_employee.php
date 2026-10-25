<?php
require_once __DIR__ . '/auth.php';

require_json_access('edit_employees');

$employee_id = (int) ($_POST['employee_id'] ?? 0);
$name = trim($_POST['employee_name'] ?? '');
$surname = trim($_POST['employee_surname'] ?? '');
$grade_name = trim($_POST['grade_name'] ?? '');

if ($employee_id <= 0) {
    json_out(['success' => false, 'message' => 'Invalid employee ID.'], 400);
}

if ($name === '' || mb_strlen($name) > 50) {
    json_out(['success' => false, 'message' => 'First name must be 1-50 characters.'], 400);
}

if ($surname === '' || mb_strlen($surname) > 50) {
    json_out(['success' => false, 'message' => 'Last name must be 1-50 characters.'], 400);
}

try {
    $pdo = db();

    // Check employee exists
    $stmt = $pdo->prepare("SELECT employee_id FROM employees WHERE employee_id = :employee_id");
    $stmt->execute([':employee_id' => $employee_id]);
    if (!$stmt->fetch()) {
        json_out(['success' => false, 'message' => 'Employee not found.'], 404);
    }

    // Resolve grade_id from grade_name
    $grade_id = null;
    if ($grade_name !== '') {
        $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_name = :grade_name");
        $stmt->execute([':grade_name' => $grade_name]);
        $grade_id = $stmt->fetchColumn();
        if (!$grade_id) {
            json_out(['success' => false, 'message' => 'Grade not found.'], 404);
        }
    }

    $stmt = $pdo->prepare("UPDATE employees SET employee_name = :name, employee_surname = :surname, grade_id = :grade_id WHERE employee_id = :id");
    $stmt->execute([
        ':name' => $name,
        ':surname' => $surname,
        ':grade_id' => $grade_id,
        ':id' => $employee_id,
    ]);

    json_out([
        'success' => true,
        'message' => 'Employee updated.',
        'employee' => [
            'employee_id' => $employee_id,
            'employee_name' => $name,
            'employee_surname' => $surname,
            'grade_name' => $grade_name,
        ],
    ]);
} catch (PDOException $e) {
    json_out(['success' => false, 'message' => 'Error updating employee.'], 500);
}

exit();