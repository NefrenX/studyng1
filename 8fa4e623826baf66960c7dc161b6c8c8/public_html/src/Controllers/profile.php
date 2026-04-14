<?php
session_start();
require __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /src/Controllers/login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        $_SESSION['csrf_token'] = sha1(uniqid('', true) . mt_rand());
    }
}

$user_id = (int)$_SESSION['user_id'];

$sql = "
    SELECT 
        o.id AS order_id,
        o.created_at,
        o.status,
        p.title,
        p.price,
        p.image_url
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(array($user_id));
$my_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/src/Controllers/main.php">Мой Проект</a>

        <div class="d-flex">
            <span class="navbar-text text-white me-3">
                Вы вошли как:
                <b><?php echo htmlspecialchars(isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'User', ENT_QUOTES, 'UTF-8'); ?></b>
            </span>

            <a href="/src/Controllers/change_password.php" class="btn btn-warning btn-sm me-2">
                Сменить пароль
            </a>

            <a href="/src/Controllers/logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h2 class="mb-0">Мои заказы</h2>
        </div>

        <div class="card-body">

            <?php if (count($my_orders) > 0): ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>№ Заказа</th>
                                <th>Дата</th>
                                <th>Товар</th>
                                <th>Цена</th>
                                <th>Статус</th>
                                <th>Действие</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($my_orders as $order): ?>
                            <tr>
                                <td>#<?php echo (int)$order['order_id']; ?></td>

                                <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>

                                <td>
                                    <strong><?php echo htmlspecialchars($order['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>

                                <td><?php echo number_format((float)$order['price'], 0, '', ' '); ?> ₽</td>

                                <td>
                                    <?php
                                    $status_color = 'secondary';
                                    if ($order['status'] == 'new') $status_color = 'primary';
                                    if ($order['status'] == 'processing') $status_color = 'warning';
                                    if ($order['status'] == 'done') $status_color = 'success';
                                    if ($order['status'] == 'cancelled') $status_color = 'danger';
                                    ?>
                                    <span class="badge bg-<?php echo $status_color; ?>">
                                        <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($order['status'] == 'new' || $order['status'] == 'processing'): ?>
                                        <form action="/src/Controllers/cancel_order.php" method="POST" onsubmit="return confirm('Отменить заказ?');">
                                            <input type="hidden" name="order_id" value="<?php echo (int)$order['order_id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                Отменить
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            <?php else: ?>

                <div class="text-center py-5">
                    <h4 class="text-muted">Вы ещё ничего не заказывали.</h4>
                    <a href="main.php" class="btn btn-primary mt-3">Перейти в каталог</a>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>