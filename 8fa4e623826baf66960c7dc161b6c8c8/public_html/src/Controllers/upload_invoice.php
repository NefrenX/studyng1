<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../Models/check_admin.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';

    if ($title === '') {
        $message = '<div class="alert alert-danger">Введите название накладной.</div>';
    } elseif (!isset($_FILES['invoice_file']) || $_FILES['invoice_file']['error'] !== 0) {
        $message = '<div class="alert alert-danger">Выберите файл изображения.</div>';
    } else {
        $allowed = array('image/jpeg', 'image/png', 'image/webp');
        $mime = mime_content_type($_FILES['invoice_file']['tmp_name']);

        if (!in_array($mime, $allowed)) {
            $message = '<div class="alert alert-danger">Разрешены только JPG, PNG, WEBP.</div>';
        } else {
            $uploadDir = __DIR__ . '/uploads/invoices/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = pathinfo($_FILES['invoice_file']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $target = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['invoice_file']['tmp_name'], $target)) {
                $imagePath = 'uploads/invoices/' . $filename;

                $stmt = $pdo->prepare("INSERT INTO invoice_scans (title, image_path) VALUES (?, ?)");
                $stmt->execute(array($title, $imagePath));

                $message = '<div class="alert alert-success">Скан накладной загружен.</div>';
            } else {
                $message = '<div class="alert alert-danger">Не удалось сохранить файл.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузить накладную</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container">
    <h1 class="mb-3">Загрузка скана накладной</h1>
    <a href="admin_panel.php" class="btn btn-secondary mb-3">← Назад</a>

    <?php echo $message; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Файл накладной</label>
            <input type="file" name="invoice_file" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
        </div>

        <button type="submit" class="btn btn-primary">Загрузить</button>
    </form>
</div>
</body>
</html>