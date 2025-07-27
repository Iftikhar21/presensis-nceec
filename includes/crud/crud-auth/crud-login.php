<?php
require_once(__DIR__ . '/../../../config.php');

function Login($data) {
    $identifier = $data['identifier']; // Menggunakan identifier bukan email
    $password = $data['password'];
    $remember = isset($data['remember']) ? true : false;

    $conn = connectDatabase();

    // Query yang menerima email ATAU username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        return [
            "status" => false,
            "message" => "Username/Email tidak ditemukan!",
            "invalid_credentials" => true
        ];
    }

    // Cek percobaan login
    $safe_username = preg_replace('/[^a-zA-Z0-9_]/', '_', $identifier);
    $attempts = isset($_COOKIE["login_attempts_$safe_username"]) ? (int)$_COOKIE["login_attempts_$safe_username"] : 0;
    $timeout_until = isset($_COOKIE["timeout_until_$safe_username"]) ? (int)$_COOKIE["timeout_until_$safe_username"] : 0;

    if (time() < $timeout_until) {
        $remaining = $timeout_until - time();
        return [
            "status" => false,
            "message" => "Terlalu banyak percobaan. Coba lagi dalam " . ceil($remaining / 60) . " menit.",
            "timeout" => true
        ];
    }

    // Verifikasi password
    if (!password_verify($password, $user['password'])) {
        $attempts++;
        $timeout_minutes = 0;

        if ($attempts >= 3) {
            $timeout_minutes = 3 + ($attempts - 3);
            setcookie("timeout_until_$safe_username", time() + ($timeout_minutes * 60), time() + 3600, '/');
        }

        setcookie("login_attempts_$safe_username", $attempts, time() + 3600, '/');

        return [
            "status" => false,
            "message" => "Username/Email atau password salah! Percobaan ke-$attempts",
            "attempts" => $attempts
        ];
    }

    // Bersihkan cookies attempt
    setcookie("login_attempts_$safe_username", '', time() - 3600, '/');
    setcookie("timeout_until_$safe_username", '', time() - 3600, '/');

    // Set session atau cookie remember
    if ($remember) {
        $cookie_data = json_encode([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'expires' => time() + (30 * 24 * 60 * 60) // 30 hari
        ]);
        $cookie_value = base64_encode($cookie_data);
        setcookie('trashit_login', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    return [
        "status" => true,
        "user" => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ];
}

function checkLoginCookie() {
    if (!isset($_COOKIE['trashit_login'])) return false;

    try {
        $cookie_data = base64_decode($_COOKIE['trashit_login']);
        $data = json_decode($cookie_data, true);

        if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
            setcookie('trashit_login', '', time() - 3600, '/');
            return false;
        }

        // Perpanjang cookie
        $new_cookie = json_encode([
            'id' => $data['id'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'expires' => time() + (30 * 24 * 60 * 60)
        ]);

        $new_cookie_value = base64_encode($new_cookie);
        setcookie('trashit_login', $new_cookie_value, time() + (30 * 24 * 60 * 60), '/', '', false, true);

        return [
            'id' => $data['id'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role']
        ];
    } catch (Exception $e) {
        error_log("Cookie error: " . $e->getMessage());
        setcookie('trashit_login', '', time() - 3600, '/');
        return false;
    }
}
?>