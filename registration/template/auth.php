<html>
<head>
    <meta charset="utf-8" />
    <title>MySite</title>
</head>
<body>
<div>
<?php if (!isset($is_logged_in) || !$is_logged_in): ?>
    <?php if (isset($error) && !empty($error)): ?>
        <h5><?php echo $error[0]; ?></h5>
    <?php endif; ?>
    
    <form action="/lab2/" method="post">
        Логин: <input type="text" name="login" required><br>
        Пароль: <input type="password" name="password" required><br>
        <input type="submit" value="Войти" name="log_in">
    </form>
    <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/lab2/registration/">Зарегистрироваться</a><br>
<?php else: ?>
    <?php include('./main/main.php'); ?>
<?php endif; ?>
</div>
</body>
</html>