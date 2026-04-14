<?php
require __DIR__ . '/../Models/check_admin.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Админка</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-5 bg-light">

<div class="container">

<div class="alert alert-success shadow-sm">
<h1>Панель Администратора</h1>
<p>Добро пожаловать, Повелитель</p>
<p>Здесь вы управляете системой склада (WMS)</p>
</div>

<!-- КНОПКИ УПРАВЛЕНИЯ -->
<div class="d-flex gap-3 flex-wrap mt-3">

<a href="/src/Controllers/add_item.php" class="btn btn-success">
+ Добавить товар
</a>

<a href="/src/Controllers/products.php" class="btn btn-primary">
Список товаров
</a>

<a href="/src/Controllers/products.php" class="btn btn-primary">
Поиск товара по SKU
</a>

<a href="/src/Controllers/upload_invoice.php" class="btn btn-warning">
Скан накладной
</a>

<a href="/src/Controllers/invoice_scans.php" class="btn btn-outline-dark">
Список сканов
</a>

<!-- НОВАЯ КНОПКА -->
<a href="/src/Controllers/admin_orders.php" class="btn btn-warning">
 Редактор заказов
</a>

<a href="/src/Controllers/main.php" class="btn btn-secondary">
На главную
</a>


<a href="/src/Controllers/logout.php" class="btn btn-danger">
Выйти
</a>

</div>

</div>

</body>
</html>
