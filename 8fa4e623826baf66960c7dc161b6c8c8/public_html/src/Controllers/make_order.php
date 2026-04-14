<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
}

$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

if ($product_id > 0) {
    $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $check->execute([$product_id]);
    $exists = $check->fetch();

    if (!$exists) {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $log_message = date('Y-m-d H:i:s') . " - Пользователь ID: $user_id, IP: $user_ip пытался заказать несуществующий товар ID: $product_id\n";
        file_put_contents('security.log', $log_message, FILE_APPEND);
        
        die("Ошибка: Попытка заказать несуществующий товар! Ваш IP записан. <a href='main.php'>Вернуться на главную</a>");
    }
    
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id) VALUES (?, ?)");
    try {
        $stmt->execute([$user_id, $product_id]);
        echo "Заказ успешно оформлен! Менеджер свяжется с вами. <a href='main.php'>Вернуться</a>";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
} else {
    echo "Неверный товар. <a href='/src/Controllers/main.php'>Вернуться на главную</a>";
}
?>