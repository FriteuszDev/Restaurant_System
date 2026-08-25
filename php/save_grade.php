<?php
$host = 'localhost';
$db = 'Restaurant_System';
$user = 'root';
$pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $emp_id = isset($_POST['emp_id']) ? $_POST['emp_id'] : null;
    $new_grade_name = isset($_POST['new_grade_name']) ? $_POST['new_grade_name'] : null;
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_name = :grade_name");
        $stmt->execute(['grade_name' => $new_grade_name]);
        $new_grade_id = $stmt->fetchColumn();

        if ($new_grade_id) {
                $sql = "UPDATE employees SET grade_id = :grade_id WHERE employee_id = :employee_id";
                $stmt2 = $pdo->prepare($sql);
                $stmt2->execute([
                    ':grade_id' => $new_grade_id,
                    ':employee_id' => $emp_id
                ]);

        echo "new grade saved";
        }
    } catch (PDOException $e) {
        die("blad polaczenia " . $e->getMessage());
    }

    exit();
}
?>