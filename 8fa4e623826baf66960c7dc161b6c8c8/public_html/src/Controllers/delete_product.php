<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../Models/check_admin.php';

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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    die('Неверный ID товара');
}

try {
    // сначала удалить связанные заказы
    $stmt = $pdo->prepare("DELETE FROM orders WHERE product_id = ?");
    $stmt->execute(array($id));

    // потом удалить товар
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute(array($id));

header("Location: /src/Controllers/products.php");
exit;

} catch (PDOException $e) {
    die("Ошибка БД: " . $e->getMessage());
}