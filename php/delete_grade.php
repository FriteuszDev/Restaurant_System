<?php
require_once __DIR__ . '/auth.php';

require_json_access('delete_grades');

$GradeIdToDelete = $_POST['gradeID'] ?? 'None';

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE grade_id = :grade_id");
    $stmt->execute([':grade_id' => $GradeIdToDelete]);

    if ((int) $stmt->fetchColumn() > 0) {
        json_out([
            'success' => false,
            'message' => 'Cannot delete position - employees are assigned to it.',
        ], 409);
    }

    $sql = "DELETE FROM grades WHERE grade_id = :grade_id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'grade_id' => $GradeIdToDelete
    ]);

    json_out(['success' => true, 'message' => 'Position deleted.']);
} catch (PDOException $e) {
    json_out(['success' => false, 'message' => 'Error deleting position.'], 500);
}

exit();
