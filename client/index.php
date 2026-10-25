<?php
require_once __DIR__ . '/../php/auth.php';

$employee = current_employee();

if ($employee) {
    $panel_url = default_panel_url($employee);

    if ($panel_url !== 'client/index.php') {
        header('Location: ' . project_url($panel_url));
        exit();
    }
}

$denied = isset($_GET['denied']);

$employees = db()->query(
    "SELECT employee_id, employee_name, employee_surname
     FROM employees
     ORDER BY employee_name, employee_surname"
)->fetchAll();

$restaurant_name = get_setting('restaurant_name') ?? 'Restaurant System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo htmlspecialchars(logo_url()) ?>">
    <title><?php echo htmlspecialchars($restaurant_name ?? 'Restaurant System') ?> - Login</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="login_card">
        <h1 class="login_title"><?php echo htmlspecialchars($restaurant_name ?? 'Restaurant System') ?></h1>

        <?php if ($denied): ?>
            <div class="login_message error">You do not have access to this section.</div>
        <?php endif; ?>

        <?php if (!$employee): ?>
            <?php if (!$employees): ?>
                <div class="login_message info">
                    No employees in database. Add the first employee in the Config panel.
                </div>
            <?php else: ?>
                <form id="login_form" autocomplete="off">
                    <label for="employee_id">Who is working:</label>
                    <select id="employee_id" name="employee_id" required>
                        <?php foreach ($employees as $row): ?>
                            <option value="<?php echo (int) $row['employee_id'] ?>">
                                <?php echo htmlspecialchars(trim($row['employee_name'] . ' ' . $row['employee_surname'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required autofocus>

                    <div id="login_error" class="login_message error hidden"></div>

                    <button type="submit" id="login_button">LOGIN</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <div class="login_message info">
                Logged in as <b><?php echo htmlspecialchars(employee_full_name($employee)) ?></b>
                (<?php echo htmlspecialchars($employee['grade_name'] ?? 'no position') ?>),
                but your position has no access to any panel.
            </div>
            <a class="logout_link" href="../php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        <?php endif; ?>
    </div>

    <script src="index.js"></script>
</body>
</html>
