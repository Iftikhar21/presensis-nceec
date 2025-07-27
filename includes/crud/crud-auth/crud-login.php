<?php
require_once(__DIR__ . '/../../../config.php');

define('REMEMBER_ME_SECRET', 'nceec-secure-key');
define('COOKIE_EXPIRY_DAYS', 30);

function Login($data) {
    $conn = connectDatabase();
    $identifier = mysqli_real_escape_string($conn, $data['username']); // bisa username/email
    $password = mysqli_real_escape_string($conn, $data['password']);

    // Periksa berdasarkan username ATAU email
    $query = "SELECT * FROM users WHERE username = '$identifier' OR email = '$identifier'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        return [
            "status" => true,
            "user" => $user
        ];
    }

    return [
        "status" => false,
        "message" => "Username atau Password salah!"
    ];
}


function createRememberMeCookie($username, $user_id) {
    $expiry_time = time() + (COOKIE_EXPIRY_DAYS * 24 * 60 * 60);

    $cookie_data = [
        'username' => $username,
        'user_id' => $user_id,
        'expiry' => $expiry_time,
        'hash' => createCookieHash($username, $user_id, $expiry_time)
    ];

    $encrypted = encryptCookieData($cookie_data);

    setcookie(
        'remember_me',
        $encrypted,
        $expiry_time,
        '/',
        '',
        false,
        true
    );
}

function validateRememberMeCookie($cookie_value) {
    $cookie_data = decryptCookieData($cookie_value);

    if (!$cookie_data || !isset($cookie_data['username'], $cookie_data['user_id'], $cookie_data['expiry'], $cookie_data['hash'])) {
        clearRememberMeCookie();
        return ["status" => false];
    }

    if ($cookie_data['expiry'] < time()) {
        clearRememberMeCookie();
        return ["status" => false];
    }

    $expected_hash = createCookieHash($cookie_data['username'], $cookie_data['user_id'], $cookie_data['expiry']);
    if (!hash_equals($expected_hash, $cookie_data['hash'])) {
        clearRememberMeCookie();
        return ["status" => false];
    }

    $conn = connectDatabase();
    $user_id = mysqli_real_escape_string($conn, $cookie_data['user_id']);
    $username = mysqli_real_escape_string($conn, $cookie_data['username']);

    $query = "SELECT * FROM users WHERE id = '$user_id' AND username = '$username'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        return [
            "status" => true,
            "user" => $user
        ];
    }

    clearRememberMeCookie();
    return ["status" => false];
}

function clearRememberMeCookie() {
    setcookie('remember_me', '', time() - 3600, "/");
}

// Helper
function createCookieHash($username, $user_id, $expiry) {
    return hash_hmac('sha256', $username . $user_id . $expiry, REMEMBER_ME_SECRET);
}

function encryptCookieData($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt(json_encode($data), 'aes-256-cbc', REMEMBER_ME_SECRET, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptCookieData($data) {
    try {
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        $decrypted = openssl_decrypt($encrypted_data, 'aes-256-cbc', REMEMBER_ME_SECRET, 0, $iv);
        return json_decode($decrypted, true);
    } catch (Exception $e) {
        return false;
    }
}
?>
