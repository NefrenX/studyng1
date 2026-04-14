<?php
session_start();

// CSRF token (совместимо со старыми PHP)
if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {

    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));

    } else {
        $_SESSION['csrf_token'] = sha1(uniqid('', true) . mt_rand());
    }
}

require __DIR__ . '/../../config/db.php';

$errorMsg = '';
$email = '';

if (isset($_SESSION['user_id'])) {
header('Location: /src/Controllers/main.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $pass  = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $pass === '') {
        $errorMsg = 'Заполните все поля!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Некорректный формат Email!';
    } else {

        $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute(array($email));
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: main.php');
            exit;

        } else {
            $errorMsg = 'Неверный логин или пароль!';
        }
    }
}
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-4">Вход на сайт</h4>

                    <?php if ($errorMsg !== ''): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/src/Controllers/login.php"
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required autofocus
                                   value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Войти</button>
                    </form>

                    <div class="text-center mt-3">
                        <small>Нет аккаунта? <a href="/src/Controllers/register.php">Регистрация</a></small>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
