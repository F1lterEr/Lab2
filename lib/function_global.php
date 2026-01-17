<?php
function enter($link){
    $error = array();
    
    // Проверяем, что поля не пустые
    if (empty($_POST['login']) || empty($_POST['password'])) {
        $error[] = "Поля не должны быть пустыми!";
        return $error;
    }
    
    $login = $_POST['login'];
    $password = $_POST['password'];
    
    // Защита от SQL-инъекций
    $login = mysqli_real_escape_string($link, $login);
    
    // Ищем пользователя
    $rez = mysqli_query($link, "SELECT * FROM users WHERE login='$login'");
    
    if (mysqli_num_rows($rez) == 1) {
        $row = mysqli_fetch_assoc($rez);
        
        // ТОЧНОЕ СРАВНЕНИЕ пароля
        if ($password === $row['password']) { // Используем строгое сравнение ===
            // Создаем сессию
            $_SESSION['id'] = $row['id'];
            return $error; // Пустой массив - нет ошибок
        } else {
            $error[] = "Неверный пароль";
            return $error;
        }
    } else {
        $error[] = "Неверный логин и пароль";
        return $error;
    }
}

function lastAct($id,$link) {
    $tm = time();
    mysqli_query($link, "UPDATE users SET online='$tm', last_act='$tm' WHERE id='$id'");
}

function login($link){
    ini_set("session.use_trans_sid", true);
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['id']))
    {
        if(isset($_COOKIE['login']) && isset($_COOKIE['password']))
        {
            setcookie("login", "", time() - 1, '/');
            setcookie("password", "", time() - 1, '/');
            setcookie("login", $_COOKIE['login'], time() + 50000, '/');
            setcookie("password", $_COOKIE['password'], time() + 50000, '/');
            $id = $_SESSION['id'];
            lastAct($id,$link);
            return true;
        }
        else
        {
            $rez = mysqli_query($link, "SELECT * FROM users WHERE id={$_SESSION['id']}");
            if (mysqli_num_rows($rez) == 1)
            {
                $row = mysqli_fetch_assoc($rez);
                setcookie("login", $row['login'], time()+50000, '/');
                setcookie("password", $row['password'], time() + 50000, '/');
                $id = $_SESSION['id'];
                lastAct($id,$link);
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    else
    {
        if(isset($_COOKIE['login']) && isset($_COOKIE['password']))
        {
            $rez = mysqli_query($link, "SELECT * FROM users WHERE login='" . mysqli_real_escape_string($link, $_COOKIE['login']) . "'");
            if (mysqli_num_rows($rez) == 1)
            {
                $row = mysqli_fetch_assoc($rez);
                if($row['password'] == $_COOKIE['password'])
                {
                    $_SESSION['id'] = $row['id'];
                    $id = $_SESSION['id'];
                    lastAct($id,$link);
                    return true;
                }
                else
                {
                    setcookie("login", "", time() - 360000, "/");
                    setcookie("password", "", time() - 360000, "/");
                    return false;
                }
            }
            else
            {
                setcookie("login", "", time() - 360000, "/");
                setcookie("password", "", time() - 360000, "/");
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}

function is_admin($id,$link){
    $rez = mysqli_query($link, "SELECT prava FROM users WHERE id='$id'");
    if (mysqli_num_rows($rez) == 1)
    {
        $prava = mysqli_fetch_assoc($rez);
        if ($prava['prava'] == 1) return true;
        else return false;
    }
    else
    return false;
}

function out($link) {
    // Начинаем сессию
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Обновляем статус в БД
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];
        mysqli_query($link, "UPDATE users SET online=0 WHERE id=$id");
    }
    
    // Очищаем сессию
    $_SESSION = array();
    
    // Удаляем куки - устанавливаем время в прошлом
    // Важно: path должен совпадать с тем, что был при установке
    setcookie("login", "", time() - 3600, "/");
    setcookie("password", "", time() - 3600, "/");
    
    // Также удаляем сессионную куку
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Уничтожаем сессию
    session_destroy();
    
    // ПЕРВЫЙ редирект - на страницу с параметром fresh
    // Это заставит браузер отправить новый запрос без старых кук
    header("Location: /lab2/?fresh=1");
    exit();
}
?>