<?php
$host = 'localhost';
$db = 'Restaurant_System';
$user = 'root';
$pass = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'])) {

$name = $_POST['name'];

$chef_access = isset($_POST['chef_access']) ? (int)$_POST['chef_access'] : 0;
$waiter_access = isset($_POST['waiter_access']) ? (int)$_POST['waiter_access'] : 0;
$config_access = isset($_POST['config_access']) ? (int)$_POST['config_access'] : 0;
$delete_dishes = isset($_POST['delete_dishes']) ? (int)$_POST['delete_dishes'] : 0;
$add_dishes = isset($_POST['add_dishes']) ? (int)$_POST['add_dishes'] : 0;
$delete_employees = isset($_POST['delete_employees']) ? (int)$_POST['delete_employees'] : 0;
$add_employees = isset($_POST['add_employees']) ? (int)$_POST['add_employees'] : 0;
$delete_grades = isset($_POST['delete_grades']) ? (int)$_POST['delete_grades'] : 0;
$add_grades = isset($_POST['add_grades']) ? (int)$_POST['add_grades'] : 0;
$edit_tables = isset($_POST['edit_tables']) ? (int)$_POST['edit_tables'] : 0;



    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $sql = "INSERT INTO grades (grade_name, chef_access, waiter_access, config_access, delete_dishes, add_dishes, delete_employees, add_employees, delete_grades, add_grades, edit_tables) VALUES (:grade_name, :chef_access, :waiter_access, :config_access, :delete_dishes, :add_dishes, :delete_employees, :add_employees, :delete_grades, :add_grades, :edit_tables)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'grade_name' => $name,
            'chef_access' => $chef_access,
            'waiter_access' => $waiter_access,
            'config_access' => $config_access,
            'delete_dishes' => $delete_dishes,
            'add_dishes' => $add_dishes,
            'delete_employees' => $delete_employees,
            'add_employees' => $add_employees,
            'delete_grades' => $delete_grades,
            'add_grades' => $add_grades,
            'edit_tables' => $edit_tables,
        ]);

        echo "added grade";

    } catch (PDOException $e) {
        die("blad polaczenia " . $e->getMessage());
    }

    header("Location: ../client/config_panel/config_panel.php");
    exit();
}
?>