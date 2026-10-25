<?php
// Chef Panel Index - checks permissions and redirects appropriately
require_once __DIR__ . '/../php/auth.php';

$employee = current_employee();

if (!$employee) {
    // Not logged in - redirect to login
    header('Location: ' . project_url('client/index.php'));
    exit();
}

$panel_url = default_panel_url($employee);

// If the default panel is NOT the chef panel, redirect to it
if ($panel_url !== 'client/chef_panel/chef_panel.php') {
    header('Location: ' . project_url($panel_url));
    exit();
}

// If user doesn't have chef_access but somehow got here, redirect
if (!empty($employee['waiter_access']) || !empty($employee['config_access'])) {
    header('Location: ' . project_url($panel_url));
    exit();
}

// Show the chef panel
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo htmlspecialchars(logo_url()) ?>">
    <title>Restaurant System - Chef Panel</title>
    <link rel="stylesheet" href="chef_panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <header>
            <h1><i class="fa-solid fa-utensils"></i> Chef Panel</h1>
            <div class="user_info">
                <i class="fa-solid fa-user"></i>
                <span><?php echo htmlspecialchars(employee_full_name($employee)) ?> (<?php echo htmlspecialchars($employee['grade_name'] ?? '') ?>)</span>
            </div>
            <a href="../php/logout.php" class="logout_link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </header>
        
        <div class="main_content">
            <i class="fa-solid fa-cookie-bite"></i>
            <p>Welcome to the Chef Panel</p>
            <p>Manage dishes and kitchen operations.</p>
        </div>
    </div>
</body>
</html>