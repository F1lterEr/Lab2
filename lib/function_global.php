<?php
date_default_timezone_set('Europe/Moscow');
// Генерация случайной соли
function gen_salt() {
    return substr(md5(uniqid()), 0, 8);
}

// Создание MD5 хэша пароля с солью
function make_password_hash($password, $salt) {
    return md5(md5($salt) . md5($password));
}

// Проверка корректности данных формы регистрации
function registrationCorrect($link) {
    if ($_POST['login'] == "") return false;
    if ($_POST['password1'] == "") return false;
    if ($_POST['password2'] == "") return false;
    if (strlen($_POST['password1']) < 5) return false;
    if ($_POST['password1'] != $_POST['password2']) return false;
    
    $login = mysqli_real_escape_string($link, $_POST['login']);
    $rez = mysqli_query($link, "SELECT * FROM users WHERE login='$login'");
    if (mysqli_num_rows($rez) != 0) return false;
    
    return true;
}

// Проверка логина (для AJAX)
function checkLogin($link) {
    if (isset($_GET['login'])) {
        $login = mysqli_real_escape_string($link, $_GET['login']);
        $rez = mysqli_query($link, "SELECT * FROM users WHERE login='$login'");
        if (mysqli_num_rows($rez) != 0) echo '1';
        else echo '0';
    }
}

// Функция входа с проверкой MD5 хэша
function enter($link){
    $error = array();
    
    if ($_POST['login'] != "" && $_POST['password'] != "") {
        $login = $_POST['login'];
        $password = $_POST['password'];
        
        $login_escaped = mysqli_real_escape_string($link, $login);
        $rez = mysqli_query($link, "SELECT * FROM users WHERE login='$login_escaped'");
        
        if (mysqli_num_rows($rez) == 1) {
            $row = mysqli_fetch_assoc($rez);
            
            // ПРОВЕРКА ПАРОЛЯ - ФОРМУЛА md5(md5($password) . $salt)
            if (isset($row['salt'])) {
                $input_hash = md5(md5($password) . $row['salt']);
                
                if ($input_hash == $row['password']) {
                    setcookie("login", $row['login'], time() + 50000);
                    setcookie("password", md5($row['login'] . $row['password']), time() + 50000);
                    $_SESSION['id'] = $row['id'];
                    $id = $_SESSION['id'];
                    lastAct($id, $link);
                    return $error;
                } else {
                    // Отладка
                    echo "Отладка входа:<br>";
                    echo "Введенный пароль: $password<br>";
                    echo "Соль из БД: " . $row['salt'] . "<br>";
                    echo "Хэш из введенного: " . md5(md5($password) . $row['salt']) . "<br>";
                    echo "Хэш из БД: " . $row['password'] . "<br>";
                    
                    $error[] = "Неверный пароль";
                    return $error;
                }
            } else {
                // Старая система для обратной совместимости
                if ($password == $row['password']) {
                    setcookie("login", $row['login'], time() + 50000);
                    setcookie("password", md5($row['login'] . $row['password']), time() + 50000);
                    $_SESSION['id'] = $row['id'];
                    $id = $_SESSION['id'];
                    lastAct($id, $link);
                    return $error;
                } else {
                    $error[] = "Неверный пароль";
                    return $error;
                }
            }
        } else {
            $error[] = "Неверный логин и пароль";
            return $error;
        }
    } else {
        $error[] = "Поля не должны быть пустыми!";
        return $error;
    }
}

// Функция авторизации через куки/сессию
function login($link){
    ini_set("session.use_trans_sid", true);
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['id'])) {
        if(isset($_COOKIE['login']) && isset($_COOKIE['password'])) {
            setcookie("login", "", time() - 1, '/');
            setcookie("password", "", time() - 1, '/');
            setcookie("login", $_COOKIE['login'], time() + 50000, '/');
            setcookie("password", $_COOKIE['password'], time() + 50000, '/');
            $id = $_SESSION['id'];
            lastAct($id, $link);
            return true;
        } else {
            $rez = mysqli_query($link, "SELECT * FROM users WHERE id={$_SESSION['id']}");
            if (mysqli_num_rows($rez) == 1) {
                $row = mysqli_fetch_assoc($rez);
                $cookie_hash = md5($row['login'] . $row['password']);
                setcookie("login", $row['login'], time() + 50000, '/');
                setcookie("password", $cookie_hash, time() + 50000, '/');
                $id = $_SESSION['id'];
                lastAct($id, $link);
                return true;
            } else {
                return false;
            }
        }
    } else {
        if(isset($_COOKIE['login']) && isset($_COOKIE['password'])) {
            $login_escaped = mysqli_real_escape_string($link, $_COOKIE['login']);
            $rez = mysqli_query($link, "SELECT * FROM users WHERE login='$login_escaped'");
            
            if (mysqli_num_rows($rez) == 1) {
                $row = mysqli_fetch_assoc($rez);
                $cookie_hash = md5($row['login'] . $row['password']);
                
                if($cookie_hash == $_COOKIE['password']) {
                    $_SESSION['id'] = $row['id'];
                    $id = $_SESSION['id'];
                    lastAct($id, $link);
                    return true;
                } else {
                    setcookie("login", "", time() - 360000, "/");
                    setcookie("password", "", time() - 360000, "/");
                    return false;
                }
            } else {
                setcookie("login", "", time() - 360000, "/");
                setcookie("password", "", time() - 360000, "/");
                return false;
            }
        } else {
            return false;
        }
    }
}

// Функция выхода
function out($link) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];
        mysqli_query($link, "UPDATE users SET online=0 WHERE id=$id");
    }
    
    $_SESSION = array();
    
    setcookie("login", "", time() - 3600, "/");
    setcookie("password", "", time() - 3600, "/");
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    header("Location: /lab2/");
    exit();
}

// Обновление времени последней активности
function lastAct($id, $link) {
    $tm = time();
    mysqli_query($link, "UPDATE users SET online='$tm', last_act='$tm' WHERE id='$id'");
}

// Проверка, является ли пользователь админом
function is_admin($id, $link){
    $rez = mysqli_query($link, "SELECT prava FROM users WHERE id='$id'");
    if (mysqli_num_rows($rez) == 1) {
        $prava = mysqli_fetch_assoc($rez);
        if ($prava['prava'] == 1) return true;
        else return false;
    } else {
        return false;
    }
}

// Функция отображения таблицы пользователей (для админов)
function userTable($link) {
    $rez = mysqli_query($link, "SELECT * FROM users");
    $output = '';
    $i = 0;
    
    while ($ans = mysqli_fetch_assoc($rez)) {
        $i++;
        $output .= '<tr>';
        $output .= '<td>' . $ans['id'] . '</td>';
        $output .= '<td>' . $ans['login'] . '</td>';
        $output .= '<td>' . date('d.m.Y H:i:s', strtotime($ans['reg_date'])) . '</td>';
        $output .= '<td>' . ($ans['prava'] == 1 ? 'Админ' : 'Пользователь') . '</td>';
        
        if ($ans['prava'] != 1) {
            $output .= '<td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="change_pass_id" value="' . $ans['id'] . '">
                    <input type="password" name="new_password" placeholder="Новый пароль">
                    <input type="submit" value="Изменить пароль">
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_id" value="' . $ans['id'] . '">
                    <input type="submit" value="Удалить" onclick="return confirm(\'Удалить пользователя?\')">
                </form>
            </td>';
        } else {
            $output .= '<td>—</td>';
        }
        
        $output .= '</tr>';
    }
    
    return $output;
}
?>