<?php
$host = 'localhost';
$db = 'Restaurant_System';
$user = 'root';
$pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $DishIdToDelete = $_POST['DishID'] ?? 'None';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE FROM menu WHERE dish_id = :dish_id";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'dish_id' => $DishIdToDelete
        ]);

        echo "deleted dish";

    } catch (PDOException $e) {
        die("blad polaczenia " . $e->getMessage());
    }

    exit();
}
?>