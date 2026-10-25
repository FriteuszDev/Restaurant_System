<?php
require_once __DIR__ . '/auth.php';

require_json_access('waiter_access');

$session_id = (int) ($_POST['session_id'] ?? 0);

if ($session_id <= 0) {
    json_out(['success' => false, 'message' => 'Nieprawidlowe ID sesji stolika.'], 400);
}

$stmt = db()->prepare("SELECT session_id, table_id, status FROM table_sessions WHERE session_id = :session_id");
$stmt->execute([':session_id' => $session_id]);
$session = $stmt->fetch();

if (!$session) {
    json_out(['success' => false, 'message' => 'Nie znaleziono sesji stolika.'], 404);
}

if ($session['status'] !== 'open') {
    json_out(['success' => false, 'message' => 'Ta sesja stolika jest juz zamknieta.'], 409);
}

$stmt = db()->prepare(
    "SELECT COUNT(*) AS items_count, COALESCE(SUM(oi.quantity * oi.dish_price), 0) AS total
     FROM order_items oi
     JOIN orders o ON o.order_id = oi.order_id
     WHERE o.session_id = :session_id"
);
$stmt->execute([':session_id' => $session_id]);
$summary = $stmt->fetch();

// Stolik bez zadnych pozycji - zwalniamy go (anulowanie pustego stolika).
if ((int) $summary['items_count'] === 0) {
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("DELETE FROM table_sessions WHERE session_id = :session_id");
        $stmt->execute([':session_id' => $session_id]);

        $stmt = $pdo->prepare("UPDATE restaurant_tables SET status = 'free' WHERE table_id = :table_id");
        $stmt->execute([':table_id' => $session['table_id']]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        json_out(['success' => false, 'message' => 'Blad zamykania stolika.'], 500);
    }

    json_out([
        'success' => true,
        'message' => 'Pusty stolik zostal zwolniony.',
        'cancelled' => true,
        'summary' => ['total' => '0.00', 'items_count' => 0],
    ]);
}

$total = number_format((float) $summary['total'], 2, '.', '');

$stmt = db()->prepare("UPDATE table_sessions SET status = 'to_pay', total = :total WHERE session_id = :session_id");
$stmt->execute([':total' => $total, ':session_id' => $session_id]);

$stmt = db()->prepare("UPDATE restaurant_tables SET status = 'to_pay' WHERE table_id = :table_id");
$stmt->execute([':table_id' => $session['table_id']]);

json_out([
    'success' => true,
    'message' => 'Zamknieto stolik - czeka na plate.',
    'cancelled' => false,
    'summary' => [
        'total' => $total,
        'items_count' => (int) $summary['items_count'],
    ],
]);
