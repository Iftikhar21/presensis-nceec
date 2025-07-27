<?php
session_start();
include '../../includes/crud/crud-auth/crud-login.php';

$user_data = checkLoginCookie();
if ($user_data) {
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['ID'] = $user_data['id'];
    $_SESSION['role'] = $user_data['role'];

    if ($user_data['role'] === 'admin') {
        header("Location: ../admin/dashboard/dashboard.php");
    } else if ($user_data['role'] === 'teacher') {
        header("Location: ../teacher/dashboard/dashboard.php");
    }
    exit;
}

if (isset($_POST['submit'])) {
    $login_data = [
        'identifier' => $_POST['identifier'], // Menggunakan identifier bukan email
        'password' => $_POST['password'],
        'remember' => isset($_POST['remember'])
    ];
    
    $result = Login($login_data);

    if ($result["status"]) {
        $_SESSION['username'] = $result["user"]['username'];
        $_SESSION['email'] = $result["user"]['email'];
        $_SESSION['ID'] = $result["user"]['id'];
        $_SESSION['role'] = $result["user"]['role'];

        if ($result["user"]['role'] === 'admin') {
            header("Location: ../admin/dashboard/dashboard.php");
        } else if ($result["user"]['role'] === 'teacher') {
            header("Location: ../teacher/dashboard/dashboard.php");
        }
        exit;
    } else {
        $alert_message = $result["message"];
        if (isset($result["timeout"])) {
            $alert_message = $result["message"];
        }
        echo "<script>alert('{$alert_message}'); window.location='form-login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monokrom</title>
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/app.css">
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
                <!-- Ubah label dan placeholder -->
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
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            class="w-4 h-4 text-black bg-gray-100 border-gray-300 rounded focus:ring-black focus:ring-2">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-black hover:text-gray-700 font-medium transition-colors">
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
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