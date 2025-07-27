<?php
require_once(__DIR__ . '/../../../config.php');

function Register($data) {
    $conn = connectDatabase();
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];
    $confirmPassword = $data['confirm-password'];
    $role = 'teacher'; // Default role sesuai kebutuhan
    $createdAt = date("Y-m-d H:i:s");

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        return [
            "status" => false,
            "message" => "Semua field harus diisi!",
            "errorField" => "all"
        ];
    }

    if ($password !== $confirmPassword) {
        return [
            "status" => false,
            "message" => "Password dan Konfirmasi Password tidak cocok!",
            "errorField" => "confirmPassword"
        ];
    }

    if (strlen($password) < 8) {
        return [
            "status" => false,
            "message" => "Password harus minimal 8 karakter!",
            "errorField" => "password"
        ];
    }

    // Cek username atau email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Cek apakah username atau email yang sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        return [
            "status" => false,
            "message" => $stmt->num_rows > 0 ? "Username sudah terdaftar!" : "Email sudah terdaftar!",
            "errorField" => $stmt->num_rows > 0 ? "username" : "email"
        ];
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashedPassword, $role, $createdAt);

    if ($stmt->execute()) {
        return [
            "status" => true,
            "message" => "Registrasi berhasil!",
            "user_id" => $conn->insert_id
        ];
    } else {
        return [
            "status" => false,
            "message" => "Gagal melakukan registrasi: " . $conn->error,
            "errorField" => null
        ];
    }
}
?>