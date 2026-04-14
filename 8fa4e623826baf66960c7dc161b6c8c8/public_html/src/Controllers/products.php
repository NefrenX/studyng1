<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../Models/check_admin.php';

/* CSRF */
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        $_SESSION['csrf_token'] = sha1(uniqid('', true) . mt_rand());
    }
}

/* ПОИСК */
$sku = isset($_GET['sku']) ? trim($_GET['sku']) : '';

/* ПАГИНАЦИЯ */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 10;
$offset = ($page - 1) * $limit;

/* БАЗОВЫЙ SQL */
$sql = "SELECT id, title, sku, price, quantity, image_url, created_at FROM products";
$params = array();

if ($sku !== '') {
    $sql .= " WHERE sku LIKE ?";
    $params[] = "%" . $sku . "%";
}

/* СЧИТАЕМ ОБЩЕЕ КОЛ-ВО */
$count_sql = "SELECT COUNT(*) FROM products";
$count_params = array();

if ($sku !== '') {
    $count_sql .= " WHERE sku LIKE ?";
    $count_params[] = "%" . $sku . "%";
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_rows = (int)$count_stmt->fetchColumn();

$total_pages = (int)ceil($total_rows / $limit);
if ($total_pages < 1) {
    $total_pages = 1;
}

if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

/* ПОЛУЧАЕМ ДАННЫЕ */
$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Товары</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4 bg-light">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Список товаров</h1>

    <div class="d-flex gap-2">
        <a href="/src/Controllers/admin_panel.php" class="btn btn-dark">Админка</a>
        <a href="/src/Controllers/add_item.php" class="btn btn-success">Добавить</a>
        <a href="/src/Controllers/main.php" class="btn btn-secondary">На главную</a>
    </div>
</div>

<form method="GET" action="/src/Controllers/products.php" class="card p-3 mb-4 shadow-sm">
    <div class="row g-2 align-items-end">
        <div class="col-md-10">
            <label class="form-label">Поиск по артикулу (SKU)</label>
            <input
                type="text"
                name="sku"
                class="form-control"
                placeholder="Например: SKU-001"
                value="<?php echo htmlspecialchars($sku, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Найти</button>
        </div>
    </div>
</form>

<table class="table table-bordered bg-white align-middle">
    <thead class="table-dark">
        <tr>
            <th style="width:70px;">ID</th>
            <th style="width:120px;">Фото</th>
            <th>Название</th>
            <th style="width:140px;">SKU</th>
            <th style="width:120px;">Цена</th>
            <th style="width:100px;">Остаток</th>
            <th style="width:180px;">Создан</th>
            <th style="width:130px;">Редактировать</th>
            <th style="width:130px;">Удалить</th>
        </tr>
    </thead>

    <tbody>
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>

            <?php
            $img = $p['image_url'];

            if (!$img) {
                $img = "/uploads/no-image.png";
            } else {
                if (strpos($img, '/uploads/') !== 0 && strpos($img, 'http') !== 0) {
                    $img = '/' . ltrim($img, '/');
                }
            }
            ?>

            <tr>
                <td><?php echo (int)$p['id']; ?></td>

                <td>
                    <img
                        src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                        style="width:120px;height:80px;object-fit:cover;border-radius:6px;"
                        alt="Фото товара"
                    >
                </td>

                <td><?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?></td>

                <td>
                    <code><?php echo htmlspecialchars($p['sku'], ENT_QUOTES, 'UTF-8'); ?></code>
                </td>

                <td><?php echo number_format((float)$p['price'], 0, '', ' '); ?> руб.</td>

                <td><?php echo (int)$p['quantity']; ?></td>

                <td><?php echo htmlspecialchars($p['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>

                <td>
                    <a href="/src/Controllers/edit_item.php?id=<?php echo (int)$p['id']; ?>"
                       class="btn btn-warning btn-sm w-100">
                        Редактировать
                    </a>
                </td>

                <td>
                    <form action="/src/Controllers/delete_product.php" method="POST" onsubmit="return confirm('Вы уверены?');">
                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            Удалить
                        </button>
                    </form>
                </td>
            </tr>

        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="9" class="text-center text-muted">Товаров пока нет</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link"
                       href="/src/Controllers/products.php?page=<?php echo $i; ?>&sku=<?php echo urlencode($sku); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

</body>
</html>