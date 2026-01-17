<html>
<head>
    <meta charset="utf-8" />
    <title>Регистрация</title>
</head>
<body>
<div>
<?php
if ($regged == true) {
    echo '
    Регистрация прошла успешно <br>
    <a href="http://' . $_SERVER['HTTP_HOST'] . '/lab2/">На главную</a><br>
    <a href="http://' . $_SERVER['HTTP_HOST'] . '/lab2/registration/">Зарегистрировать еще</a>
    ';
} else {
    if ($regged_error == true) {
        echo '<p>Ошибка в заполнении формы!</p>';
    }
    echo '
    <form id="reg_form" method="post" action="index.php" onsubmit="return isValidForm()">
    Логин*: <input id="login" type="text" name="login" /><br />
    Пароль*: <input id="pass" type="password" name="password1" /><br />
    Подтверждение*: <input id="re_pass" type="password" name="password2" /><br />
    <label><input id="adm" type="checkbox" name="adm" value="1" /> Сделать администратором</label><br />
    <input type="submit" name="GO" value="Регистрация">
    </form>
    <a href="http://' . $_SERVER['HTTP_HOST'] . '/lab2/">На главную</a>
    ';
}
?>
</div>
<script type="text/javascript" src="/lab2/js/regform.js?<?php echo time(); ?>"></script>
</body>
</html>