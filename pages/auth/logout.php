<?php
session_start();
include '../../includes/crud/crud-auth/crud-login.php';

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hapus cookie remember me
clearRememberMeCookie();

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: form-login.php");
exit;
?>