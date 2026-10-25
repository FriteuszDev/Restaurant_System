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
                <span id="queue_counter" class="queue_counter"><i class="fa-solid fa-hourglass-half"></i> 0</span>
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
            <div class="queue_wrapper">
                <div id="queue" class="queue"></div>
            </div>
        </div>

        <div id="chef_toasts"></div>

        <script src="chef_panel.js"></script>
    </div>
</body>
</html>
