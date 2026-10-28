<?php
require_once __DIR__ . '/auth.php'; require_json_access('edit_grades'); $grade_id = (int) ($_POST['grade_id'] ?? 0); $grade_name = trim($_POST['grade_name'] ?? ''); $permissions = $_POST['permissions'] ?? []; if ($grade_id <= 0) { json_out(['success' => false, 'message' => 'Invalid grade ID.'], 400); } if ($grade_name === '' || mb_strlen($grade_name) > 50) { json_out(['success' => false, 'message' => 'Grade name must be 1-50 characters.'], 400); } $allowed_flags = grade_permission_flags(); $permission_values = []; foreach ($allowed_flags as $flag) { $raw = $permissions[$flag] ?? 0; $permission_values[$flag] = ((string) $raw === '1' || $raw === 1 || $raw === true) ? 1 : 0; } $current_employee = require_login(); foreach ($permission_values as $flag => $value) { if ($value === 1 && !can_grant_permission($current_employee, $flag)) { json_out([ 'success' => false, 'message' => 'You cannot grant permission "' . htmlspecialchars($flag) . '" which you do not have.', ], 403); } } try { $pdo = db(); $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_id = :grade_id"); $stmt->execute([':grade_id' => $grade_id]); if (!$stmt->fetch()) { json_out(['success' => false, 'message' => 'Grade not found.'], 404); } $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_name = :grade_name AND grade_id != :grade_id"); $stmt->execute([':grade_name' => $grade_name, ':grade_id' => $grade_id]); if ($stmt->fetch()) { json_out(['success' => false, 'message' => 'A grade with this name already exists.'], 409); } $sql = "UPDATE grades SET
        grade_name = :grade_name,
        chef_access = :chef_access,
        waiter_access = :waiter_access,
        config_access = :config_access,
        add_dishes = :add_dishes,
        delete_dishes = :delete_dishes,
        edit_dishes = :edit_dishes,
        add_employees = :add_employees,
        delete_employees = :delete_employees,
        edit_employees = :edit_employees,
        add_grades = :add_grades,
        delete_grades = :delete_grades,
        edit_grades = :edit_grades,
        edit_tables = :edit_tables,
        manage_settings = :manage_settings
        WHERE grade_id = :grade_id"; $params = [ ':grade_name' => $grade_name, ':grade_id' => $grade_id, ]; foreach ($permission_values as $flag => $value) { $params[':' . $flag] = $value; } $stmt = $pdo->prepare($sql); $stmt->execute($params); json_out([ 'success' => true, 'message' => 'Grade updated.', 'grade' => array_merge(['grade_id' => $grade_id, 'grade_name' => $grade_name], $permission_values), ]); } catch (PDOException $e) { json_out(['success' => false, 'message' => 'Error updating grade.'], 500); } exit();