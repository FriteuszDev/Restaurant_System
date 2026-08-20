<?php
$host = 'localhost';
$db = 'Restaurant_System';
$user = 'root';
$pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $desc = $_POST['desc'];
    $price = $_POST['price'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $FileName = $_FILES['image']['name'];

        $SaveInFolder = '../client/images/';
        $SaveIn = $SaveInFolder . basename($FileName);
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $SaveIn)) {
            echo "Saved Image!";
        } else {
            echo "Coulndnt Save Image";
        }
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO menu (dish_name, dish_desc, dish_price, dish_img) VALUES (:dish_name, :dish_desc, :dish_price, :dish_img)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'dish_name' => $name,
            'dish_desc' => $desc,
            'dish_price' => $price,
            'dish_img' => $FileName,
        ]);

        echo "added dish";

    } catch (PDOException $e) {
        die("blad polaczenia " . $e->getMessage());
    }

    header("Location: ../client/config_panel/config_panel.php");
    exit();
}
?>