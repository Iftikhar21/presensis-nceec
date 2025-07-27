<?php
session_start();
include '../../includes/crud/crud-auth/crud-login.php';

// Cek cookie remember me
if (isset($_COOKIE['remember_me']) && !isset($_SESSION['username'])) {
    $check = validateRememberMeCookie($_COOKIE['remember_me']);
    if ($check['status']) {
        $_SESSION['username'] = $check['user']['username'];
        $_SESSION['email'] = $check['user']['email'];
        $_SESSION['ID'] = $check['user']['id'];
        $_SESSION['role'] = $check['user']['role'];

        // Redirect sesuai role
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard/dashboard.php");
        } else if ($_SESSION['role'] === 'teacher') {
            header("Location: ../teacher/dashboard/dashboard.php");
        }
        exit;
    }
}

// Proses login saat form dikirim
if (isset($_POST['submit'])) {
    $login = Login([
        'username' => $_POST['identifier'],
        'password' => $_POST['password']
    ]);

    if ($login['status']) {
        $_SESSION['username'] = $login['user']['username'];
        $_SESSION['email'] = $login['user']['email'];
        $_SESSION['ID'] = $login['user']['id'];
        $_SESSION['role'] = $login['user']['role'];

        if (isset($_POST['remember'])) {
            createRememberMeCookie($login['user']['username'], $login['user']['id']);
        }

        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard/dashboard.php");
        } else if ($_SESSION['role'] === 'teacher') {
            header("Location: ../teacher/dashboard/dashboard.php");
        }
        exit;
    } else {
        echo "<script>alert('{$login['message']}'); window.location='form-login.php';</script>";
        exit;
    }
}

// Jika sudah login, langsung redirect
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard/dashboard.php");
    } else if ($_SESSION['role'] === 'teacher') {
        header("Location: ../teacher/dashboard/dashboard.php");
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NCEEC</title>
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-toggle-icon {
            display: none;
        }

        .password-toggle-icon.active {
            display: inline-block;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Container utama -->
        <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-black rounded-full flex items-center justify-center mx-auto mb-4 p-1">
                    <img src="../../assets/img/nceec-logo.jpg" alt="Logo NCEEC" class="rounded-full">
                </div>
                <h2 class="text-2xl font-bold tracking-widest text-gray-900">LOGIN</h2>
            </div>

            <!-- Form -->
            <form class="space-y-6" method="post" action="">
                <!-- Email/Username -->
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700 mb-2">
                        Email atau Username
                    </label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500"
                        placeholder="Masukkan Email atau Username">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500 pr-12"
                            placeholder="Masukkan password">
                        <button
                            type="button"
                            onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fa-regular fa-eye password-toggle-icon" id="eye-open-password"></i>
                            <i class="fa-regular fa-eye-slash password-toggle-icon active" id="eye-closed-password"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 text-black bg-gray-100 border-gray-300 rounded focus:ring-black focus:ring-2">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                    <a href="forgot-pass.php" class="text-sm text-black hover:text-gray-700 font-medium transition-colors">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit" name="submit"
                    class="w-full bg-black text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02]">
                    Masuk
                </button>
            </form>

            <!-- Sign Up Link -->
            <div class="text-center mt-8">
                <p class="text-gray-600">
                    Belum punya akun?
                    <a href="form-regis.php" class="text-black font-medium hover:text-gray-700 transition-colors">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const eyeOpen = document.getElementById(`eye-open-${fieldId}`);
            const eyeClosed = document.getElementById(`eye-closed-${fieldId}`);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('active');
                eyeClosed.classList.remove('active');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('active');
                eyeClosed.classList.add('active');
            }
        }

        // Animasi saat load
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.bg-white');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';

            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>

</html>