<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../Models/check_admin.php';

$stmt = $pdo->query("SELECT * FROM invoice_scans ORDER BY id DESC");
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сканы накладных</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Сканы накладных</h1>
        <div class="d-flex gap-2">
            <a href="/src/Controllers/upload_invoice.php" class="btn btn-success">+ Загрузить</a>
            <a href="/src/Controllers/admin_panel.php" class="btn btn-secondary">Админка</a>
        </div>
    </div>

    <div class="row">
        <?php if (!empty($files)): ?>
            <?php foreach ($files as $f): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <img src="<?php echo htmlspecialchars($f['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                             class="card-img-top"
                             style="height:240px;object-fit:cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($f['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <a href="<?php echo htmlspecialchars($f['image_path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary w-100">
                                Открыть
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Сканов пока нет.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>