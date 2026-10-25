<?php
require_once __DIR__ . '/../../php/auth.php';

$employee = require_panel_access('config_access');

$can = [
    'config_access' => !empty($employee['config_access']),
    'chef_access' => !empty($employee['chef_access']),
    'waiter_access' => !empty($employee['waiter_access']),
    'add_dishes' => !empty($employee['add_dishes']),
    'delete_dishes' => !empty($employee['delete_dishes']),
    'edit_dishes' => !empty($employee['edit_dishes']),
    'add_employees' => !empty($employee['add_employees']),
    'delete_employees' => !empty($employee['delete_employees']),
    'edit_employees' => !empty($employee['edit_employees']),
    'add_grades' => !empty($employee['add_grades']),
    'delete_grades' => !empty($employee['delete_grades']),
    'edit_grades' => !empty($employee['edit_grades']),
    'edit_tables' => !empty($employee['edit_tables']),
    'manage_settings' => !empty($employee['manage_settings']),
];

$host = 'localhost';
$db   = 'Restaurant_System';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $all_dishes = $pdo->query("SELECT * FROM menu")->fetchAll(PDO::FETCH_ASSOC);
    $all_employees = $pdo->query("SELECT * FROM employees")->fetchAll(PDO::FETCH_ASSOC);
    $all_grades = $pdo->query("SELECT * FROM grades")->fetchAll(PDO::FETCH_ASSOC);

    $floors = $pdo->query("SELECT * FROM floors ORDER BY floor_name")->fetchAll(PDO::FETCH_ASSOC);
    $all_tables = $pdo->query("SELECT * FROM restaurant_tables ORDER BY table_number")->fetchAll(PDO::FETCH_ASSOC);

    $tables_by_floor = [];
    foreach ($all_tables as $table_row) {
        $tables_by_floor[(int) $table_row['floor_id']][] = $table_row;
    }

    $settings = [];
    $setting_rows = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($setting_rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    // Kontrakt logo - jedno zrodlo prawdy (settings.setting_value dla 'restaurant_logo').
    $logo_path = get_logo_path();
    $logo_src = logo_url($logo_path);
    $has_custom_logo = $logo_path !== null && $logo_path !== '';

    $employee_full_name = htmlspecialchars(employee_full_name($employee));
    $employee_grade = htmlspecialchars($employee['grade_name'] ?? 'no position');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo htmlspecialchars($logo_src) ?>">
    <title><?php echo htmlspecialchars($settings['restaurant_name'] ?? 'Restaurant System') ?> - Config Panel</title>
    <link rel="stylesheet" href="config_panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
        <div class="tabs_container">
                <div class="tab_menu active" data-tab="menu"><span><i class="fa-solid fa-utensils"></i></span> Menu</div>
                <div class="tab_employees" data-tab="employees"><span><i class="fa-solid fa-users-gear"></i></span> Employees</div>
                <div class="tab_grades" data-tab="grades"><span><i class="fa-solid fa-user-shield"></i></span> Grades</div>
                <div class="tab_tables" data-tab="tables"><span><i class="fa-solid fa-table-cells"></i></span> Tables</div>
                <?php if ($can['manage_settings']): ?>
                <div class="tab_settings" data-tab="settings"><span><i class="fa-solid fa-cog"></i></span> Settings</div>
                <?php endif; ?>

                <div class="user_bar">
                    <div class="user_info">
                        <img src="<?php echo htmlspecialchars($logo_src) ?>" alt="Restaurant Logo" class="logo config_top_logo">
                        <i class="fa-solid fa-user"></i>
                        <span class="user_name"><?php echo $employee_full_name ?></span> <span class="user_grade">(<?php echo $employee_grade ?>)</span>
                    </div>
                    <div class="user_links">
                        <?php if ($can['waiter_access']): ?>
                            <a href="../waiter_panel/waiter_panel.php"><i class="fa-solid fa-bell-concierge"></i> Waiter</a>
                        <?php endif; ?>
                        <?php if ($can['chef_access']): ?>
                            <a href="../chef_panel/chef_panel.php"><i class="fa-solid fa-fire-burner"></i> Chef</a>
                        <?php endif; ?>
                        <a href="../../php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
        </div>

        <div class="config_menu is-active">
            <div class="menu">

            <!-- Menu Edit Mode Toolbar -->
            <?php if ($can['edit_dishes'] || $can['delete_dishes']): ?>
            <div class="edit_toolbar">
                <button type="button" id="btn_edit_mode_menu" class="btn_edit_mode" data-section="menu">
                    <i class="fa-solid fa-pen"></i> Edit Mode
                </button>
                <button type="button" id="btn_save_menu" class="btn_save_changes" data-section="menu" style="display:none;">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <button type="button" id="btn_cancel_menu" class="btn_cancel_changes" data-section="menu" style="display:none;">
                    <i class="fa-solid fa-ban"></i> Cancel
                </button>
                <button type="button" id="btn_undo_menu" class="btn_undo_redo" data-section="menu" data-action="undo" style="display:none;">
                    <i class="fa-solid fa-rotate-left"></i> Undo
                </button>
                <button type="button" id="btn_redo_menu" class="btn_undo_redo" data-section="menu" data-action="redo" style="display:none;">
                    <i class="fa-solid fa-rotate-right"></i> Redo
                </button>
                <span id="edit_hint_menu" class="edit_hint" style="display:none; color: rgb(180, 100, 0); font-size: 85%; font-weight: 700;">
                    Unsaved changes - save or cancel before leaving Edit Mode
                </span>
            </div>
            <?php endif; ?>

            <table class="table_menu">
                        <th class="th_menu">ID:</th>
                        <th class="th_menu">Name:</th>
                        <th class="th_menu">Description:</th>
                        <th class="th_menu">Price:</th>
                        
                        <?php if ($can['delete_dishes']): ?>
                        <th class="th_menu"></th>
                        <?php endif; ?>
                    </tr>

                    <?php foreach ($all_dishes as $row):?>
                        <tr class="tr_menu" data-dish-id="<?php echo (int) $row['dish_id'] ?>">
                            <td class="td_menu_id"><?php echo $row['dish_id'] ?></td>
                            <td class="td_menu_name" data-field="dish_name"><?php echo htmlspecialchars((string) $row['dish_name']) ?></td>
                            <td class="td_menu" data-field="dish_desc"><?php echo htmlspecialchars((string) ($row['dish_desc'] ?? '')) ?></td>
                            <td class="td_menu" data-field="dish_price"><?php echo htmlspecialchars((string) $row['dish_price']) ?></td>
<?php if ($can['delete_dishes']): ?>
<td class="td_menu"><i class="fa-solid fa-trash dish_delete" style="color: rgb(251, 59, 59);" title="Delete dish (Edit Mode required)"></i></td>
<?php endif; ?>

                        </tr>
                    <?php endforeach; ?>


            </table>
            </div>
            <?php if ($can['add_dishes']): ?>
            <form id="add_dish_form" method="POST" action="../../php/add_dish.php" enctype="multipart/form-data" class="ajax_add_form" data-add-type="dish">
            <table class="table_add">
                <tr>
                    <th class="th_add">Name:</th>
                    <th class="th_add">Description:</th>
                    <th class="th_add">Price:</th>
                    
                    <th class="th_add"></th>
                </tr>
                <tr class="tr_add">
                        <td class="td_add"><input type="text" maxlength="100" name="dish_name" required></td>
                        <td class="td_add"><input type="text" maxlength="500" name="dish_desc" required></td>
                        <td class="td_add"><input type="text" name="dish_price" inputmode="decimal" pattern="^\d{1,8}(\.\d{1,2})?$" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder="np. 10.50" required></td>
                        <td class="td_add"></td>
                        <td class="td_add"><button type="submit" class="btn_dish_add">ADD DISH</button></td>
                </tr>
            </table>
            </form>
            <?php endif; ?>
                        </div>
                    </div>

        <div class="config_employees" data-tab="employees">
            <div class="employees">

            <!-- Employees Edit Mode Toolbar -->
            <?php if ($can['edit_employees'] || $can['delete_employees']): ?>
            <div class="edit_toolbar">
                <button type="button" id="btn_edit_mode_employees" class="btn_edit_mode" data-section="employees">
                    <i class="fa-solid fa-pen"></i> Edit Mode
                </button>
                <button type="button" id="btn_save_employees" class="btn_save_changes" data-section="employees" style="display:none;">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <button type="button" id="btn_cancel_employees" class="btn_cancel_changes" data-section="employees" style="display:none;">
                    <i class="fa-solid fa-ban"></i> Cancel
                </button>
                <button type="button" id="btn_undo_employees" class="btn_undo_redo" data-section="employees" data-action="undo" style="display:none;">
                    <i class="fa-solid fa-rotate-left"></i> Undo
                </button>
                <button type="button" id="btn_redo_employees" class="btn_undo_redo" data-section="employees" data-action="redo" style="display:none;">
                    <i class="fa-solid fa-rotate-right"></i> Redo
                </button>
                <span id="edit_hint_employees" class="edit_hint" style="display:none; color: rgb(180, 100, 0); font-size: 85%; font-weight: 700;">
                    Unsaved changes - save or cancel before leaving Edit Mode
                </span>
            </div>
            <?php endif; ?>

            <table class="table_employees">
                    <tr class="tr_employees">
                        <th class="th_employees">ID:</th>
                        <th class="th_employees">Name:</th>
                        <th class="th_employees th_employees_surname">Surname:</th>
                        <th class="th_employees th_employees_grade">Grade:</th>
                        <?php if ($can['delete_employees']): ?>
                        <th class="th_employees th_employees_delete"></th>
                        <?php endif; ?>
                    </tr>

                    <?php foreach ($all_employees as $row):?>
                        <tr class="tr_employees" data-employee-id="<?php echo (int) $row['employee_id'] ?>">
                            <td class="td_employees_id"><?php echo $row['employee_id'] ?></td>
                            <td class="td_employees_name" data-field="employee_name"><?php echo htmlspecialchars($row['employee_name']) ?></td>
                            <td class="td_employees_surname" data-field="employee_surname"><?php echo htmlspecialchars($row['employee_surname']) ?></td>
                            <td class="td_employees_grade" data-field="grade_name">
                                <select class="emp_grade_select" data-field="grade_name" disabled>
                                    <?php foreach ($all_grades as $grade): ?>
                                    <option value="<?php echo htmlspecialchars($grade['grade_name']) ?>"<?php echo ($grade['grade_id'] == $row['grade_id']) ? ' selected' : '' ?>><?php echo htmlspecialchars($grade['grade_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <?php if ($can['delete_employees']): ?>
                            <td class="td_employees"><i class="fa-solid fa-user-minus employee_delete" style="color: rgb(251, 59, 59);" title="Delete employee (Edit Mode required)"></i></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>


            </table>
            </div>

            <?php if ($can['add_employees']): ?>
            <form id="add_employee_form" method="POST" action="../../php/add_employee.php" class="ajax_add_form" data-add-type="employee">
            <table class="table_add">
                <tr>
                    <th class="th_add">Name:</th>
                    <th class="th_add">Surname:</th>
                    <th class="th_add">Password:</th>
                    <th class="th_add">Grade:</th>
                    <th class="th_add"></th>
                </tr>
                <tr class="tr_add">
                        <td class="td_add"><input type="text" maxlength="20" name="name" required></td>
                        <td class="td_add"><input type="text" maxlength="20" name="surname" required></td>
                        <td class="td_add"><input type="password" name="password" minlength="4" required></td>
                        <td class="td_add"><label for="grade">Select Grade:</label>
                                            <select id="grade" name="grade" required>
                                            <?php foreach ($all_grades as $row):?>
                                                <option value="<?php echo htmlspecialchars($row['grade_name']) ?>"><?php echo htmlspecialchars($row['grade_name']) ?></option>
                                            <?php endforeach; ?>
                                            </select></td>
                        <td class="td_add"><button type="submit" class="btn_dish_add">ADD EMPLOYEE</button></td>
                </tr>
            </table>
            </form>
            <?php endif; ?>
        </div>

        <div class="config_grades" data-tab="grades">
            <div class="grades">

            <?php $access_icon = function ($value) {
                $on = ((int) $value) === 1;
                return $on
                    ? '<i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i>'
                    : '<i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i>';
            } ?>

            <!-- Grades Edit Mode Toolbar -->
            <?php if ($can['edit_grades'] || $can['delete_grades']): ?>
            <div class="edit_toolbar">
                <button type="button" id="btn_edit_mode_grades" class="btn_edit_mode" data-section="grades">
                    <i class="fa-solid fa-pen"></i> Edit Mode
                </button>
                <button type="button" id="btn_save_grades" class="btn_save_changes" data-section="grades" style="display:none;">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <button type="button" id="btn_cancel_grades" class="btn_cancel_changes" data-section="grades" style="display:none;">
                    <i class="fa-solid fa-ban"></i> Cancel
                </button>
                <button type="button" id="btn_undo_grades" class="btn_undo_redo" data-section="grades" data-action="undo" style="display:none;">
                    <i class="fa-solid fa-rotate-left"></i> Undo
                </button>
                <button type="button" id="btn_redo_grades" class="btn_undo_redo" data-section="grades" data-action="redo" style="display:none;">
                    <i class="fa-solid fa-rotate-right"></i> Redo
                </button>
                <span id="edit_hint_grades" class="edit_hint" style="display:none; color: rgb(180, 100, 0); font-size: 85%; font-weight: 700;">
                    Unsaved changes - save or cancel before leaving Edit Mode
                </span>
            </div>
            <?php endif; ?>

            <table class="table_grades">
                    <tr class="tr_grades">
                        <th class="th_grades">ID:</th>
                        <th class="th_grades">Name:</th>
                        <th class="th_grades">Chef Panel Access:</th>
                        <th class="th_grades">Waiter Panel Access:</th>
                        <th class="th_grades">Config Panel Access:</th>
                        <th class="th_grades">Add Dishes:</th>
                        <th class="th_grades">Delete Dishes:</th>
                        <th class="th_grades">Edit Dishes:</th>
                        <th class="th_grades">Add Employees:</th>
                        <th class="th_grades">Delete Employees:</th>
                        <th class="th_grades">Edit Employees:</th>
                        <th class="th_grades">Add Grades:</th>
                        <th class="th_grades">Delete Grades:</th>
                        <th class="th_grades">Edit Grades:</th>
                        <th class="th_grades">Edit Tables:</th>
                        <th class="th_grades">Manage Settings:</th>
                    <th class="th_grades"></th>
                    </tr>

                    <?php foreach ($all_grades as $row):?>
                        <tr class="tr_grades" data-grade-id="<?php echo (int) $row['grade_id'] ?>">
                            <td class="td_grade_id"><?php echo $row['grade_id'] ?></td>
                            <td class="td_grade_name" data-field="grade_name"><?php echo htmlspecialchars($row['grade_name']) ?></td>
                            <?php
                            $grade_flags = [
                                'chef_access', 'waiter_access', 'config_access',
                                'add_dishes', 'delete_dishes', 'edit_dishes',
                                'add_employees', 'delete_employees', 'edit_employees',
                                'add_grades', 'delete_grades', 'edit_grades',
                                'edit_tables', 'manage_settings',
                            ];
                            foreach ($grade_flags as $flag):
                                $flag_value = (int) ($row[$flag] ?? 0);
                            ?>
                            <td class="td_grades" data-flag="<?php echo htmlspecialchars($flag) ?>" data-value="<?php echo $flag_value ?>"><?php echo $access_icon($flag_value) ?></td>
                            <?php endforeach; ?>

                            <?php if ($can['delete_grades']): ?>
                            <td class="td_grades"><i class="fa-solid fa-trash-can grade_delete" style="color: rgb(222, 36, 36);" title="Delete grade (Edit Mode required)"></i></td>
                            <?php else: ?>
                            <td class="td_grades"></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>


            </table>
            </div>
            <?php if ($can['add_grades']): ?>
            <form id="add_grade_form" method="POST" action="../../php/add_grade.php" class="ajax_add_form" data-add-type="grade">
            <table class="table_add">
                <tr>
                        <th class="th_grades">Name:</th>
                        <th class="th_grades">Chef Panel Access:</th>
                        <th class="th_grades">Waiter Panel Access:</th>
                        <th class="th_grades">Config Panel Access:</th>
                        <th class="th_grades">Add Dishes:</th>
                        <th class="th_grades">Delete Dishes:</th>
                        <th class="th_grades">Edit Dishes:</th>
                        <th class="th_grades">Add Employees:</th>
                        <th class="th_grades">Delete Employees:</th>
                        <th class="th_grades">Edit Employees:</th>
                        <th class="th_grades">Add Grades:</th>
                        <th class="th_grades">Delete Grades:</th>
                        <th class="th_grades">Edit Grades:</th>
                        <th class="th_grades">Edit Tables:</th>
                        <th class="th_grades">Manage Settings:</th>
                    <th class="th_add"></th>
                </tr>
                <tr class="tr_add">
                        <td class="td_add"><input type="text" maxlength="15" name="name" required></td>
                        <td class="td_add"><input type="checkbox" name="chef_access" value="1"></td>
                        <td class="td_add"><input type="checkbox" name="waiter_access" value="1"></td>
                        <td class="td_add"><input type="checkbox" name="config_access" value="1" onchange="uncheckAll(this)" class="config_auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="add_dishes" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="delete_dishes" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="edit_dishes" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="add_employees" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="delete_employees" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="edit_employees" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="add_grades" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="delete_grades" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="edit_grades" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="edit_tables" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><input type="checkbox" name="manage_settings" value="1" onchange="CheckBox(this)" class="auto_change"></td>
                        <td class="td_add"><button type="submit" class="btn_dish_add">ADD GRADE</button></td>
                </tr>
            </table>
            </form>
            <?php endif; ?>
        </div>

        <div class="config_tables" data-tab="tables">
            <?php if ($can['edit_tables']): ?>
            <div class="edit_toolbar">
                <button type="button" id="btn_edit_mode_tables" class="btn_edit_mode" data-section="tables">
                    <i class="fa-solid fa-pen"></i> Edit Mode
                </button>
                <button type="button" id="btn_save_tables" class="btn_save_changes" data-section="tables" style="display:none;">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <button type="button" id="btn_cancel_tables" class="btn_cancel_changes" data-section="tables" style="display:none;">
                    <i class="fa-solid fa-ban"></i> Cancel
                </button>
                <button type="button" id="btn_undo_tables" class="btn_undo_redo" data-section="tables" data-action="undo" style="display:none;">
                    <i class="fa-solid fa-rotate-left"></i> Undo
                </button>
                <button type="button" id="btn_redo_tables" class="btn_undo_redo" data-section="tables" data-action="redo" style="display:none;">
                    <i class="fa-solid fa-rotate-right"></i> Redo
                </button>
                <span id="edit_hint_tables" class="edit_hint" style="display:none; color: rgb(180, 100, 0); font-size: 85%; font-weight: 700;">
                    Unsaved changes - save or cancel before leaving Edit Mode
                </span>
            </div>
            <div class="tables_toolbar">
                <div class="toolbar_box">
                    <span class="toolbar_title"><i class="fa-solid fa-layer-group"></i> New Floor</span>
                    <input type="text" id="floor_name_input" maxlength="30" placeholder="e.g. Ground Floor">
                    <button type="button" id="btn_add_floor">ADD FLOOR</button>
                </div>

                <div class="toolbar_box">
                    <span class="toolbar_title"><i class="fa-solid fa-table-cells"></i> New Table</span>
                    <select id="table_floor_select">
                        <?php foreach ($floors as $floor):?>
                        <option value="<?php echo (int) $floor['floor_id'] ?>"><?php echo htmlspecialchars($floor['floor_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="table_number_input" maxlength="10" placeholder="Numer">
                    <input type="number" id="table_chairs_input" min="1" max="50" value="4" placeholder="Chairs">
                    <button type="button" id="btn_add_table">ADD TABLE</button>
                </div>
            </div>

            <div id="floors_container" class="floors_container">
                <?php if (!$floors):?>
                <div class="floors_empty" id="floors_empty">No floors - add the first floor above.</div>
                <?php endif; ?>
                <?php foreach ($floors as $floor):?>
                <div class="floor_block" data-floor-id="<?php echo (int) $floor['floor_id'] ?>">
                    <div class="floor_header">
                        <span class="floor_name" data-field="floor_name"><i class="fa-solid fa-building"></i> <span class="floor_name_text"><?php echo htmlspecialchars($floor['floor_name']) ?></span></span>
                        <span class="floor_count"><?php echo count($tables_by_floor[(int) $floor['floor_id']] ?? []) ?> tables</span>
                        <i class="fa-solid fa-trash floor_delete" title="Delete floor"></i>
                    </div>
                    <div class="floor_tables">
                        <?php foreach ($tables_by_floor[(int) $floor['floor_id']] ?? [] as $table):?>
                        <div class="table_card status_<?php echo htmlspecialchars($table['status'] === 'active' ? 'free' : $table['status']) ?>" data-table-id="<?php echo (int) $table['table_id'] ?>">
                            <div class="table_card_number" data-field="table_number">#<?php echo htmlspecialchars((string) $table['table_number']) ?></div>
                            <div class="table_card_chairs" data-field="chairs"><i class="fa-solid fa-chair"></i> <span class="chairs_value"><?php echo (int) $table['chairs'] ?></span></div>
                            <i class="fa-solid fa-trash table_delete" title="Delete table"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no_access_message">
                <i class="fa-solid fa-lock"></i>
                You do not have <b>edit_tables</b> permission to edit tables and floors.
            </div>
            <?php endif; ?>
        </div>

                <?php if ($can['manage_settings']): ?>
        <div class="config_settings" data-tab="settings">
            <div class="settings">
                <h2>Restaurant Settings</h2>
                <form id="restaurant_name_form" method="POST" action="../../php/update_setting.php" class="ajax_form" data-setting-type="name">
                    <label>Restaurant Name:</label>
                    <input type="text" name="key" value="restaurant_name" hidden>
                    <input type="text" name="value" value="<?php echo htmlspecialchars($settings['restaurant_name'] ?? 'Restaurant System') ?>" maxlength="100" required>
                    <button type="submit" class="btn_setting_save">Save Name</button>
                </form>
                <form id="restaurant_logo_form" method="POST" action="../../php/upload_logo.php" enctype="multipart/form-data" class="ajax_form" data-setting-type="logo">
                    <label>Restaurant Logo:</label>
                    <input type="file" id="upload_logo_input" name="logo" accept="image/png, image/jpeg, image/gif, image/webp" required>
                    <input type="hidden" id="setting_restaurant_logo" name="setting_restaurant_logo" value="">
                    <button type="submit" class="btn_setting_save">Upload Logo</button>
                </form>
                <div class="current_logo settings_logo_preview">
                    <img src="<?php echo htmlspecialchars($logo_src) ?>" alt="Current Logo" class="logo_preview" id="current_logo_img">
                </div>
                <?php if (!$has_custom_logo): ?>
                <p class="logo_hint">Using the default logo. Upload a file to replace it.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div id="config_toasts"></div>

    <script>
        window.CAN = <?php echo json_encode($can, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        window.ALL_GRADES = <?php echo json_encode(array_values($all_grades), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="config_panel.js"></script>
</body>
</html>
    <?php
} catch(PDOException $e) {
    echo "Connection error: " . $e->getMessage();
}
?>




