<?php
$host = 'localhost';
$db = 'Restaurant_System';
$user = 'root';
$pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $password = $_POST['password'];
    $grade = $_POST['grade'];


    $password_coded = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE grade_name = :grade_name");
        $stmt->execute([':grade_name' => $grade]);
        $grade_id = $stmt->fetchColumn();


        $sql2 = "INSERT INTO employees (employee_name, employee_surname, employee_password, grade_id) VALUES (:e_name, :e_surname, :e_password, :e_gradeid)";
        $stmt2 = $pdo->prepare($sql2);

        $stmt2->execute([
            'e_name' => $name,
            'e_surname' => $surname,
            'e_password' => $password_coded,
            'e_gradeid' => $grade_id,
        ]);

        echo "added dish";

    } catch (PDOException $e) {
        die("blad polaczenia " . $e->getMessage());
    }

    header("Location: ../client/config_panel/config_panel.php");
    exit();
}
?>