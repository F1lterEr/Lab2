<?php
if (!isset($link) || !isset($UID)) {
    die("Ошибка: Недостаточно данных");
}

$rez = mysqli_query($link, "SELECT * FROM users WHERE id='$UID'");
$ans = mysqli_fetch_assoc($rez);

if (!$ans) {
    die("Пользователь не найден");
}

echo "<h1>Привет, " . $ans['login'] . ".</h1>";
echo "<a href='/lab2/?action=out'>Выход</a>"; // ИСПРАВЛЕНА ССЫЛКА

if ($admin) { // если зашел админ – выводим ему таблицу со всеми пользователями и управлением ими
    echo "
    <div>
    <p>Этот раздел виден только администратору</p>
    <a href='http://" . $_SERVER['HTTP_HOST'] . "/lab2/registration/'>Добавить пользователя</a><br>
    <table border='1'>
    <thead>
    <tr>
    <th>#</th>
    <th>Ид</th>
    <th>Логин</th>
    <th>Дата регистрации</th>
    <th>Админ?</th>
    <th>Изменить пароль</th>
    <th>Удалить</th>
    </tr>
    </thead>
    <tbody>
    ";
    
    // Вызов функции отображения таблицы пользователей
    echo userTable($link);
    
    echo "
    </tbody>
    </table>
    </div>";
    
    // Обработка изменения пароля (админом для других пользователей)
    if (isset($_POST['change_pass_id']) && isset($_POST['new_password']) && $admin) {
        $password = $_POST['new_password'];
        $id = $_POST['change_pass_id'];
        $salt = mt_rand(100, 999);
        $password_hash = md5(md5($password) . $salt);
        
        if (mysqli_query($link, "UPDATE users SET password='$password_hash', salt='$salt' WHERE id='$id'")) {
            echo '<p style="color:green;">Пароль пользователя с id=' . $id . ' изменен</p>';
        } else {
            echo '<p style="color:red;">Произошла ошибка</p>';
        }
    }
    
    // Обработка удаления пользователя
    if (isset($_POST['delete_id']) && $admin) {
        $id = $_POST['delete_id'];
        // Проверяем, что удаляем не админа и не себя
        $rez = mysqli_query($link, "SELECT prava FROM users WHERE id='$id'");
        $user = mysqli_fetch_assoc($rez);
        
        if ($user['prava'] != 1 && $id != $UID) {
            if (mysqli_query($link, "DELETE FROM users WHERE id='$id'")) {
                echo '<p style="color:green;">Пользователь удален</p>';
            } else {
                echo '<p style="color:red;">Произошла ошибка</p>';
            }
        } else {
            echo '<p style="color:red;">Нельзя удалить администратора или себя</p>';
        }
    }
} else {
    // Обычный пользователь - может менять только свой пароль
    echo "
    <div>
    <p>Ваши данные:</p>
    <p>Логин: " . $ans['login'] . "</p>
    <p>Дата регистрации: " . date('d.m.Y', strtotime($ans['reg_date'])) . "</p>
    
    <form method='post'>
    Изменить свой пароль на: 
    <input type='password' name='password1' required>
    <input type='submit' value='Изменить пароль'>
    </form>
    </div>";
    
    // Обработка изменения своего пароля
    if (isset($_POST['password1'])) {
        $password = $_POST['password1'];
        $salt = mt_rand(100, 999);
        $password_hash = md5(md5($password) . $salt);
        
        if (mysqli_query($link, "UPDATE users SET password='$password_hash', salt='$salt' WHERE id='$UID'")) {
            echo '<p style="color:green;">Ваш пароль изменен</p>';
        } else {
            echo '<p style="color:red;">Произошла ошибка</p>';
        }
    }
}
?>