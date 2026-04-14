<?php
session_start();
require __DIR__ . '/../../config/db.php';

// Генерация CSRF (если нет)
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $_SESSION['csrf_token'] = sha1(uniqid('', true));
    }
}

// Только для вошедших
if (!isset($_SESSION['user_id'])) {
    header('Location: /src/Controllers/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Смена пароля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-4">

<div class="card shadow-sm">
<div class="card-body">

<h4 class="text-center mb-4">Смена пароля</h4>

<form action="/src/Controllers/update_profile.php" method="POST">

<!-- CSRF -->
<input type="hidden" name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

<div class="mb-3">
<label class="form-label">Старый пароль</label>
<input type="password" name="old_password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Новый пароль</label>
<input type="password" name="new_password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Повторите новый пароль</label>
<input type="password" name="new_password2" class="form-control" required>
</div>

<button type="submit" class="btn btn-primary w-100">
Сохранить новый пароль
</button>

</form>

<div class="text-center mt-3">
<a href="/src/Controllers/profile.php">← Назад в профиль</a>
</div>

</div>
</div>

</div>
</div>
</div>

</body>
</html>
