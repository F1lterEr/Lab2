<?php
if (!isset($link) || !isset($UID)) {
    die("Ошибка: Недостаточно данных");
}

$rez = mysqli_query($link, "SELECT * FROM users WHERE id='$UID'");
$ans = mysqli_fetch_assoc($rez);

if (!$ans) {
    die("Пользователь не найден");
}

echo "<h1>Привет, {$ans['login']}</h1>";
echo "<a href='/lab2/?action=out'>Выход</a>";

if (isset($admin) && $admin) {
    echo '<div>
    <p>Этот раздел виден только админам</p>
    </div>';
}
?>