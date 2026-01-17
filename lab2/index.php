<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('lib/connect.php');
include('lib/function_global.php');

// Обработка выхода - ПЕРВОЕ ДЕЛО
if(isset($_GET['action']) && $_GET['action'] == "out") {
    out($link);
    exit();
}

// Специальный флаг после выхода - игнорируем все куки
if (isset($_GET['fresh'])) {
    // Удаляем куки на клиенте еще раз
    setcookie("login", "", time() - 3600, "/");
    setcookie("password", "", time() - 3600, "/");
    
    // Показываем чистую форму входа
    include('registration/template/auth.php');
    exit();
}

// Инициализируем сессию
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Обработка формы входа
if(isset($_POST['log_in'])) {
    $error = enter($link);
    
    if (count($error) == 0) {
        // Успешный вход
        header("Location: /lab2/");
        exit();
    }
}

// Проверка авторизации
$is_logged_in = false;
$UID = null;
$admin = false;

// 1. Проверяем сессию
if (isset($_SESSION['id'])) {
    $is_logged_in = true;
    $UID = $_SESSION['id'];
    $admin = is_admin($UID, $link);
} 
// 2. Если нет сессии, проверяем куки
elseif (isset($_COOKIE['login']) && isset($_COOKIE['password'])) {
    echo "Отладка: Обнаружены куки, проверяем...<br>";
    
    $login = $_COOKIE['login'];
    $password = $_COOKIE['password'];
    
    $rez = mysqli_query($link, "SELECT * FROM users WHERE login='" . mysqli_real_escape_string($link, $login) . "'");
    
    if (mysqli_num_rows($rez) == 1) {
        $row = mysqli_fetch_assoc($rez);
        if ($row['password'] == $password) {
            echo "Отладка: Куки валидны, создаем сессию<br>";
            $_SESSION['id'] = $row['id'];
            $is_logged_in = true;
            $UID = $row['id'];
            $admin = is_admin($UID, $link);
            lastAct($UID, $link);
        } else {
            echo "Отладка: Пароль не совпадает<br>";
            // Удаляем невалидные куки
            setcookie("login", "", time() - 3600, "/");
            setcookie("password", "", time() - 3600, "/");
        }
    } else {
        echo "Отладка: Пользователь не найден<br>";
        setcookie("login", "", time() - 3600, "/");
        setcookie("password", "", time() - 3600, "/");
    }
}

// Отображение страницы
if ($is_logged_in) {
    include('./main/main.php');
} else {
    include('registration/template/auth.php');
}
?>