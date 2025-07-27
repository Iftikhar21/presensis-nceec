<?php
session_start();
include '../../includes/crud/crud-auth/crud-reset-password.php';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password dan konfirmasi password tidak cocok!";
        header("Location: form-reset-pass.php?token=" . urlencode($token));
        exit();
    }

    // Validasi kompleksitas password
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password minimal 8 karakter!";
        header("Location: form-reset-pass.php?token=" . urlencode($token));
        exit();
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $_SESSION['error'] = "Password harus mengandung minimal 1 huruf besar!";
        header("Location: form-reset-pass.php?token=" . urlencode($token));
        exit();
    }

    if (!preg_match('/[a-z]/', $password)) {
        $_SESSION['error'] = "Password harus mengandung minimal 1 huruf kecil!";
        header("Location: form-reset-pass.php?token=" . urlencode($token));
        exit();
    }

    if (!preg_match('/[0-9]/', $password)) {
        $_SESSION['error'] = "Password harus mengandung minimal 1 angka!";
        header("Location: form-reset-pass.php?token=" . urlencode($token));
        exit();
    }

    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $_SESSION['error'] = "Password harus mengandung minimal 1 simbol!";
        header("Location: form-reset-pass.php?token=" . urlencode($token));
        exit();
    }

    $result = resetPassword($_POST);

    if ($result["status"]) {
        $_SESSION['success'] = $result["message"];
        echo "<script>
                alert('{$result["message"]}');
                window.location.href = 'form-login.php';
            </script>";
        exit();
    } else {
        $_SESSION['error'] = $result["message"];
        header("Location: form-reset-pass.php?token=" . urlencode($token));
        exit();
    }
}

// Handle GET request (show form)
$token = $_GET['token'] ?? '';
$conn = connectDatabase();

// Check token validity (you'll need to implement this function in your crud-reset-password.php)
$user = verifyResetToken($token);

if ($user === null) {
    $_SESSION['error'] = "Token tidak valid atau telah kedaluwarsa.";
    header("Location: form-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - NCEEC</title>
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
                <h2 class="text-2xl font-bold tracking-widest text-gray-900">RESET PASSWORD</h2>
            </div>

            <!-- Form -->
            <form class="space-y-6" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username
                    </label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?>" disabled class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?>" disabled class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Baru
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500 pr-12" placeholder="Masukkan Password Baru">
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fa-regular fa-eye password-toggle-icon" id="eye-open-password"></i>
                            <i class="fa-regular fa-eye-slash password-toggle-icon active" id="eye-closed-password"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <input type="password" id="confirm-password" name="confirm_password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500 pr-12" placeholder="Masukkan Konfirmasi Password">
                        <button type="button" onclick="togglePassword('confirm-password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fa-regular fa-eye password-toggle-icon" id="eye-open-confirm-password"></i>
                            <i class="fa-regular fa-eye-slash password-toggle-icon active" id="eye-closed-confirm-password"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" class="w-full bg-black text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02]">
                    Reset Password
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center mt-8">
                <p class="text-gray-600">
                    Sudah ingat password?
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
        document.addEventListener('DOMContentLoaded', function () {
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