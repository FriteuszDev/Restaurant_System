<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant System - Config Panel</title>
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
        $stmt2 = $pdo->query("SELECT * FROM employees", PDO::FETCH_ASSOC);
        $stmt3 = $pdo->query("SELECT * FROM grades", PDO::FETCH_ASSOC);
        $stmt5 = $pdo->query("SELECT * FROM grades", PDO::FETCH_ASSOC);
        
        

        ?>
        <div class="tabs_container">
                <div class="tab_menu"><span><i class="fa-solid fa-utensils"></i></span> Menu</div>
                <div class="tab_employees"><span><i class="fa-solid fa-users-gear"></i></span> Employees</div>
                <div class="tab_tables"><span><i class="fa-solid fa-table-cells"></i></span> Tables</div>
                <div class="tab_grades"><span><i class="fa-solid fa-user-shield"></i></span> Grades</div>
        </div>
        
        
        
        
        
        <div class="config_menu"> 
            <div class="menu">

            
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
        </div>




        <div class="config_employees">
            <div class="employees">

            
            <table class="table_employees">
                    <tr class="tr_employees">
                        <th class="th_employees">ID:</th>
                        <th class="th_employees">Name:</th>
                        <th class="th_employees">Surname:</th>
                        <th class="th_employees">Grade:</th>
                        <th class="th_employees"></th>
                    </tr>
                    
                    <?php foreach ($stmt2 as $row):?>
                        <tr class="tr_employees">
                            <td class="td_employees_id"><?php echo $row['employee_id'] ?></td>
                            <td class="td_employees_name"><?php echo htmlspecialchars($row['employee_name']) ?></td>
                            <td class="td_employees"><?php echo htmlspecialchars($row['employee_surname']) ?></td>
                            <?php $stmt4 = $pdo->prepare("SELECT grade_name FROM grades WHERE grade_id = :grade_id_fe"); 
                                $stmt4->execute([':grade_id_fe' => $row['grade_id']]);
                                $grade = $stmt4->fetchColumn();?>
                            <td class="td_employees"><?php echo $grade ?></td>
                            <td class="td_employees"><i class="fa-solid fa-user-minus" style="color: rgb(251, 59, 59);"></i></td>
                        </tr>
                    <?php endforeach;?>
                    
            
            </table>
            </div>
            <table class="table_add">
                <tr>
                    <th class="th_add">Name:</th>
                    <th class="th_add">Surname:</th>
                    <th class="th_add">Password:</th>
                    <th class="th_add">Grade:</th>
                    <th class="th_add"></th>
                </tr>
                <tr class="tr_add">
                    <form method="POST" action="../../php/add_employee.php" enctype="multipart/form-data">
                        <td class="td_add"><input type="text" maxlength="15" name="name" required></td>
                        <td class="td_add"><input type="text" maxlength="25" name="surname" required></td>
                        <td class="td_add"><input type="password" name="password" required></td>
                        <td class="td_add"><label for="grade">Select Grade:</label>
                                            <select id="grade" name="grade" required>
                                            <?php foreach ($stmt3 as $row):?>
                                                <option value="<?php echo htmlspecialchars($row['grade_name']) ?>"><?php echo htmlspecialchars($row['grade_name']) ?></option>
                                            <?php endforeach; ?>
                                            </select></td>
                        <td class="td_add"><button type="submit" class="btn_dish_add">ADD EMPLOYEE</button></td>
                    </form>
                </tr>
            </table>
        </div>

        <div class="config_tables">

        </div>









        <div class="config_grades">
            <div class="grades">

            
            <table class="table_grades">
                    <tr class="tr_grades">
                        <th class="th_grades">ID:</th>
                        <th class="th_grades">Name:</th>
                        <th class="th_grades">Chef Panel Access:</th>
                        <th class="th_grades">Waiter Panel Access:</th>
                        <th class="th_grades">Config Panel Access:</th>
                        <th class="th_grades">Add Dishes:</th>
                        <th class="th_grades">Delete Dishes:</th>
                        <th class="th_grades">Add Employees:</th>
                        <th class="th_grades">Delete Employees:</th>
                        <th class="th_grades">Add Grades:</th>
                        <th class="th_grades">Delete Grades:</th>
                        <th class="th_grades">Edit Tables:</th>
                        <th class="th_grades"></th>
                    </tr>
                    
                    <?php foreach ($stmt5 as $row):?>
                        <tr class="tr_grades">
                            <td class="td_grade_id"><?php echo $row['grade_id'] ?></td>
                            <td class="td_grade_name"><?php echo htmlspecialchars($row['grade_name']) ?></td>

                                <?php if($row['chef_access'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                                <?php if($row['waiter_access'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>
                    
                                <?php if($row['config_access'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                                <?php if($row['add_dishes'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                                <?php if($row['delete_dishes'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                                <?php if($row['add_employees'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                                <?php if($row['delete_employees'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                                <?php if($row['add_grades'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                                <?php if($row['delete_grades'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>
                    
                                <?php if($row['edit_tables'] == 1): ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i></td>
                                <?php else: ?>
                            <td class="td_grades"><i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i></td>
                                <?php endif; ?>

                            <td class="td_grades"><i class="fa-solid fa-trash-can" style="color: rgb(222, 36, 36);"></i></td>
                        </tr>
                    <?php endforeach;?>
                    
            
            </table>
            </div>
            <table class="table_add">
                <tr>
                        <th class="th_grades">Name:</th>
                        <th class="th_grades">Chef Panel Access:</th>
                        <th class="th_grades">Waiter Panel Access:</th>
                        <th class="th_grades">Config Panel Access:</th>
                        <th class="th_grades">Add Dishes:</th>
                        <th class="th_grades">Delete Dishes:</th>
                        <th class="th_grades">Add Employees:</th>
                        <th class="th_grades">Delete Employees:</th>
                        <th class="th_grades">Add Grades:</th>
                        <th class="th_grades">Delete Grades:</th>
                        <th class="th_grades">Edit Tables:</th>
                    <th class="th_add"></th>
                </tr>
                <tr class="tr_add">
                    <form method="POST" action="../../php/add_grade.php">
                        <td class="td_add"><input type="text" maxlength="25" name="name" required></td>
                        <td class="td_add"><input type="checkbox" name="chef_access"></td>
                        <td class="td_add"><input type="checkbox" name="waiter_access"></td>
                        <td class="td_add"><input type="checkbox" name="config_access" onchange="uncheckAll(this)" class="config_auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="add_dishes" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="delete_dishes" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="add_employees" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="delete_employees" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="add_grades" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="delete_grades" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="edit_tables" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><button type="submit" class="btn_dish_add">ADD GRADE</button></td>
                    </form>
                </tr>
            </table>
        </div>


        <?php
    } catch(PDOException $e) {
        echo "Błąd połączenia: " . $e->getMessage();
    }
    ?>



    <script src="config_panel.js"></script>
</body>
</html>