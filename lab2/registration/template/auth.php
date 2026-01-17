<html>
<head>
    <meta charset="utf-8" />
    <title>MySite</title>
</head>
<body>
<div>
<?php if (!isset($is_logged_in) || !$is_logged_in): ?>
    <?php if (isset($error) && !empty($error)): ?>
        <div style="color: red;"><?php echo $error[0]; ?></div>
    <?php endif; ?>
    
    <form action="/lab2/" method="post">
        Логин: <input type="text" name="login" required><br>
        Пароль: <input type="password" name="password" required><br>
        <input type="submit" value="Войти" name="log_in">
    </form>
<?php else: ?>
    <?php include('./main/main.php'); ?>
<?php endif; ?>
</div>
</body>
</html>