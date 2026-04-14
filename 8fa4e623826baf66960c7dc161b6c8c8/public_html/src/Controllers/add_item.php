<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../Models/check_admin.php';

$message = '';

$post_title = '';
$post_sku = '';
$post_price = '0';
$post_qty = '0';
$post_desc = '';
$post_supplier_id = '';
$post_cell_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $post_title = trim($_POST['title']);
    $post_sku   = trim($_POST['sku']);
    $post_price = trim($_POST['price']);
    $post_qty   = trim($_POST['quantity']);
    $post_desc  = trim($_POST['description']);

    $post_supplier_id = trim($_POST['supplier_id']);
    $post_cell_id     = trim($_POST['cell_id']);

    if ($post_title === '' || $post_sku === '') {
        $message = '<div class="alert alert-danger">Заполните название и SKU</div>';
    } else {

        $price = (float)$post_price;
        $qty   = (int)$post_qty;

        $supplier_id = $post_supplier_id === '' ? null : (int)$post_supplier_id;
        $cell_id     = $post_cell_id === '' ? null : (int)$post_cell_id;

        $img = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

            $uploadDir = __DIR__ . '/../../uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $img = '/uploads/' . $fileName;
            }
        }

        $sql = "INSERT INTO products
                (title, sku, description, price, quantity, supplier_id, cell_id, image_url)
                VALUES
                (:t, :sku, :d, :p, :q, :sid, :cid, :i)";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':t'   => $post_title,
                ':sku' => $post_sku,
                ':d'   => $post_desc,
                ':p'   => $price,
                ':q'   => $qty,
                ':sid' => $supplier_id,
                ':cid' => $cell_id,
                ':i'   => $img
            ]);

            $message = '<div class="alert alert-success">Товар успешно добавлен!</div>';

        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">

    <h1>Добавление товара</h1>
    <a href="/src/Controllers/main.php" class="btn btn-secondary mb-3">← Назад</a>

    <?php echo $message; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">

        <input type="text" name="title" class="form-control mb-3" placeholder="Название" required>
        <input type="text" name="sku" class="form-control mb-3" placeholder="SKU" required>
        <input type="number" name="price" class="form-control mb-3" placeholder="Цена">
        <input type="number" name="quantity" class="form-control mb-3" placeholder="Количество">

        <input type="number" name="supplier_id" class="form-control mb-3" placeholder="ID поставщика">
        <input type="number" name="cell_id" class="form-control mb-3" placeholder="ID ячейки">

        <textarea name="description" class="form-control mb-3" placeholder="Описание"></textarea>

        <label class="form-label">Фото товара</label>
        <input type="file" name="image" class="form-control mb-3">

        <button type="submit" class="btn btn-success">Сохранить</button>
    </form>

</div>
</body>
</html>