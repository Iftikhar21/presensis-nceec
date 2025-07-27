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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #495057;
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-input-wrapper input {
            padding-right: 45px;
        }

        .password-requirements {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }

        .requirement i {
            margin-right: 5px;
            font-size: 0.7rem;
        }

        .valid {
            color: #28a745;
        }

        .invalid {
            color: #dc3545;
        }

        .account-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .reset-password-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .form-section {
            padding: 40px;
        }

        .image-section {
            background-image: url('../../assets/img/nceec-logo.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .form-title {
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }

        .btn-nceec {
            background-color: #000;
            color: white;
            border: none;
            padding: 10px 0;
            font-weight: 600;
        }

        .btn-nceec:hover {
            background-color: #333;
            color: white;
        }
    </style>
</head>

<body class="bg-light">
    <div class="reset-password-container d-flex">
        <div class="form-section col-md-7">
            <h2 class="form-title">Reset Password</h2>
            <p class="text-muted">Silahkan Masukkan Password yang Baru!</p>

            <!-- Informasi Akun -->
            <div class="mb-2">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?>" disabled />
            </div>

            <div class="mb-3">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?>" disabled />
            </div>

            <form method="POST" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <!-- Password -->
                <div class="mb-3">
                    <label for="password">Password Baru</label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="********" required />
                        <button type="button" class="password-toggle" data-target="password">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="password-requirements">
                        <div class="row">
                            <div class="col">
                                <div class="requirement" id="length-req">
                                    <i class="fas fa-circle"></i> Minimal 8 karakter
                                </div>
                                <div class="requirement" id="uppercase-req">
                                    <i class="fas fa-circle"></i> Minimal 1 huruf besar
                                </div>
                                <div class="requirement" id="lowercase-req">
                                    <i class="fas fa-circle"></i> Minimal 1 huruf kecil
                                </div>
                            </div>
                            <div class="col">
                                <div class="requirement" id="number-req">
                                    <i class="fas fa-circle"></i> Minimal 1 angka
                                </div>
                                <div class="requirement" id="special-req">
                                    <i class="fas fa-circle"></i> Minimal 1 simbol
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="********" required />
                        <button type="button" class="password-toggle" data-target="confirm_password">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    <div id="confirm-message" class="text-danger" style="font-size: 0.8rem;"></div>
                </div>

                <button type="submit" name="submit" class="btn btn-nceec w-100">Reset Password</button>
            </form>
        </div>
        <div class="image-section col-md-5 d-none d-md-block"></div>
    </div>

    <!-- Modal Error -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" role="alert">
                <div class="modal-header bg-danger text-light">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Reset Password Gagal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="error-icon-large">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="error-message">
                        <strong><?= $_SESSION['error'] ?? '' ?></strong><br>
                        Silakan coba lagi.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-redo me-2"></i>Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });

        // Validasi password real-time
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const confirmMessage = document.getElementById('confirm-message');

        passwordInput.addEventListener('input', function() {
            const password = this.value;

            // Validasi panjang
            if (password.length >= 8) {
                document.getElementById('length-req').classList.add('valid');
                document.getElementById('length-req').classList.remove('invalid');
                document.getElementById('length-req').querySelector('i').className = 'fas fa-check-circle';
            } else {
                document.getElementById('length-req').classList.add('invalid');
                document.getElementById('length-req').classList.remove('valid');
                document.getElementById('length-req').querySelector('i').className = 'fas fa-times-circle';
            }

            // Validasi huruf besar
            if (/[A-Z]/.test(password)) {
                document.getElementById('uppercase-req').classList.add('valid');
                document.getElementById('uppercase-req').classList.remove('invalid');
                document.getElementById('uppercase-req').querySelector('i').className = 'fas fa-check-circle';
            } else {
                document.getElementById('uppercase-req').classList.add('invalid');
                document.getElementById('uppercase-req').classList.remove('valid');
                document.getElementById('uppercase-req').querySelector('i').className = 'fas fa-times-circle';
            }

            // Validasi huruf kecil
            if (/[a-z]/.test(password)) {
                document.getElementById('lowercase-req').classList.add('valid');
                document.getElementById('lowercase-req').classList.remove('invalid');
                document.getElementById('lowercase-req').querySelector('i').className = 'fas fa-check-circle';
            } else {
                document.getElementById('lowercase-req').classList.add('invalid');
                document.getElementById('lowercase-req').classList.remove('valid');
                document.getElementById('lowercase-req').querySelector('i').className = 'fas fa-times-circle';
            }

            // Validasi angka
            if (/[0-9]/.test(password)) {
                document.getElementById('number-req').classList.add('valid');
                document.getElementById('number-req').classList.remove('invalid');
                document.getElementById('number-req').querySelector('i').className = 'fas fa-check-circle';
            } else {
                document.getElementById('number-req').classList.add('invalid');
                document.getElementById('number-req').classList.remove('valid');
                document.getElementById('number-req').querySelector('i').className = 'fas fa-times-circle';
            }

            // Validasi simbol
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                document.getElementById('special-req').classList.add('valid');
                document.getElementById('special-req').classList.remove('invalid');
                document.getElementById('special-req').querySelector('i').className = 'fas fa-check-circle';
            } else {
                document.getElementById('special-req').classList.add('invalid');
                document.getElementById('special-req').classList.remove('valid');
                document.getElementById('special-req').querySelector('i').className = 'fas fa-times-circle';
            }
        });

        // Validasi konfirmasi password
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                confirmMessage.textContent = "Password tidak cocok!";
            } else {
                confirmMessage.textContent = "";
            }
        });
    </script>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<script>
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                </script>";
        unset($_SESSION['error']);
    }
    ?>
</body>

</html>