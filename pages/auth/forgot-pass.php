<?php
session_start();
include '../../includes/crud/crud-auth/crud-login.php';

// You can add PHP code here to handle the password reset request if needed
if (isset($_POST['submit'])) {
    // Here you would typically:
    // 1. Check if email exists in database
    // 2. Generate a reset token
    // 3. Send email with reset link
    // 4. Show success message
    
    // For now, we'll just show a success message regardless
    $alert_message = "Jika email terdaftar, kami telah mengirimkan link reset password ke email Anda.";
    echo "<script>alert('{$alert_message}'); window.location='form-login.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - NCEEC</title>
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
                <h2 class="text-2xl font-bold tracking-widest text-gray-900">LUPA PASSWORD</h2>
                <p class="text-gray-600 mt-2 text-sm">Masukkan email terdaftar untuk reset password</p>
            </div>

            <!-- Form -->
            <form class="space-y-6" method="post" action="send-pass-reset.php">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Terdaftar
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500"
                        placeholder="Masukkan email yang terdaftar">
                </div>

                <!-- Submit Button -->
                <button
                    type="submit" name="submit"
                    class="w-full bg-black text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02]">
                    Kirim Link Reset
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center mt-8">
                <p class="text-gray-600">
                    Ingat password?
                    <a href="form-login.php" class="text-black font-medium hover:text-gray-700 transition-colors">
                        Kembali ke login
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Animasi saat load -->
    <script>
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