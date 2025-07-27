<?php
require_once(__DIR__ . '/../../../config.php');

function verifyResetToken($token) {
    $conn = connectDatabase();
    
    // Hash token untuk dicocokkan dengan database
    $token_hash = hash('sha256', $token);
    
    // Cek token di database
    $sql = "SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $user;
}

function resetPassword($data) {
    $conn = connectDatabase();
    
    $token = $data['token'];
    $password = $data['password'];
    $token_hash = hash('sha256', $token);
    
    // Verifikasi token masih valid
    $user = verifyResetToken($token);
    if (!$user) {
        return [
            "status" => false,
            "message" => "Token tidak valid atau telah kedaluwarsa."
        ];
    }
    
    // Hash password baru
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $updatedAt = date("Y-m-d H:i:s");
    
    // Update password dan clear token
    $sql = "UPDATE users SET password = ?, update_at = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashedPassword, $updatedAt, $user['id']);
    
    if ($stmt->execute()) {
        $response = [
            "status" => true,
            "message" => "Password berhasil diupdate. Silakan login dengan password baru Anda."
        ];
    } else {
        $response = [
            "status" => false,
            "message" => "Gagal mengupdate password. Silakan coba lagi."
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    return $response;
}

// Fungsi untuk membuat token reset password (digunakan di halaman lupa password)
function createPasswordResetToken($email) {
    $conn = connectDatabase();
    
    // Cek apakah email terdaftar
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        return [
            "status" => false,
            "message" => "Email tidak terdaftar dalam sistem."
        ];
    }
    
    // Generate token
    $token = bin2hex(random_bytes(16));
    $token_hash = hash('sha256', $token);
    $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 jam kedaluwarsa
    
    // Simpan token ke database
    $sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $token_hash, $expiry, $user['id']);
    
    if ($stmt->execute()) {
        $response = [
            "status" => true,
            "message" => "Token reset password telah dibuat.",
            "token" => $token,
            "email" => $user['email'],
            "name" => $user['name']
        ];
    } else {
        $response = [
            "status" => false,
            "message" => "Gagal membuat token reset password."
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    return $response;
}
?>