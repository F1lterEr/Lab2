<?php
include ('lib/connect.php');
include ('lib/function_global.php');

if(isset($_GET['action']) && $_GET['action'] == "out") {
    out($link);
}

if (login($link))
{
    $UID = $_SESSION['id'];
    $admin = is_admin($UID,$link);
}

if(isset($_POST['log_in']))
{
    $error = enter($link);

    if (count($error) == 0)
    {
        $UID = $_SESSION['id'];
        $admin = is_admin($UID,$link);
    }

    include ('registration/template/auth.php');
}
?>