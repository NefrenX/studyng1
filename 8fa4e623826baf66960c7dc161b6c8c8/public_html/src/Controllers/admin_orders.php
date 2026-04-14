<?php
header('Content-Type: text/html; charset=utf-8');
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../Models/check_admin.php';

/* CSRF токен */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* === ОБНОВЛЕНИЕ СТАТУСА === */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {

    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    $allowed = array('new','processing','done');

    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->execute(array($status, $order_id));
    }
}

/* === ПОЛУЧЕНИЕ ЗАКАЗОВ === */
$sql = "
    SELECT 
        o.id AS order_id,
        o.created_at,
        o.status,
        u.email,
        p.title,
        p.price
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.id DESC
";

$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Управление заказами</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4 bg-light">

<h1>Управление заказами</h1>
<a href="/src/Controllers/main.php" class="btn btn-secondary mb-3">На главную</a>

<table class="table table-bordered bg-white">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Дата</th>
<th>Клиент</th>
<th>Товар</th>
<th>Цена</th>
<th>Статус</th>
<th>Изменить</th>
<th>Удалить</th>
</tr>
</thead>

<tbody>

<?php foreach ($orders as $order): ?>
<tr>

<td><?php echo $order['order_id']; ?></td>
<td><?php echo $order['created_at']; ?></td>
<td><?php echo htmlspecialchars($order['email']); ?></td>
<td><?php echo htmlspecialchars($order['title']); ?></td>
<td><?php echo $order['price']; ?> ₽</td>

<td>
<?php
$color = "secondary";
if ($order['status']=="new") $color="warning";
if ($order['status']=="processing") $color="primary";
if ($order['status']=="done") $color="success";
?>
<span class="badge bg-<?php echo $color; ?>">
<?php echo $order['status']; ?>
</span>
</td>

<td>
<form method="POST" class="d-flex gap-2">

<input type="hidden" name="order_id"
value="<?php echo $order['order_id']; ?>">

<select name="status" class="form-select form-select-sm">

<option value="new" <?php if($order['status']=="new") echo "selected"; ?>>new</option>
<option value="processing" <?php if($order['status']=="processing") echo "selected"; ?>>processing</option>
<option value="done" <?php if($order['status']=="done") echo "selected"; ?>>done</option>

</select>

<button class="btn btn-sm btn-primary">OK</button>
</form>
</td>

<!-- УДАЛЕНИЕ -->
<td>
<form action="/src/Controllers/delete_item.php" method="POST"
onsubmit="return confirm('Вы уверены?');">

<input type="hidden" name="id"
value="<?php echo $order['order_id']; ?>">

<input type="hidden" name="csrf_token"
value="<?php echo $_SESSION['csrf_token']; ?>">

<button type="submit" class="btn btn-danger btn-sm">
🗑️ Удалить
</button>

</form>
</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>

</body>
</html>
