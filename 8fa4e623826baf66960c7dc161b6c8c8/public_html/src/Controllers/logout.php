<?php
session_start();
session_destroy();

// Перенаправление на страницу входа
header("Location: /src/Controllers/login.php");
exit;
