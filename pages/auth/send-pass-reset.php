<?php

use Dba\Connection;

require_once('../../config.php');

$email = $_POST['email'];

$token = bin2hex(random_bytes(16));
$token_hash = hash('sha256', $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

$conn = connectDatabase();

// Pertama, ambil username berdasarkan email
$sql_get_username = "SELECT username FROM users WHERE email = ?";
$stmt_get = $conn->prepare($sql_get_username);
$stmt_get->bind_param("s", $email);
$stmt_get->execute();
$result = $stmt_get->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $username = $user['username'];
    
    // Lanjutkan dengan update token
    $sql_update = "UPDATE users
            SET reset_token_hash = ?,
                reset_token_expires_at = ?
            WHERE email = ?";

    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sss", $token_hash, $expiry, $email);
    $stmt_update->execute();

    if ($conn->affected_rows) {
        $mail = require __DIR__ . '/mailer.php';

        $mail->setFrom("neorozatech@gmail.com", "Neoroza Tech");
        $mail->addAddress($email);
        $mail->Subject = "Permintaan Reset Password - Neoroza Tech";
        
        // Email content dengan username
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { color: #2c3e50; font-size: 24px; margin-bottom: 20px; }
                .content { margin-bottom: 30px; }
                .button { 
                    display: inline-block; 
                    padding: 10px 20px; 
                    background-color: #3498db; 
                    color: white !important; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 15px 0;
                }
                .footer { 
                    margin-top: 30px; 
                    font-size: 12px; 
                    color: #7f8c8d; 
                    border-top: 1px solid #eee;
                    padding-top: 10px;
                }
                .warning { color: #e74c3c; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>Reset Password - Fashion 24</div>
                
                <div class='content'>
                    <p>Halo, <strong>$username</strong>,</p>
                    
                    <p>Kami menerima permintaan untuk mereset password akun Fashion 24 Anda. 
                    Silakan klik tombol di bawah ini untuk melanjutkan proses reset password:</p>
                    
                    <p><a href='https://backend24.site/Rian/XI/recode/presence-nceec/pages/auth/form-reset-pass.php?token=$token' class='button'>Reset Password Saya</a></p>
                    
                    <p>Atau salin dan tempel link berikut ke browser Anda:<br>
                    <small>https://backend24.site/Rian/XI/recode/presence-nceec/pages/auth/form-reset-pass.php?token=$token</small></p>
                    
                    <p class='warning'>Link ini akan kadaluarsa dalam 30 menit. Jika Anda tidak meminta reset password, 
                    Anda dapat mengabaikan email ini dan password Anda tidak akan berubah.</p>
                    
                    <p>Terima kasih,<br>
                    Tim Neoroza Tech</p>
                </div>
                
                <div class='footer'>
                    <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                    <p>&copy; " . date('Y') . " Neoroza Tech. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Reset Password - Fashion 24\n\n"
            . "Halo, $username,\n\n"
            . "Kami menerima permintaan untuk mereset password akun Fashion 24 Anda. "
            . "Silakan kunjungi link berikut untuk melanjutkan proses reset password:\n\n"
            . "https://backend24.site/Rian/XI/recode/ventra-web/Login/reset-pass.php?token=$token\n\n"
            . "Link ini akan kadaluarsa dalam 30 menit. Jika Anda tidak meminta reset password, "
            . "Anda dapat mengabaikan email ini dan password Anda tidak akan berubah.\n\n"
            . "Terima kasih,\n"
            . "Tim Neoroza Tech";

        try {
            $mail->send();
            echo "<script>alert('Link reset password telah dikirim ke email Anda.'); window.location.href = 'form-login.php';</script>";
        } catch (Exception $e) {
            error_log("Gagal mengirim email: " . $e->getMessage());
            echo "<script>alert('Gagal mengirim email. Silakan coba lagi atau hubungi admin.'); window.location.href = 'forgot-pass.php';</script>";
        }
    } else {
        echo "<script>alert('Gagal memproses permintaan reset password.'); window.location.href = 'forgot-pass.php';</script>";
    }
} else {
    echo "<script>alert('Email tidak ditemukan.'); window.location.href = 'forgot-pass.php';</script>";
}