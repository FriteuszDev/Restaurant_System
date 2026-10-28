<?php
require_once __DIR__ . '/../../php/auth.php';

$employee = require_panel_access('waiter_access');

$can = [
    'config_access' => !empty($employee['config_access']),
    'chef_access' => !empty($employee['chef_access']),
];

$floors = db()->query("SELECT floor_id, floor_name FROM floors ORDER BY floor_name")->fetchAll();
$tables = db()->query(
    "SELECT table_id, floor_id, table_number, chairs, status FROM restaurant_tables ORDER BY table_number"
)->fetchAll();
$categories = db()->query("SELECT category_id, category_name FROM menu_categories ORDER BY category_name")->fetchAll();
$menu = db()->query("SELECT m.dish_id, m.dish_name, m.dish_price, m.category_id, mc.category_name FROM menu m LEFT JOIN menu_categories mc ON mc.category_id = m.category_id ORDER BY mc.category_name, m.dish_name")->fetchAll();

$history_employees = db()->query("SELECT e.employee_id, e.employee_name, e.employee_surname, g.grade_name FROM employees e LEFT JOIN grades g ON g.grade_id = e.grade_id ORDER BY g.grade_name, e.employee_name, e.employee_surname")->fetchAll();
$restaurant_name = get_setting('restaurant_name') ?? 'Restaurant System';

$floors_layout = [];
$floor_index = [];

foreach ($floors as $floor) {
    $floor_index[(int) $floor['floor_id']] = count($floors_layout);
    $floors_layout[] = [
        'floor_id' => (int) $floor['floor_id'],
        'floor_name' => $floor['floor_name'],
        'tables' => [],
    ];
}

foreach ($tables as $table) {
    $floor_id = (int) $table['floor_id'];

    if (!isset($floor_index[$floor_id])) {
        continue;
    }

    $floors_layout[$floor_index[$floor_id]]['tables'][] = [
        'table_id' => (int) $table['table_id'],
        'table_number' => $table['table_number'],
        'chairs' => (int) $table['chairs'],
        'status' => $table['status'],
    ];
}

$employee_full_name = htmlspecialchars(employee_full_name($employee));

