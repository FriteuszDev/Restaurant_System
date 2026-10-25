<?php
// Config Panel Index - checks permissions and redirects appropriately
require_once __DIR__ . '/../php/auth.php';

$employee = current_employee();

if (!$employee) {
    // Not logged in - redirect to login
    header('Location: ' . project_url('client/index.php'));
    exit();
}

$panel_url = default_panel_url($employee);

// If the default panel is NOT the config panel, redirect to it
if ($panel_url !== 'client/config_panel/config_panel.php') {
    header('Location: ' . project_url($panel_url));
    exit();
}

// If user doesn't have config_access but somehow got here, redirect
if (!empty($employee['waiter_access']) || !empty($employee['chef_access'])) {
    header('Location: ' . project_url($panel_url));
    exit();
}

// Show the config panel
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo htmlspecialchars(logo_url()) ?>">
    <title>Restaurant System - Config Panel</title>
    <link rel="stylesheet" href="config_panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <header>
            <h1><i class="fa-solid fa-cog"></i> Config Panel</h1>
            <div class="user_info">
                <i class="fa-solid fa-user"></i>
                <span><?php echo htmlspecialchars(employee_full_name($employee)) ?> (<?php echo htmlspecialchars($employee['grade_name'] ?? '') ?>)</span>
            </div>
            <a href="../php/logout.php" class="logout_link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </header>
        
        <div class="main_content">
            <i class="fa-solid fa-info-circle"></i>
            <p>Config panel access denied for your role.</p>
            <p>You have: <?php echo implode(', ', array_keys($employee)); ?></p>
        </div>
    </div>
</body>
</html>