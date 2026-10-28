<?php
require_once __DIR__ . '/auth.php';

require_json_access('delete_employees');

$EmployeeIdToDelete = $_POST['emID'] ?? 'None';

try {
    $pdo = db();

    $sql = "DELETE FROM employees WHERE employee_id = :employee_id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'employee_id' => $EmployeeIdToDelete
    ]);

    json_out(['success' => true, 'message' => 'Employee deleted.']);
} catch (PDOException $e) {
    json_out(['success' => false, 'message' => 'Error deleting employee.'], 500);
}

exit();
