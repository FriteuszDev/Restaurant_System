<?php
require_once __DIR__ . '/db.php';

// Session ini settings MUST be set BEFORE session_start()
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0');
ini_set('session.use_only_cookies', '1');
session_cache_limiter('private');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function json_out(array $payload, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function is_ajax_request(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function current_employee(): ?array
{
    if (empty($_SESSION['employee_id'])) {
        return null;
    }

    static $employee = null;
    static $loaded = false;

    if (!$loaded) {
        $loaded = true;

        $stmt = db()->prepare(
            "SELECT e.employee_id, e.employee_name, e.employee_surname, e.grade_id,
                    g.grade_name, " . grade_permission_sql('g') . "
             FROM employees e
             LEFT JOIN grades g ON g.grade_id = e.grade_id
             WHERE e.employee_id = :employee_id"
        );
        $stmt->execute([':employee_id' => $_SESSION['employee_id']]);
        $employee = $stmt->fetch() ?: null;
    }

    return $employee;
}

function get_setting(string $key): ?string
{
    $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
    $stmt->execute([':key' => $key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : null;
}

/**
 * Kontrakt logo - jedno zrodlo prawdy dla calego projektu.
 *
 * Kanoniczny zapis: settings.setting_value dla setting_key = 'restaurant_logo'
 * (sciezka względna wobec katalogu projektu, np. 'uploads/logo_abc.png').
 *
 * Wspierajace miejsca trzymania tego samego stanu:
 *  - settings.restaurant_logo  - kolumna zrodlowa, synchronizowana przy kazdym zapisie,
 *  - current_logo              - historia wgranych plikow (rejestracja wgrania).
 */
const LOGO_SETTING_KEY = 'restaurant_logo';

/**
 * Domyślne logo projektu (gdy nie wgrano własnego).
 */
function default_logo_path(): string
{
    return 'client/images/logo.png';
}

/**
 * Zapis ustawienia (idempotentny - INSERT ... ON DUPLICATE KEY UPDATE).
 */
function save_setting(string $key, ?string $value): void
{
    $stmt = db()->prepare(
        "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    );
    $stmt->execute([':key' => $key, ':value' => $value]);
}

/**
 * Zapis logo - synchronizuje kolumne settings.restaurant_logo z wartoscia klucza.
 */
function save_logo_setting(?string $path): void
{
    $stmt = db()->prepare(
        "INSERT INTO settings (setting_key, setting_value, restaurant_logo) VALUES (:key, :value, :logo)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), restaurant_logo = VALUES(restaurant_logo)"
    );
    $stmt->execute([':key' => LOGO_SETTING_KEY, ':value' => $path, ':logo' => $path]);
}

/**
 * Odczyt aktualnej sciezki logo (null = brak wlasnego logo).
 */
function get_logo_path(): ?string
{
    static $resolved = false;
    static $path = null;

    if ($resolved) {
        return $path;
    }
    $resolved = true;

    try {
        $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
        $stmt->execute([':key' => LOGO_SETTING_KEY]);
        $value = $stmt->fetchColumn();
        if ($value !== false && trim((string) $value) !== '') {
            $path = trim((string) $value);
            return $path;
        }

        // Zapasowo: kolumna zrodlowa.
        $stmt = db()->prepare("SELECT restaurant_logo FROM settings WHERE setting_key = :key");
        $stmt->execute([':key' => LOGO_SETTING_KEY]);
        $value = $stmt->fetchColumn();
        if ($value !== false && trim((string) $value) !== '') {
            $path = trim((string) $value);
            return $path;
        }

        // Zapasowo: ostatnio wgrany plik.
        $stmt = db()->query("SELECT filename FROM current_logo ORDER BY id DESC LIMIT 1");
        $value = $stmt->fetchColumn();
        if ($value !== false && trim((string) $value) !== '') {
            $path = trim((string) $value);
        }
    } catch (Throwable $e) {
        $path = null;
    }

    return $path;
}

/**
 * Adres URL logo gotowy do uzycia w atrybucie src (sciezka jest wzgledna wobec biezacego skryptu).
 */
function logo_url(?string $path = null): string
{
    $path = $path !== null ? trim($path) : (string) get_logo_path();

    if ($path === '') {
        $path = default_logo_path();
    }

    // Zewnetrzny adres albo data-URI - uzywamy bez zmian.
    if (preg_match('#^(https?:)?//#i', $path) || stripos($path, 'data:') === 0) {
        return $path;
    }

    $path = ltrim(str_replace('\\', '/', $path), '/');

    return project_url($path);
}

/**
 * Rejestracja wgrania logo w tabeli current_logo (historia).
 */
function log_current_logo(string $path, string $originalName, ?int $fileSize, ?string $mimeType): void
{
    $stmt = db()->prepare(
        "INSERT INTO current_logo (filename, original_name, file_size, mime_type, settings_key)
         VALUES (:filename, :original_name, :file_size, :mime_type, :settings_key)"
    );
    $stmt->execute([
        ':filename' => $path,
        ':original_name' => $originalName,
        ':file_size' => $fileSize,
        ':mime_type' => $mimeType,
        ':settings_key' => LOGO_SETTING_KEY,
    ]);
}

function require_setting_access(): array
{
    $employee = require_login();

    if (empty($employee['manage_settings'])) {
        if (is_ajax_request()) {
            json_out(['success' => false, 'message' => 'Access denied to settings.'], 403);
        }

        header('Location: ' . project_url('client/index.php') . '?denied=1');
        exit();
    }

    return $employee;
}
function employee_full_name(array $employee): string
{
    return trim($employee['employee_name'] . ' ' . $employee['employee_surname']);
}

function employee_full_name_by_id(?int $employee_id): ?string
{
    if (!$employee_id) {
        return null;
    }

    $stmt = db()->prepare("SELECT employee_name, employee_surname FROM employees WHERE employee_id = :employee_id");
    $stmt->execute([':employee_id' => $employee_id]);
    $row = $stmt->fetch();

    return $row ? trim($row['employee_name'] . ' ' . $row['employee_surname']) : null;
}

function project_url(string $relative = ''): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $projectName = basename(dirname(__DIR__));

    $prefix = '';
    $current = rtrim($scriptDir, '/');

    while ($current !== '' && $current !== '/' && basename($current) !== $projectName) {
        $prefix .= '../';
        $parent = dirname($current);
        if ($parent === $current) {
            $prefix = '';
            break;
        }
        $current = $parent;
    }

    return $prefix . ltrim($relative, '/');
}

function default_panel_url(array $employee): string
{
    // Domyślnie kieruj do panelu kelnera lub kucharza; config dopiero
    // gdy brak dostępu do obu tych modułów.
    if (!empty($employee['waiter_access'])) {
        return 'client/waiter_panel/waiter_panel.php';
    }

    if (!empty($employee['chef_access'])) {
        return 'client/chef_panel/chef_panel.php';
    }

    if (!empty($employee['config_access'])) {
        return 'client/config_panel/config_panel.php';
    }

    return 'client/index.php';
}

function require_login(): array
{
    $employee = current_employee();

    if (!$employee) {
        if (is_ajax_request()) {
            json_out(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
        }

        header('Location: ' . project_url('client/index.php'));
        exit();
    }

    return $employee;
}

function require_panel_access(string $flag): array
{
    $employee = require_login();

    if (empty($employee[$flag])) {
        if (is_ajax_request()) {
            json_out(['success' => false, 'message' => 'Access denied to this section.'], 403);
        }

        header('Location: ' . project_url('client/index.php') . '?denied=1');
        exit();
    }

    return $employee;
}

function grade_permission_flags(): array
{
    return [
        'chef_access',
        'waiter_access',
        'config_access',
        'add_dishes',
        'delete_dishes',
        'edit_dishes',
        'add_employees',
        'delete_employees',
        'edit_employees',
        'add_grades',
        'delete_grades',
        'edit_grades',
        'edit_tables',
        'manage_settings',
    ];
}

function grade_permission_sql(string $alias = 'g'): string
{
    $cols = [];
    foreach (grade_permission_flags() as $flag) {
        $cols[] = $alias . '.' . $flag;
    }
    return implode(', ', $cols);
}

function has_grade_management(array $employee): bool
{
    return !empty($employee['add_grades'])
        || !empty($employee['delete_grades'])
        || !empty($employee['edit_grades']);
}

function can_grant_permission(array $employee, string $flag): bool
{
    if (!in_array($flag, grade_permission_flags(), true)) {
        return false;
    }

    return !empty($employee[$flag]);
}

function require_json_access(?string $flag = null): array
{
    $employee = require_login();

    if ($flag !== null && empty($employee[$flag])) {
        json_out(['success' => false, 'message' => 'Access denied to this operation.'], 403);
    }

    return $employee;
}
