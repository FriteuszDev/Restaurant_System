<?php
require_once __DIR__ . '/../../php/auth.php';

$employee = require_panel_access('chef_access');

$can = [
    'config_access' => !empty($employee['config_access']),
    'waiter_access' => !empty($employee['waiter_access']),
];

$employee_full_name = htmlspecialchars(employee_full_name($employee));

// Kontrakt logo - jedno zrodlo prawdy (settings.setting_value dla 'restaurant_logo').
$logo_src = logo_url();

$history_dishes = db()->query("SELECT m.dish_id, m.dish_name, mc.category_name FROM menu m LEFT JOIN menu_categories mc ON mc.category_id = m.category_id ORDER BY mc.category_name, m.dish_name")->fetchAll();
$history_employees = db()->query("SELECT e.employee_id, e.employee_name, e.employee_surname, g.grade_name FROM employees e LEFT JOIN grades g ON g.grade_id = e.grade_id ORDER BY g.grade_name, e.employee_name, e.employee_surname")->fetchAll();
$restaurant_name = get_setting('restaurant_name') ?? 'Restaurant System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo htmlspecialchars(logo_url()) ?>">
    <title><?php echo htmlspecialchars($restaurant_name) ?> - Chef Panel</title>
    <link rel="stylesheet" href="chef_panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="chef_layout">
        <div class="topbar">
            <div class="topbar_left">
                <img src="<?php echo htmlspecialchars($logo_src) ?>" alt="Restaurant Logo" class="logo">
                <div>
                    <div class="app_name">Chef Panel</div>
                    <div class="app_sub">Chef: <b><?php echo $employee_full_name ?></b></div>
                </div>
            </div>

            <div class="topbar_right">
                <button id="btn_kitchen_history" class="btn_nav" type="button"><i class="fa-solid fa-clock-rotate-left"></i> Kitchen History</button>
                <span class="auto_refresh"><i class="fa-solid fa-rotate"></i> Auto-refresh</span>
                <?php if ($can['waiter_access']): ?>
                <a class="btn_nav" href="../waiter_panel/waiter_panel.php"><i class="fa-solid fa-bell-concierge"></i> Waiter</a>
                <?php endif; ?>
                <?php if ($can['config_access']): ?>
                <a class="btn_nav" href="../config_panel/config_panel.php"><i class="fa-solid fa-gear"></i> Config</a>
                <?php endif; ?>
                <a class="btn_nav logout" href="../../php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>

        <div class="chef_main">
            <div id="queue_split" class="queue_split">
                <div class="queue_column column_cook">
                    <div class="column_header">
                        <i class="fa-solid fa-fire-burner"></i>
                        <span>To prepare</span>
                        <span id="cook_counter" class="column_counter">0</span>
                    </div>
                    <div id="queue_cook" class="queue"></div>
                </div>

                <div class="queue_column column_ready">
                    <div class="column_header">
                        <i class="fa-solid fa-bell"></i>
                        <span>Ready for pickup</span>
                        <span id="ready_counter" class="column_counter">0</span>
                    </div>
                    <div id="queue_ready" class="queue"></div>
                </div>
            </div>
        </div>

        <div id="chef_toasts"></div>

        <div id="kitchen_history_modal" class="modal hidden">
            <div class="modal_box wide">
                <h3><i class="fa-solid fa-clock-rotate-left"></i> Kitchen History</h3>
                <button id="btn_kitchen_history_x" class="modal_close_x" type="button">&times;</button>
                <div class="history_filters">
                    <input type="date" id="kh_date_from" class="history_filter_input">
                    <input type="date" id="kh_date_to" class="history_filter_input">
                    <input type="time" id="kh_time_from" class="history_filter_input">
                    <input type="time" id="kh_time_to" class="history_filter_input">
                    <select id="kh_dish" class="history_filter_input">
                        <option value="">All dishes</option>
                    </select>
                    <select id="kh_employee" class="history_filter_input" multiple size="4">
                    </select>
                    <button type="button" id="kh_apply" class="btn_secondary">Search</button>
                    <button type="button" id="kh_clear" class="btn_secondary">Clear</button>
                </div>
                <div id="kitchen_history_list" class="history_list"></div>
                <div class="summary_actions">
                    <button id="btn_kitchen_history_close" class="btn_secondary" type="button">Close</button>
                </div>
            </div>
        </div>

    <script>
        window.HISTORY_FILTERS = <?php echo json_encode(['dishes' => $history_dishes, 'employees' => $history_employees], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="chef_panel.js"></script>
    </div>
</body>
</html>
