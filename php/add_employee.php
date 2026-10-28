<?php
require_once __DIR__ . '/auth.php';

require_json_access('add_employees');

$name = trim($_POST['name'] ?? '');
$surname = trim($_POST['surname'] ?? '');
$password = $_POST['password'] ?? '';
$grade = $_POST['grade'] ?? '';

if ($name === '' || mb_strlen($name) > 20) {
    json_out(['success' => false, 'message' => 'First name must be 1-20 characters.'], 400);
}

if ($surname === '' || mb_strlen($surname) > 20) {
    json_out(['success' => false, 'message' => 'Last name must be 1-20 characters.'], 400);
}

if (strlen($password) < 4) {
    json_out(['success' => false, 'message' => 'Password must be at least 4 characters.'], 400);
}

$password_coded = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_name = :grade_name");
    $stmt->execute([':grade_name' => $grade]);
    $grade_id = $stmt->fetchColumn();

    if (!$grade_id) {
        json_out(['success' => false, 'message' => 'Position not found.'], 404);
    }

    $sql2 = "INSERT INTO employees (employee_name, employee_surname, employee_password, grade_id) VALUES (:e_name, :e_surname, :e_password, :e_gradeid)";
    $stmt2 = $pdo->prepare($sql2);

    $stmt2->execute([
        'e_name' => $name,
        'e_surname' => $surname,
        'e_password' => $password_coded,
        'e_gradeid' => $grade_id,
    ]);

    $employee = [
        'employee_id' => (int) $pdo->lastInsertId(),
        'employee_name' => $name,
        'employee_surname' => $surname,
        'grade_name' => $grade,
    ];

    if (is_ajax_request()) {
        json_out(['success' => true, 'message' => 'Employee added.', 'employee' => $employee]);
    }

    echo "added employee";
} catch (PDOException $e) {
    if (is_ajax_request()) {
        json_out(['success' => false, 'message' => 'Error saving employee.'], 500);
    }

    die("connection error " . $e->getMessage());
}

header("Location: ../client/config_panel/config_panel.php");
exit();
