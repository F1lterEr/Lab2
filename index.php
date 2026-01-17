<?php
include('lib/connect.php');
include('lib/function_global.php');

// Обработка выхода
if(isset($_GET['action']) && $_GET['action'] == "out") {
    out($link);
    exit();
}

// Инициализируем сессию
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ТОЛЬКО обработка формы входа
$is_logged_in = false;
$UID = null;
$admin = false;
$error = array();

if(isset($_POST['log_in'])) {
    $error = enter($link);
    
    if (count($error) == 0) {
        // Успешный вход
        $is_logged_in = true;
        $UID = $_SESSION['id'];
        $admin = is_admin($UID, $link);
    }
}

// Проверяем существующую сессию (только если не было отправки формы)
if (!$is_logged_in && isset($_SESSION['id'])) {
    $is_logged_in = true;
    $UID = $_SESSION['id'];
    $admin = is_admin($UID, $link);
}

// Отображение страницы
if ($is_logged_in) {
    include('./main/main.php');
} else {
    include('registration/template/auth.php');
}
?>