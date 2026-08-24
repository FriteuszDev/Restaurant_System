<?php
$host = 'localhost';
$db = 'Restaurant_System';
$user = 'root';
$pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $GradeIdToDelete = $_POST['gradeID'] ?? 'None';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE FROM grades WHERE grade_id = :grade_id";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'grade_id' => $GradeIdToDelete
        ]);

        echo "grade deleted";

    } catch (PDOException $e) {
        die("blad polaczenia " . $e->getMessage());
    }

    exit();
}
?>