// Kontrakt logo - jedno zrodlo prawdy (settings.setting_value dla 'restaurant_logo').
$logo_src = logo_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo htmlspecialchars($logo_src) ?>">
    <title><?php echo htmlspecialchars($restaurant_name) ?> - Waiter Panel</title>
    <link rel="stylesheet" href="waiter_panel.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="waiter_layout">
    <div class="sidebar">
        <div class="sidebar_header">
            <img src="<?php echo htmlspecialchars($logo_src) ?>" alt="Restaurant Logo" class="logo">
            <div>
                <div class="app_name"><?php echo htmlspecialchars($restaurant_name) ?></div>
                <div class="app_sub">Waiter Panel</div>
            </div>
        </div>

        <div class="user_row">
            <span class="user_name"><i class="fa-solid fa-user"></i> <b><?php echo $employee_full_name ?></b></span>
            <a href="../../php/logout.php" class="btn_icon" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>

        <div class="nav_row">
            <button id="btn_history" class="btn_nav" type="button"><i class="fa-solid fa-clock-rotate-left"></i> History</button>
            <?php if ($can['config_access']): ?>
            <a class="btn_nav" href="../config_panel/config_panel.php"><i class="fa-solid fa-gear"></i> Config</a>
            <?php endif; ?>
            <?php if ($can['chef_access']): ?>
            <a class="btn_nav" href="../chef_panel/chef_panel.php"><i class="fa-solid fa-fire-burner"></i> Kitchen</a>
            <?php endif; ?>
        </div>

        <div id="floors_container" class="floors_container"></div>
    </div>

    <div class="main">
        <div id="empty_hint" class="empty_hint">
            <i class="fa-solid fa-hand-pointer"></i>
            Select a table from the list to open the bill.
        </div>

        <div id="table_view" class="table_view hidden">
            <div class="table_view_header">
                <div class="tv_left">
                    <span id="tv_table_number" class="tv_number">-</span>
                    <span id="tv_floor_name" class="tv_floor">-</span>
                    <span id="tv_chairs" class="tv_chairs"><i class="fa-solid fa-chair"></i> <span id="tv_chairs_count">0</span></span>
                </div>
                <div class="tv_right">
                    <span id="tv_status" class="status_badge">-</span>
                    <button id="btn_close_view" class="btn_icon" title="Close table view" type="button"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>

            <div class="table_view_body">
                <div class="orders_panel">
                    <h3><i class="fa-solid fa-receipt"></i> Orders <span id="tv_total" class="tv_total"></span></h3>
                    <div id="orders_list" class="orders_list"></div>
                </div>

                <div id="menu_panel" class="menu_panel">
                    <h3><i class="fa-solid fa-utensils"></i> Add to order</h3>
                    <div id="menu_categories" class="menu_categories"></div>
                    <div id="menu_list" class="menu_list"></div>

                    <h3><i class="fa-solid fa-cart-shopping"></i> Cart</h3>
                    <div id="cart_list" class="cart_list"></div>
                    <button id="btn_send_order" class="btn_primary" type="button">Send to kitchen</button>
                </div>
            </div>

            <div id="table_view_footer" class="table_view_footer">
                <button id="btn_free_table" class="btn_secondary" type="button"><i class="fa-solid fa-user-slash"></i> Free table</button>
                <button id="btn_close_table" class="btn_warning" type="button">Summary and close table</button>
            </div>

            <div id="to_pay_panel" class="to_pay_panel hidden">
                <h3><i class="fa-solid fa-cash-register"></i> Bill waiting for payment</h3>
                <div id="to_pay_list" class="summary_list"></div>
                <div class="summary_total">Total: <b id="to_pay_total">0.00</b> PLN</div>
                <div class="summary_actions">
                    <button id="btn_pay_now" class="btn_primary" type="button"><i class="fa-solid fa-check"></i> Paid - free table</button>
                    <button id="btn_skip_pay" class="btn_secondary" type="button">Skip - will do later</button>
                </div>
            </div>
        </div>
    </div>

    <div id="summary_modal" class="modal hidden">
        <div class="modal_box">
            <h3><i class="fa-solid fa-cash-register"></i> Table summary <span id="sm_table"></span></h3>
            <div id="sm_list" class="summary_list"></div>
            <div class="summary_total">Total: <b id="sm_total">0.00</b> PLN</div>
            <div class="summary_actions">
                <button id="btn_modal_pay" class="btn_primary" type="button"><i class="fa-solid fa-check"></i> Paid</button>
                <button id="btn_modal_skip" class="btn_secondary" type="button">Skip (pay later)</button>
            </div>
        </div>
    </div>

    <div id="history_modal" class="modal hidden">
        <div class="modal_box wide">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Order history</h3>
            <button id="btn_history_x" class="modal_close_x" type="button">&times;</button>
            <div class="history_filters">
                <input type="date" id="wh_date_from" class="history_filter_input">
                <input type="date" id="wh_date_to" class="history_filter_input">
                <input type="time" id="wh_time_from" class="history_filter_input">
                <input type="time" id="wh_time_to" class="history_filter_input">
                <select id="wh_dish" class="history_filter_input">
                    <option value="">All dishes</option>
                </select>
                <select id="wh_employee" class="history_filter_input" multiple size="4">
                </select>
                <button type="button" id="wh_apply" class="btn_secondary">Search</button>
                <button type="button" id="wh_clear" class="btn_secondary">Clear</button>
            </div>
            <div id="history_list" class="history_list"></div>
            <div class="summary_actions">
                <button id="btn_history_close" class="btn_secondary" type="button">Close</button>
            </div>
        </div>
    </div>

    <div id="waiter_toasts"></div>

    <script>
        window.INITIAL = <?php echo json_encode(['floors' => $floors_layout, 'menu' => $menu, 'categories' => $categories, 'employees' => $history_employees], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        window.CAN = <?php echo json_encode($can, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    </div>
    <script src="waiter_panel.js?v=15"></script>
</body>
</html>

