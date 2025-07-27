<?php
session_start();
include '../../../includes/crud/crud-auth/crud-login.php';

// Pengecekan session utama
if (!isset($_SESSION['username']) || !isset($_SESSION['ID']) || empty($_SESSION['role'])) {
    $user_data = checkLoginCookie();
    
    if ($user_data) {
        // Set session dari cookie
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['ID'] = $user_data['id'];
        $_SESSION['role'] = $user_data['role'];
    } else {
        // Jika tidak ada session DAN tidak ada cookie yang valid
        header("Location: ../../auth/form-login.php");
        exit();
    }
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