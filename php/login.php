<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (is_ajax_request()) {
        json_out(['success' => false, 'message' => 'Invalid method.'], 405);
    }

    header('Location: ' . project_url('client/index.php'));
    exit();
}

$employee_id = trim($_POST['employee_id'] ?? '');
$password = $_POST['password'] ?? '';

if ($employee_id === '' || $password === '') {
    json_out(['success' => false, 'message' => 'Enter employee ID and password.'], 400);
}

if (!ctype_digit($employee_id)) {
    json_out(['success' => false, 'message' => 'Employee ID must be a number.'], 400);
}

$stmt = db()->prepare(
    "SELECT e.employee_id, e.employee_password, " . grade_permission_sql('g') . "
     FROM employees e
     LEFT JOIN grades g ON g.grade_id = e.grade_id
     WHERE e.employee_id = :employee_id"
);
$stmt->execute([':employee_id' => $employee_id]);
$employee = $stmt->fetch();

if (!$employee || !password_verify($password, $employee['employee_password'])) {
    json_out(['success' => false, 'message' => 'Invalid ID or password.'], 401);
}

session_regenerate_id(true);
$_SESSION['employee_id'] = (int) $employee['employee_id'];

$_SESSION['granted_flags'] = [];

foreach (grade_permission_flags() as $flag) {
    $_SESSION['granted_flags'][$flag] = !empty($employee[$flag]);
}

$redirect = default_panel_url($employee);

json_out([
    'success' => true,
    'message' => 'Logged in.',
    'redirect' => project_url($redirect),
]);
