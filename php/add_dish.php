<?php
require_once __DIR__ . '/auth.php';

require_json_access('add_dishes');

$name = trim((string) ($_POST['name'] ?? $_POST['dish_name'] ?? ''));
$desc = trim((string) ($_POST['desc'] ?? $_POST['dish_desc'] ?? ''));
$price = $_POST['price'] ?? $_POST['dish_price'] ?? '';

if ($name === '' || mb_strlen($name) > 100) {
    json_out(['success' => false, 'message' => 'Dish name must be 1-100 characters.'], 400);
}

if (mb_strlen($desc) > 500) {
    json_out(['success' => false, 'message' => 'Description too long (max 500 chars).'], 400);
}

if (!is_numeric($price) || (float) $price < 0) {
    json_out(['success' => false, 'message' => 'Price must be a non-negative number.'], 400);
}


$SavedPath = null;

if (isset($_FILES['image']) && is_array($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $upload = $_FILES['image'];

    if ($upload['error'] !== UPLOAD_ERR_OK) {
        json_out(['success' => false, 'message' => 'File upload error (code ' . (int) $upload['error'] . ').'], 400);
    }

    if (!is_uploaded_file($upload['tmp_name'])) {
        json_out(['success' => false, 'message' => 'Invalid file uploaded.'], 400);
    }

    // Limit rozmiaru pliku (5 MB).
    $maxBytes = 5 * 1024 * 1024;
    if ((int) $upload['size'] <= 0 || (int) $upload['size'] > $maxBytes) {
        json_out(['success' => false, 'message' => 'File is empty or exceeds the 5 MB limit.'], 400);
    }

    $originalName = (string) $upload['name'];

    // 1) Rozszerzenie: dozwolone wylacznie .png
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension !== 'png') {
        json_out(['success' => false, 'message' => 'Only PNG image format is allowed.'], 400);
    }

    // 1a) Odrzucamy podwojne rozszerzenia z rozszerzeniem wykonywalnym (np. shell.php.png),
    //     nawet jeśli sama treść pliku jest poprawnym obrazem.
    $baseName = basename(str_replace('\\', '/', $originalName));
    $nameParts = explode('.', $baseName);
    array_shift($nameParts); // nazwa pliku bez rozszerzenia
    $dangerous = [
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phps', 'phtml',
        'phar', 'pgif', 'phtm', 'shtml', 'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'exe',
        'bat', 'cmd', 'com', 'htaccess', 'asp', 'aspx', 'jsp', 'js', 'html', 'htm',
    ];

    foreach ($nameParts as $part) {
        if (in_array(strtolower($part), $dangerous, true)) {
            json_out(['success' => false, 'message' => 'File name contains a disallowed extension.'], 400);
        }
    }

    // 2) Typ MIME z pliku (nie z naglowka klienta) - musi byc image/png
    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = (string) finfo_file($finfo, $upload['tmp_name']);
            finfo_close($finfo);
        }
    }

    if ($mime !== '' && $mime !== 'image/png') {
        json_out(['success' => false, 'message' => 'Uploaded file is not a PNG image.'], 400);
    }

    // 3) Sygnatura PNG w zawartosci pliku (8 bajtow: \x89PNG\r\n\x1a\n)
    $handle = fopen($upload['tmp_name'], 'rb');
    if ($handle === false) {
        json_out(['success' => false, 'message' => 'Cannot read the uploaded file.'], 500);
    }
    $signature = fread($handle, 8);
    fclose($handle);

    if ($signature !== "\x89PNG\r\n\x1a\n") {
        json_out(['success' => false, 'message' => 'Uploaded file is not a PNG image.'], 400);
    }

    // 4) Plik musi byc poprawnym obrazem odczytywalnym przez GD/getimagesize
    $imageInfo = @getimagesize($upload['tmp_name']);
    if ($imageInfo === false || ($imageInfo[2] ?? 0) !== IMAGETYPE_PNG) {
        json_out(['success' => false, 'message' => 'Uploaded file is not a valid PNG image.'], 400);
    }

    // 5) Generowana, bezpieczna nazwa pliku (bez danych z oryginalnej nazwy)
    $FileName = 'dish_' . bin2hex(random_bytes(8)) . '.png';

    $SaveInFolder = __DIR__ . '/../client/images/';
    $SaveIn = $SaveInFolder . $FileName;

    if (!move_uploaded_file($upload['tmp_name'], $SaveIn)) {
        json_out(['success' => false, 'message' => 'Cannot save image.'], 500);
    }

    $SavedPath = $SaveIn;
}

try {
    $pdo = db();

    $sql = "INSERT INTO menu (dish_name, dish_desc, dish_price) VALUES (:dish_name, :dish_desc, :dish_price)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'dish_name' => $name,
        'dish_desc' => $desc,
        'dish_price' => $price,
    ]);

    $dish = [
        'dish_id' => (int) $pdo->lastInsertId(),
        'dish_name' => $name,
        'dish_desc' => $desc,
        'dish_price' => number_format((float) $price, 2, '.', ''),
    ];

    if (is_ajax_request()) {
        json_out(['success' => true, 'message' => 'Dish added.', 'dish' => $dish]);
    }

    echo "added dish";
} catch (PDOException $e) {
    // Nieudane zapisywanie do bazy nie moze zostawic pliku na serwerze.
    if ($SavedPath !== null && is_file($SavedPath)) {
        @unlink($SavedPath);
    }

    if (is_ajax_request()) {
        json_out(['success' => false, 'message' => 'Error saving dish.'], 500);
    }

    die("connection error " . $e->getMessage());
}

header("Location: ../client/config_panel/config_panel.php");
exit();
