<?php
require_once __DIR__ . '/auth.php';

require_setting_access();

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    json_out(['success' => false, 'message' => 'No file uploaded.'], 400);
}

$file = $_FILES['logo'];
$allowedMime = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
$allowedExt = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
$maxSize = 2 * 1024 * 1024;

if ($file['size'] > $maxSize) {
    json_out(['success' => false, 'message' => 'File too large. Maximum 2 MB.'], 400);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowedMime, true)) {
    json_out(['success' => false, 'message' => 'Invalid file type. Only PNG, JPG, GIF, WEBP allowed.'], 400);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    json_out(['success' => false, 'message' => 'Invalid file extension.'], 400);
}

$logoData = $_POST['logo_data'] ?? '';
$decoded = ($logoData !== '') ? base64_decode($logoData, true) : false;
if ($decoded !== false && is_string($decoded)) {
    $sample = substr($decoded, 0, 4096);
    $check = explode(',', $sample);
    $head = $check[0] ?? '';
    $chunk = substr($head, 0, 12);
    $mime = mime_content_type($chunk);
    if (!in_array($mime, $allowedMime, true)) {
        json_out(['success' => false, 'message' => 'Invalid file type. Only PNG, JPG, GIF, WEBP allowed.'], 400);
    }
    $ext = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/webp' => 'webp'][$mime] ?? null;
    if ($ext === null) {
        json_out(['success' => false, 'message' => 'Invalid file type.'], 400);
    }
    $filename = 'logo_' . bin2hex(random_bytes(12)) . '.' . $ext;
    $destination = $uploadDir . $filename;
    if (file_put_contents($destination, $decoded) === false || strlen($decoded) > $maxSize) {
        @unlink($destination);
        json_out(['success' => false, 'message' => 'Failed to save uploaded file.'], 500);
    }
} else {
    if ($file['size'] > $maxSize) {
        json_out(['success' => false, 'message' => 'File too large. Maximum 2 MB.'], 400);
    }
    $filename = 'logo_' . bin2hex(random_bytes(12)) . '.' . $ext;
    $destination = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        json_out(['success' => false, 'message' => 'Failed to save uploaded file.'], 500);
    }
}

$pdo = db();
$relativePath = 'uploads/' . $filename;

// Kanoniczny zapis logo (klucz + kolumna zrodlowa) oraz rejestracja w current_logo.
save_logo_setting($relativePath);

try {
    log_current_logo(
        $relativePath,
        $file['name'] ?? $filename,
        (int) filesize($destination),
        $mime
    );
} catch (Throwable $e) {
    // Historia wgran nie blokuje zapisu logo.
}

json_out([
    'success' => true,
    'status' => 'success',
    'message' => 'Logo uploaded successfully.',
    'logo_path' => $relativePath,
    'logo_url' => logo_url($relativePath),
    'filename' => $filename,
]);
