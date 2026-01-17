<?php
include('../lib/connect.php');
include('../lib/function_global.php');

$regged = false;
$regged_error = false;

if (isset($_POST['GO'])) {
    if (registrationCorrect($link)) {
        // Получаем данные из формы
        $login = mysqli_real_escape_string($link, $_POST['login']);
        $password = $_POST['password1'];
        $is_admin = isset($_POST['adm']) ? 1 : 0;
        
        // Генерируем соль и хэш пароля
        $salt = mt_rand(100, 999);
        // ИСПРАВЛЕНО: md5(md5($password) . $salt) вместо md5(md5($salt) . md5($password))
        $password_hash = md5(md5($password) . $salt);
        
        // Отладка
        echo "Отладка регистрации:<br>";
        echo "Логин: $login<br>";
        echo "Пароль: $password<br>";
        echo "Соль: $salt<br>";
        echo "Хэш: $password_hash<br>";
        
        // Добавляем пользователя в БД
        $query = "INSERT INTO users (login, password, salt, prava, reg_date) 
                  VALUES ('$login', '$password_hash', '$salt', '$is_admin', NOW())";
        
        echo "SQL: $query<br>";
        
        if (mysqli_query($link, $query)) {
            echo "Успешно добавлено в БД!<br>";
            $regged = true;
        } else {
            echo "Ошибка SQL: " . mysqli_error($link) . "<br>";
            $regged_error = true;
        }
    } else {
        echo "Данные формы некорректны!<br>";
        $regged_error = true;
    }
}

include('template/registration.php');
?>