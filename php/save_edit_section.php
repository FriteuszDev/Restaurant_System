<?php
require_once __DIR__ . '/auth.php';

$employee = require_login();
$section = trim((string) ($_POST['section'] ?? ''));
$payload = json_decode((string) ($_POST['payload'] ?? ''), true);

if (!is_array($payload)) {
    json_out(['success' => false, 'message' => 'Invalid payload.'], 400);
}

$pdo = db();

if ($section === 'menu') {
    if (empty($employee['edit_dishes']) && empty($employee['delete_dishes'])) {
        json_out(['success' => false, 'message' => 'Access denied to this operation.'], 403);
    }

    $rows = $payload['rows'] ?? [];
    $deleted = $payload['deleted'] ?? [];

    if (!empty($employee['delete_dishes'])) {
        foreach ((array) $deleted as $dishId) {
            $dishId = (int) $dishId;
            if ($dishId <= 0) {
                continue;
            }
            $stmt = $pdo->prepare('DELETE FROM menu WHERE dish_id = :id');
            $stmt->execute([':id' => $dishId]);
        }
    }

    if (!empty($employee['edit_dishes'])) {
        $stmt = $pdo->prepare(
            'UPDATE menu SET dish_name = :name, dish_desc = :desc, dish_price = :price WHERE dish_id = :id'
        );
        foreach ((array) $rows as $row) {
            $dishId = (int) ($row['dish_id'] ?? 0);
            $name = trim((string) ($row['dish_name'] ?? ''));
            $desc = trim((string) ($row['dish_desc'] ?? ''));
            $price = $row['dish_price'] ?? '';
            

            if ($dishId <= 0 || $name === '' || mb_strlen($name) > 100) {
                json_out(['success' => false, 'message' => 'Invalid dish data.'], 400);
            }
            if (mb_strlen($desc) > 500) {
                json_out(['success' => false, 'message' => 'Description too long (max 500 chars).'], 400);
            }
            if (!is_numeric($price) || (float) $price < 0) {
                json_out(['success' => false, 'message' => 'Price must be a non-negative number.'], 400);
            }

            $stmt->execute([
                ':name' => $name,
                ':desc' => $desc,
                ':price' => (float) $price,
                ':id' => $dishId,
            ]);
        }
    }

    json_out(['success' => true, 'message' => 'Menu changes saved.']);
}

if ($section === 'employees') {
    if (empty($employee['edit_employees']) && empty($employee['delete_employees'])) {
        json_out(['success' => false, 'message' => 'Access denied to this operation.'], 403);
    }

    $rows = $payload['rows'] ?? [];
    $deleted = $payload['deleted'] ?? [];

    if (!empty($employee['delete_employees'])) {
        foreach ((array) $deleted as $empId) {
            $empId = (int) $empId;
            if ($empId <= 0) {
                continue;
            }
            if ($empId === (int) $employee['employee_id']) {
                json_out(['success' => false, 'message' => 'You cannot delete your own account.'], 409);
            }
            $stmt = $pdo->prepare('DELETE FROM employees WHERE employee_id = :id');
            $stmt->execute([':id' => $empId]);
        }
    }

    if (!empty($employee['edit_employees'])) {
        $gradeStmt = $pdo->prepare('SELECT grade_id FROM grades WHERE grade_name = :grade_name');
        $updateStmt = $pdo->prepare(
            'UPDATE employees SET employee_name = :name, employee_surname = :surname, grade_id = :grade_id WHERE employee_id = :id'
        );

        foreach ((array) $rows as $row) {
            $empId = (int) ($row['employee_id'] ?? 0);
            $name = trim((string) ($row['employee_name'] ?? ''));
            $surname = trim((string) ($row['employee_surname'] ?? ''));
            $gradeName = trim((string) ($row['grade_name'] ?? ''));

            if ($empId <= 0 || $name === '' || $surname === '' || mb_strlen($name) > 50 || mb_strlen($surname) > 50) {
                json_out(['success' => false, 'message' => 'Invalid employee data.'], 400);
            }

            $gradeStmt->execute([':grade_name' => $gradeName]);
            $gradeId = $gradeStmt->fetchColumn();
            if (!$gradeId) {
                json_out(['success' => false, 'message' => 'Grade not found: ' . $gradeName], 404);
            }

            $updateStmt->execute([
                ':name' => $name,
                ':surname' => $surname,
                ':grade_id' => $gradeId,
                ':id' => $empId,
            ]);
        }
    }

    json_out(['success' => true, 'message' => 'Employee changes saved.']);
}

