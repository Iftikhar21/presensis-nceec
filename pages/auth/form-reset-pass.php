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

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .password-requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .password-requirement i {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }

        .requirement-met {
            color: #10b981;
        }

        .requirement-not-met {
            color: #ef4444;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal.modal-visible {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .modal.modal-visible .modal-content {
            transform: translateY(0);
        }

        .modal-content {
            background-color: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-header {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            background-color: #3B82F6;
            border-bottom: 1px solid #FECACA;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }

        .icon-wrapper {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            color: #fff;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-body p {
            margin: 0 0 16px 0;
            line-height: 1.5;
        }

        .help-tip {
            background-color: #f5bbbbff;
            border-left: 4px solid #B91C1C;
            padding: 12px;
            border-radius: 4px;
        }

        .help-tip p {
            color: #B91C1C;
            font-size: 14px;
            margin: 0;
        }

        .modal-footer {
            padding: 12px 20px;
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid #EEE;
        }

        .modal-close {
            background-color: #DC2626;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .modal-close:hover {
            background-color: #B91C1C;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-error {
            border-color: #ef4444 !important;
        }

        #passwordMatchMessage {
            display: none;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        #passwordMatchMessage.show {
            display: block;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
                <h3>Reset Password Gagal</h3>
            </div>

            <div class="modal-body">
                <div class="help-tip">
                    <p id="modalMessage" class="text-black"><?php echo $_SESSION['error'] ?? ''; ?></p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="modal-close" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

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
            <form class="space-y-6" method="post" onsubmit="return validateForm()">
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
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500 pr-12" 
                            placeholder="Masukkan Password Baru"
                            oninput="checkPasswordRequirements()">
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fa-regular fa-eye password-toggle-icon" id="eye-open-password"></i>
                            <i class="fa-regular fa-eye-slash password-toggle-icon active" id="eye-closed-password"></i>
                        </button>
                    </div>
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="password-requirement">
                            <i id="lengthIcon" class="fas fa-times requirement-not-met"></i>
                            <span>Minimal 8 karakter</span>
                        </div>
                        <div class="password-requirement">
                            <i id="uppercaseIcon" class="fas fa-times requirement-not-met"></i>
                            <span>Minimal 1 huruf besar</span>
                        </div>
                        <div class="password-requirement">
                            <i id="lowercaseIcon" class="fas fa-times requirement-not-met"></i>
                            <span>Minimal 1 huruf kecil</span>
                        </div>
                        <div class="password-requirement">
                            <i id="numberIcon" class="fas fa-times requirement-not-met"></i>
                            <span>Minimal 1 angka</span>
                        </div>
                        <div class="password-requirement">
                            <i id="specialIcon" class="fas fa-times requirement-not-met"></i>
                            <span>Minimal 1 simbol</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm-password" 
                            name="confirm_password" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-500 pr-12" 
                            placeholder="Masukkan Konfirmasi Password"
                            oninput="checkPasswordMatch()">
                        <button type="button" onclick="togglePassword('confirm-password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fa-regular fa-eye password-toggle-icon" id="eye-open-confirm-password"></i>
                            <i class="fa-regular fa-eye-slash password-toggle-icon active" id="eye-closed-confirm-password"></i>
                        </button>
                    </div>
                    <div id="passwordMatchMessage" class="text-sm mt-2">
                        <i class="fas fa-times text-red-500 mr-1"></i>
                        <span class="text-red-500">Password tidak cocok</span>
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
        // Show modal if there's an error
        <?php if (isset($_SESSION['error'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const errorModal = document.getElementById('errorModal');

                // Show modal with animation
                errorModal.style.display = 'flex';
                setTimeout(() => {
                    errorModal.classList.add('modal-visible');
                }, 10);

                // Clean up session variables
                <?php unset($_SESSION['error']); ?>

                // Close when pressing Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeModal();
                    }
                });
            });
        <?php endif; ?>

        function closeModal() {
            const errorModal = document.getElementById('errorModal');

            // Add fade-out animation
            errorModal.classList.remove('modal-visible');

            // Hide after animation completes
            setTimeout(() => {
                errorModal.style.display = 'none';
            }, 300); // Match this with CSS transition duration
        }

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

        function checkPasswordRequirements() {
            const password = document.getElementById('password').value;

            // Check length
            const lengthValid = password.length >= 8;
            document.getElementById('lengthIcon').className = lengthValid ?
                'fas fa-check requirement-met' : 'fas fa-times requirement-not-met';

            // Check uppercase
            const uppercaseValid = /[A-Z]/.test(password);
            document.getElementById('uppercaseIcon').className = uppercaseValid ?
                'fas fa-check requirement-met' : 'fas fa-times requirement-not-met';

            // Check lowercase
            const lowercaseValid = /[a-z]/.test(password);
            document.getElementById('lowercaseIcon').className = lowercaseValid ?
                'fas fa-check requirement-met' : 'fas fa-times requirement-not-met';

            // Check number
            const numberValid = /[0-9]/.test(password);
            document.getElementById('numberIcon').className = numberValid ?
                'fas fa-check requirement-met' : 'fas fa-times requirement-not-met';

            // Check special character
            const specialValid = /[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password);
            document.getElementById('specialIcon').className = specialValid ?
                'fas fa-check requirement-met' : 'fas fa-times requirement-not-met';
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const messageElement = document.getElementById('passwordMatchMessage');

            if (confirmPassword.length > 0) {
                if (password !== confirmPassword) {
                    messageElement.classList.add('show');
                } else {
                    messageElement.classList.remove('show');
                }
            } else {
                messageElement.classList.remove('show');
            }
        }

        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            // Check password requirements
            const lengthValid = password.length >= 8;
            const uppercaseValid = /[A-Z]/.test(password);
            const lowercaseValid = /[a-z]/.test(password);
            const numberValid = /[0-9]/.test(password);
            const specialValid = /[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password);

            if (!lengthValid || !uppercaseValid || !lowercaseValid || !numberValid || !specialValid) {
                document.getElementById('modalMessage').textContent = 'Password tidak memenuhi semua persyaratan.';
                const errorModal = document.getElementById('errorModal');
                errorModal.style.display = 'flex';
                setTimeout(() => {
                    errorModal.classList.add('modal-visible');
                }, 10);
                return false;
            }

            if (password !== confirmPassword) {
                document.getElementById('modalMessage').textContent = 'Password dan Konfirmasi Password tidak cocok!';
                const errorModal = document.getElementById('errorModal');
                errorModal.style.display = 'flex';
                setTimeout(() => {
                    errorModal.classList.add('modal-visible');
                }, 10);
                return false;
            }

            return true;
        }

        // Initialize password match message
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('passwordMatchMessage').classList.remove('show');
            
            // Animasi saat load
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