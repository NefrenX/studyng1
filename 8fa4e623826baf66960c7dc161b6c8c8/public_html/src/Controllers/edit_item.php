<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../Models/check_admin.php';

$message = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Неверный ID товара');
}

/* 1. Получаем текущий товар */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute(array($id));
$product = $stmt->fetch();

if (!$product) {
    die('Товар не найден');
}

/* 2. Если отправили форму — обновляем */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $sku = isset($_POST['sku']) ? trim($_POST['sku']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '0';
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '0';
    $supplier_id = isset($_POST['supplier_id']) ? trim($_POST['supplier_id']) : '';
    $cell_id = isset($_POST['cell_id']) ? trim($_POST['cell_id']) : '';

    if ($title === '' || $sku === '') {
        $message = '<div class="alert alert-danger">Заполните название и SKU.</div>';
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $message = '<div class="alert alert-danger">Цена должна быть числом не меньше 0.</div>';
    } elseif ($quantity === '' || preg_match('/^\d+$/', $quantity) !== 1) {
        $message = '<div class="alert alert-danger">Количество должно быть целым числом.</div>';
    } else {
        $price = (float)$price;
        $quantity = (int)$quantity;

        $supplier_id = ($supplier_id === '') ? null : (int)$supplier_id;
        $cell_id = ($cell_id === '') ? null : (int)$cell_id;

        try {
            $sql = "UPDATE products 
                    SET title = ?, sku = ?, description = ?, price = ?, quantity = ?, supplier_id = ?, cell_id = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                $title,
                $sku,
                $description,
                $price,
                $quantity,
                $supplier_id,
                $cell_id,
                $id
            ));

            $message = '<div class="alert alert-success">Товар успешно обновлён!</div>';

            /* Обновляем данные на странице после сохранения */
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute(array($id));
            $product = $stmt->fetch();

        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Ошибка БД: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

<div class="container">
    <h1 class="mb-3">Редактирование товара</h1>

    <div class="mb-3">
        <a href="/src/Controllers/products.php" class="btn btn-secondary">← Назад к товарам</a>
        <a href="/src/Controllers/admin_panel.php" class="btn btn-dark">Админка</a>
    </div>

    <?php echo $message; ?>

    <form method="POST" class="card p-4 shadow-sm">

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Название товара</label>
                <input
                    type="text"
                    name="title"
                    class="form-control"
                    required
                    value="<?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">SKU</label>
                <input
                    type="text"
                    name="sku"
                    class="form-control"
                    required
                    value="<?php echo htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <label class="form-label">Цена</label>
                <input
                    type="number"
                    name="price"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Количество</label>
                <input
                    type="number"
                    name="quantity"
                    class="form-control"
                    min="0"
                    step="1"
                    value="<?php echo htmlspecialchars($product['quantity'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">ID ячейки</label>
                <input
                    type="number"
                    name="cell_id"
                    class="form-control"
                    min="1"
                    step="1"
                    value="<?php echo htmlspecialchars($product['cell_id'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">ID поставщика</label>
                <input
                    type="number"
                    name="supplier_id"
                    class="form-control"
                    min="1"
                    step="1"
                    value="<?php echo htmlspecialchars($product['supplier_id'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Описание</label>
                <textarea
                    name="description"
                    class="form-control"
                    rows="3"><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">
            Обновить товар
        </button>
    </form>
</div>

</body>
</html>