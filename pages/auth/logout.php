<?php
    session_start();
    include '../../includes/crud/crud-auth/crud-login.php';

    $_SESSION = array();
    session_destroy();

    setcookie('trashit_login', '', time() - 3600, '/');

    header("Location: form-login.php");
    exit;
?>