if ($section === 'grades') {
    if (empty($employee['edit_grades']) && empty($employee['delete_grades'])) {
        json_out(['success' => false, 'message' => 'Access denied to this operation.'], 403);
    }

    $rows = $payload['rows'] ?? [];
    $deleted = $payload['deleted'] ?? [];
    $allowedFlags = grade_permission_flags();

    if (!empty($employee['delete_grades'])) {
        foreach ((array) $deleted as $gradeId) {
            $gradeId = (int) $gradeId;
            if ($gradeId <= 0) {
                continue;
            }
            if ($gradeId === (int) ($employee['grade_id'] ?? 0)) {
                json_out(['success' => false, 'message' => 'You cannot delete your own grade.'], 409);
            }
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE grade_id = :grade_id');
            $stmt->execute([':grade_id' => $gradeId]);
            if ((int) $stmt->fetchColumn() > 0) {
                json_out(['success' => false, 'message' => 'Cannot delete position - employees are assigned to it.'], 409);
            }
            $stmt = $pdo->prepare('DELETE FROM grades WHERE grade_id = :id');
            $stmt->execute([':id' => $gradeId]);
        }
    }

    if (!empty($employee['edit_grades'])) {
        $setParts = ['grade_name = :grade_name'];
        foreach ($allowedFlags as $flag) {
            $setParts[] = $flag . ' = :' . $flag;
        }
        $updateSql = 'UPDATE grades SET ' . implode(', ', $setParts) . ' WHERE grade_id = :grade_id';
        $updateStmt = $pdo->prepare($updateSql);

        foreach ((array) $rows as $row) {
            $gradeId = (int) ($row['grade_id'] ?? 0);
            $gradeName = trim((string) ($row['grade_name'] ?? ''));
            if ($gradeId <= 0 || $gradeName === '' || mb_strlen($gradeName) > 50) {
                json_out(['success' => false, 'message' => 'Invalid grade data.'], 400);
            }

            $stmt = $pdo->prepare('SELECT grade_id FROM grades WHERE grade_name = :grade_name AND grade_id != :grade_id');
            $stmt->execute([':grade_name' => $gradeName, ':grade_id' => $gradeId]);
            if ($stmt->fetch()) {
                json_out(['success' => false, 'message' => 'A grade with this name already exists.'], 409);
            }

            $params = [
                ':grade_name' => $gradeName,
                ':grade_id' => $gradeId,
            ];
            foreach ($allowedFlags as $flag) {
                $value = !empty($row['permissions'][$flag]) ? 1 : 0;
                if ($value === 1 && !can_grant_permission($employee, $flag)) {
                    json_out([
                        'success' => false,
                        'message' => 'You cannot grant permission "' . $flag . '" which you do not have.',
                    ], 403);
                }
                $params[':' . $flag] = $value;
            }

            if (isset($params[':config_access']) && (int) $params[':config_access'] === 0
                && (int) ($employee['grade_id'] ?? 0) === $gradeId) {
                json_out(['success' => false, 'message' => 'Cannot revoke your own configuration panel access.'], 409);
            }

            $updateStmt->execute($params);
        }
    }

    json_out(['success' => true, 'message' => 'Grade changes saved.']);
}

if ($section === 'tables') {
    if (empty($employee['edit_tables'])) {
        json_out(['success' => false, 'message' => 'Access denied to this operation.'], 403);
    }

    $floors = $payload['floors'] ?? [];
    $tables = $payload['tables'] ?? [];
    $deletedFloors = $payload['deleted_floors'] ?? [];
    $deletedTables = $payload['deleted_tables'] ?? [];

    foreach ((array) $deletedTables as $tableId) {
        $tableId = (int) $tableId;
        if ($tableId <= 0) {
            continue;
        }
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS active_count FROM table_sessions WHERE table_id = :table_id AND status IN ('open', 'to_pay')"
        );
        $stmt->execute([':table_id' => $tableId]);
        if ((int) $stmt->fetchColumn() > 0) {
            json_out(['success' => false, 'message' => 'Cannot delete a table with an active order.'], 409);
        }
        $stmt = $pdo->prepare('DELETE FROM restaurant_tables WHERE table_id = :id');
        $stmt->execute([':id' => $tableId]);
    }

    foreach ((array) $deletedFloors as $floorId) {
        $floorId = (int) $floorId;
        if ($floorId <= 0) {
            continue;
        }
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM table_sessions ts
             JOIN restaurant_tables t ON t.table_id = ts.table_id
             WHERE t.floor_id = :floor_id AND ts.status IN ('open', 'to_pay')"
        );
        $stmt->execute([':floor_id' => $floorId]);
        if ((int) $stmt->fetchColumn() > 0) {
            json_out(['success' => false, 'message' => 'Cannot delete a floor with an active order.'], 409);
        }
        $stmt = $pdo->prepare('DELETE FROM restaurant_tables WHERE floor_id = :id');
        $stmt->execute([':id' => $floorId]);
        $stmt = $pdo->prepare('DELETE FROM floors WHERE floor_id = :id');
        $stmt->execute([':id' => $floorId]);
    }

    foreach ((array) $floors as $floor) {
        $floorId = (int) ($floor['floor_id'] ?? 0);
        $floorName = trim((string) ($floor['floor_name'] ?? ''));
        if ($floorId <= 0 || $floorName === '' || mb_strlen($floorName) > 50) {
            json_out(['success' => false, 'message' => 'Invalid floor name.'], 400);
        }
        $stmt = $pdo->prepare('UPDATE floors SET floor_name = :name WHERE floor_id = :id');
        $stmt->execute([':name' => $floorName, ':id' => $floorId]);
    }

    foreach ((array) $tables as $table) {
        $tableId = (int) ($table['table_id'] ?? 0);
        $tableNumber = trim((string) ($table['table_number'] ?? ''));
        $chairs = (int) ($table['chairs'] ?? 0);
        if ($tableId <= 0 || $tableNumber === '' || mb_strlen($tableNumber) > 10) {
            json_out(['success' => false, 'message' => 'Invalid table number.'], 400);
        }
        if ($chairs < 1 || $chairs > 50) {
            json_out(['success' => false, 'message' => 'Chairs must be between 1 and 50.'], 400);
        }
        $stmt = $pdo->prepare(
            'UPDATE restaurant_tables SET table_number = :table_number, chairs = :chairs WHERE table_id = :id'
        );
        $stmt->execute([
            ':table_number' => $tableNumber,
            ':chairs' => $chairs,
            ':id' => $tableId,
        ]);
    }

    json_out(['success' => true, 'message' => 'Table changes saved.']);
}

json_out(['success' => false, 'message' => 'Unknown section.'], 400);
