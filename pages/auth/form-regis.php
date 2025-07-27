<?php
    session_start();
    include '../../includes/crud/crud-auth/crud-register.php';

    if (isset($_POST['submit'])) {
        $result = Register($_POST);

        if ($result["status"]) {
            echo "<script>alert('{$result["message"]}'); window.location='form-login.php';</script>";
        } else {
            echo "<script>alert('{$result["message"]}');</script>";
        }
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
                <h2 class="text-2xl font-bold tracking-widest text-gray-900">REGISTER</h2>
            </div>

            <!-- Form -->
            <form class="space-y-6" method="post">
                <!-- Email/Username -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500"
                        placeholder="Masukkan Email Anda">
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500"
                        placeholder="Masukkan Username Anda">
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
                            placeholder="Masukkan Password Anda">
                        <button
                            type="button"
                            onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fa-regular fa-eye password-toggle-icon" id="eye-open-password"></i>
                            <i class="fa-regular fa-eye-slash password-toggle-icon active" id="eye-closed-password"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="confirm-password"
                            name="confirm-password"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500 pr-12"
                            placeholder="Masukkan Konfirmasi Password Anda">
                        <button
                            type="button"
                            onclick="togglePassword('confirm-password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fa-regular fa-eye password-toggle-icon" id="eye-open-confirm-password"></i>
                            <i class="fa-regular fa-eye-slash password-toggle-icon active" id="eye-closed-confirm-password"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit" name="submit"
                    class="w-full bg-black text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02]">
                    Simpan
                </button>
            </form>

            <!-- Sign Up Link -->
            <div class="text-center mt-8">
                <p class="text-gray-600">
                    Sudah punya akun?
                    <a href="form-login.php" class="text-black font-medium hover:text-gray-700 transition-colors">
                        Login sekarang
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