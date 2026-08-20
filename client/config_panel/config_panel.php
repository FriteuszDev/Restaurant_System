<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="config_panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<?php
    $host = 'localhost';
    $db   = 'Restaurant_System';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->query("SELECT * FROM menu", PDO::FETCH_ASSOC);

        ?> <div class="menu">

        
        <table class="table_menu">
                <tr class="tr_menu">
                    <th class="th_menu">ID:</th>
                    <th class="th_menu">Name:</th>
                    <th class="th_menu">Description:</th>
                    <th class="th_menu">Price:</th>
                    <th class="th_menu">Image:</th>
                    <th class="th_menu"></th>
                </tr>
                
                <?php foreach ($stmt as $row):?>
                    <tr class="tr_menu">
                        <td class="td_menu_id"><?php echo $row['dish_id'] ?></td>
                        <td class="td_menu_name"><?php echo htmlspecialchars($row['dish_name']) ?></td>
                        <td class="td_menu"><?php echo htmlspecialchars($row['dish_desc']) ?></td>
                        <td class="td_menu"><?php echo $row['dish_price'] ?></td>
                        <td class="td_menu"><?php echo htmlspecialchars($row['dish_img']) ?></td>
                        <td class="td_menu"><i class="fa-solid fa-trash" style="color: rgb(251, 59, 59);"></i></td>
                    </tr>
                <?php endforeach;?>
                
        
        </table>
        </div>
        <table class="table_add">
            <tr>
                <th class="th_add">Name:</th>
                <th class="th_add">Description:</th>
                <th class="th_add">Price:</th>
                <th class="th_add">Image:</th>
                <th class="th_add"></th>
            </tr>
            <tr class="tr_add">
                <form method="POST" action="../../php/add_dish.php" enctype="multipart/form-data">
                    <td class="td_add"><input type="text" maxlength="30" name="name" required></td>
                    <td class="td_add"><input type="text" maxlength="50" name="desc" required></td>
                    <td class="td_add"><input type="text" name="price" inputmode="decimal" pattern="^\d{1,8}(\.\d{1,2})?$" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" required></td>
                    <td <label for="image">PNG:</label>
                    <input type="file" id="image" name="image" accept=".png" required></td>
                    <td class="td_add"><button type="submit" class="btn_dish_add">ADD DISH</button></td>
                </form>
            </tr>
        </table>
        <?php
    } catch(PDOException $e) {
        echo "Błąd połączenia: " . $e->getMessage();
    }
    ?>



    <script src="config_panel.js"></script>
</body>
</html>