<?php
session_start();
require __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /src/Controllers/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Неверный запрос');
}

if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('CSRF ошибка');
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$user_id  = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    die('Неверный ID заказа');
}

$stmt = $pdo->prepare("
    UPDATE orders
    SET status = 'cancelled'
    WHERE id = ?
      AND user_id = ?
      AND status IN ('new', 'processing')
");
$stmt->execute(array($order_id, $user_id));

header("Location: /src/Controllers/profile.php");
exit;