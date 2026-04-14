<?php
session_start();
require __DIR__ . '/../../config/db.php';

// Поиск
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 10;
$offset = ($page - 1) * $limit;

// Базовый SQL
$where = '';
$params = array();

if ($q !== '') {
    $where = " WHERE title LIKE ? OR description LIKE ? OR sku LIKE ? ";
    $search = "%" . $q . "%";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// Считаем общее количество товаров
$count_sql = "SELECT COUNT(*) FROM products" . $where;
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$total_pages = (int)ceil($total_rows / $limit);
if ($total_pages < 1) {
    $total_pages = 1;
}
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Получаем товары текущей страницы
$sql = "SELECT * FROM products" . $where . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-light bg-white px-4 mb-4 shadow-sm">
    <span class="navbar-brand mb-0 h1">WMS Склад</span>

    <div>
        <?php if (isset($_SESSION['user_id'])): ?>

            <span class="me-3">Привет!</span>

            <a href="/src/Controllers/profile.php" class="btn btn-outline-primary btn-sm">Профиль</a>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="/src/Controllers/admin_panel.php" class="btn btn-outline-danger btn-sm">Админка</a>
                <a href="/src/Controllers/add_item.php" class="btn btn-success btn-sm">Добавить товар</a>
            <?php endif; ?>

            <a href="/src/Controllers/logout.php" class="btn btn-dark btn-sm">Выйти</a>

        <?php else: ?>

            <a href="/src/Controllers/login.php" class="btn btn-primary btn-sm">Войти</a>
            <a href="/src/Controllers/register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>

        <?php endif; ?>
    </div>
</nav>

<div class="container">

    <h2 class="mb-4">Каталог товаров</h2>

    <form method="GET" action="/src/Controllers/main.php" class="card p-3 mb-4 shadow-sm">
        <div class="row g-2">
            <div class="col-md-10">
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Поиск по названию, описанию или SKU"
                    value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Найти</button>
            </div>
        </div>
    </form>

    <div class="row">

    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>

<?php
$img = $product['image_url'];

if (!$img) {
    $img = "/uploads/no-image.png"; // или placeholder
} else {
    // если путь без / → добавляем
    if (strpos($img, '/uploads/') !== 0 && strpos($img, 'http') !== 0) {
        $img = '/' . ltrim($img, '/');
    }
}
?>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">

                    <img src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                         class="card-img-top"
                         style="height:200px;object-fit:cover;">

                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h5>

                        <p class="card-text">
                            <?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <p class="fw-bold text-primary mb-1">
                            <?php echo number_format((float)$product['price'], 0, '', ' '); ?> руб.
                        </p>

                        <p class="text-muted small mb-0">
                            SKU: <?php echo htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>

                    <div class="card-footer bg-white">
                        <a href="/src/Controllers/make_order.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-primary w-100">
                            Купить
                        </a>
                    </div>

                </div>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">Ничего не найдено.</p>
    <?php endif; ?>

    </div>

<?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="/src/Controllers/main.php?page=<?php echo $i; ?>&q=<?php echo urlencode($q); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

</div>

</body>
</html>