<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require __DIR__ . '/../../config/db.php';

// Проверка входа
if (!isset($_SESSION['user_id'])) {
    die('Сначала войдите.');
}

// Проверка CSRF
if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    die('CSRF ошибка!');
}

$user_id = (int)$_SESSION['user_id'];

$old = isset($_POST['old_password']) ? $_POST['old_password'] : '';
$new = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$new2 = isset($_POST['new_password2']) ? $_POST['new_password2'] : '';

if ($new !== $new2) {
    die('Новые пароли не совпадают!');
}

// Берём текущий пароль из БД
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
$stmt->execute(array($user_id));
$user = $stmt->fetch();

if (!$user || !password_verify($old, $user['password_hash'])) {
    die('Старый пароль неверный!');
}

// Хешируем новый
$new_hash = password_hash($new, PASSWORD_DEFAULT);

// Обновляем
$stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
$stmt->execute(array($new_hash, $user_id));

echo "Пароль успешно изменён! <a href='/src/Controllers/profile.php'>Назад</a>";