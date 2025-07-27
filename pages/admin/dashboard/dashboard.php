<?php
// Tambahkan ini dulu
ini_set('session.cookie_lifetime', 0); // session hilang saat browser ditutup

session_start();
include '../../../includes/crud/crud-auth/crud-login.php';

// Pengecekan session utama
if (!isset($_SESSION['username']) && !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}
?>


<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Hello World !</h1>
    <a href="../../auth/logout.php">logout</a>

    <!-- Debugging info -->
    <div style="background:#f0f0f0; padding:10px; margin-top:20px;">
        <h3>Debug Info:</h3>
        <p>Username: <?php echo $_SESSION['username'] ?? 'Not set'; ?></p>
        <p>User ID: <?php echo $_SESSION['ID'] ?? 'Not set'; ?></p>
        <p>Role: <?php echo $_SESSION['role'] ?? 'Not set'; ?></p>
    </div>
</body>

</html>