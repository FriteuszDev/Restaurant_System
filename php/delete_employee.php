<?php
$host = 'localhost';
$db = 'Restaurant_System';
$user = 'root';
$pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $EmployeeIdToDelete = $_POST['emID'] ?? 'None';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE FROM employees WHERE employee_id = :employee_id";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'employee_id' => $EmployeeIdToDelete
        ]);

        echo "fired employee";

    } catch (PDOException $e) {
        die("blad polaczenia " . $e->getMessage());
    }

    exit();
}
?>