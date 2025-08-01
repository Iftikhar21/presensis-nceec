<?php
// Konfigurasi session yang aman
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

session_start();

// Include file CRUD
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-admin/crud-admin.php';
include '../../../includes/crud/crud-material/crud-material.php';
include '../../../includes/crud/crud-lessons/crud-lessons.php';
include '../../../includes/crud/crud-presence/crud-presence.php';

$actionURL = "profile.php"; // Current page name

// Pengecekan session dan autoritas
if (!isset($_SESSION['username']) || !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];

// Validasi admin data
$data_admin = getAdminWhereId($id_user);
if (!$data_admin['status']) {
    echo "<p>Error: " . htmlspecialchars($data_admin['message']) . "</p>";
    exit();
}

if (!isset($data_admin['admin']['username'])) {
    echo "<p>Error: Admin data is incomplete.</p>";
    exit();
}

$username = htmlspecialchars($data_admin['admin']['username']);
$email = htmlspecialchars($data_admin['admin']['email']);
$role = ucfirst(htmlspecialchars($data_admin['admin']['role']));

// Get data dengan error handling yang lebih baik
$all_materi = getAllMaterial();
$materi_count = $all_materi['status'] ? count($all_materi['materi']) : 0;

$lessons_data = getAllLessons();
$lessons_count = is_array($lessons_data) ? count($lessons_data) : 0;

// Cek apakah data admin sudah ada menggunakan fungsi dari CRUD
$existing_admin = getAdminWhereId($id_user)['admin'] ?? null;
$isEdit = ($existing_admin !== null);

// Tentukan URL action dan inisialisasi nilai
if ($isEdit) {
    $id_admin = $existing_admin['id'];
    $username = htmlspecialchars($existing_admin['username']);
    $email = htmlspecialchars($existing_admin['email']);
    $update_admin = updateAdmin($username, $email, $id_admin);
} else {
    $id_admin = '';
    $username = '';
    $email = '';
}

// Di bagian penanganan form submit, ganti dengan ini:
if (isset($_POST['submit'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_admin = $_POST['id_admin'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';

        // Validasi input
        if (empty($username) || empty($email)) {
            header("Location: profile.php?status=error&message=Semua+field+harus+diisi");
            exit();
        }

        // Update admin
        $result = updateAdmin($username, $email, $id_admin);

        if ($result['status']) {
            // Update session data jika berhasil
            $_SESSION['username'] = $username;
            header("Location: profile.php?status=success");
            exit();
        } else {
            header("Location: profile.php?status=error&message=" . urlencode($result['message']));
            exit();
        }
    }
}


$title_page = "NCEEC";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - <?= htmlspecialchars($title_page); ?></title>
    <link rel="icon" href="../../../assets/img/nceec-logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../../../assets/css/index.css">
    <link rel="stylesheet" href="../../../assets/css/app.css">
    <link rel="stylesheet" href="../../../assets/css/main.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-primary text-primary min-h-screen flex flex-col">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 w-64 sidebar text-white transform -translate-x-full lg:translate-x-0 sidebar-transition z-50">
        <div class="flex items-center justify-center h-16 sidebar-header mt-4">
            <img src="../../../assets/img/nceec-logo.jpg" alt="Left Logo" class="h-12 w-12 mr-4 rounded-full">
            <h1 class="text-2xl font-bold tracking-wide">NCEEC</h1>
        </div>

        <nav class="mt-8">
            <div class="px-4 space-y-2">
                <a href="../../admin/dashboard/dashboard.php" onclick="setActivePage('Dashboard')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-dashboard">
                    <i class="fa-solid fa-house text-lg mr-3"></i>
                    Dashboard
                </a>

                <a href="../../admin/list-materi/material-list.php" onclick="setActivePage('List Materi')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-materi">
                    <i class="fa-solid fa-list-check text-lg mr-3"></i>
                    List Materi
                </a>

                <a href="../../admin/list-pelajaran/lesson-list.php" onclick="setActivePage('List Pelajaran')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-pelajaran">
                    <i class="fa-solid fa-book-open-reader text-lg mr-3"></i>
                    List Pelajaran
                </a>

                <a href="../../admin/data-absent-tutor/absent-tutor.php" onclick="setActivePage('Data Absen Tutor')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-absen">
                    <i class="fa-solid fa-calendar text-lg mr-3"></i>
                    Data Absen Tutor
                </a>

                <a href="../../admin/data-tutor/data-tutor.php" onclick="setActivePage('Data Tutor')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-tutor">
                    <i class="fa-solid fa-users text-lg mr-3"></i>
                    Data Tutor
                </a>

                <div class="border-t border-gray-600 mt-6 pt-6">
                    <a href="#" onclick="logout()" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg text-red-300 hover:text-red-200" id="menu-logout">
                        <i class="fa-solid fa-right-from-bracket text-lg mr-3"></i>
                        Logout
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Overlay untuk mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="lg:ml-64 flex-grow flex flex-col">
        <!-- Topbar -->
        <header class="topbar h-16 flex items-center justify-between px-6">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md btn-hover btn-focus">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h2 id="page-title" class="ml-4 lg:ml-0 text-xl font-semibold">Profile</h2>
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center text-sm text-muted z-10">
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                            <i class="fa-solid fa-user mr-1 bg-gray-200 p-3 rounded-full hover:bg-gray-300 hover:transition ease-in duration-100 hover:shadow-md"></i>
                            <span class="hidden md:inline-block"><?= "$username ($role)"; ?></span>
                            <i class="fa-solid fa-chevron-down" id="user-menu-icon"></i>
                        </button>
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg">
                            <a href="../../auth/admin/profile.php" class="block px-6 py-4 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fa-solid fa-user mr-3 text-blue-700"></i>
                                Profile
                            </a>
                            <hr class="border-gray-200">
                            <a href="#" onclick="logout()" class="block px-6 py-4 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fa-solid fa-right-from-bracket text-lg mr-3 text-red-700"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-6 flex-grow">
            <div class="max-w-2xl mx-auto">
                <!-- Notifikasi Popup di Pojok Kanan -->
                <?php if (isset($_GET['status'])): ?>
                    <div class="fixed top-4 right-4 z-50">
                        <?php if ($_GET['status'] == 'success'): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-lg transform transition-all duration-300 animate-slide-in">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-check-circle mr-2"></i>
                                    <strong>Sukses!</strong> Data admin berhasil <?= $isEdit ? 'diupdate' : 'ditambahkan' ?>.
                                </div>
                            </div>
                        <?php elseif ($_GET['status'] == 'error'): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 shadow-lg transform transition-all duration-300 animate-slide-in">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-exclamation-circle mr-2"></i>
                                    <strong>Error!</strong> <?= isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Terjadi kesalahan.' ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl md:text-2xl font-semibold">
                            <?= $isEdit ? 'Edit Data Admin' : 'Tambah Data Admin' ?>
                        </h3>
                        <?php if ($isEdit): ?>
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                <i class="fa-solid fa-edit mr-1"></i>
                                Mode Edit
                            </span>
                        <?php else: ?>
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                <i class="fa-solid fa-plus mr-1"></i>
                                Mode Tambah
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                <i class="fa-solid fa-info-circle mr-2"></i>
                                Anda sedang mengedit data admin yang sudah ada. Silakan ubah data yang diperlukan dan klik "Update" untuk menyimpan perubahan.
                            </p>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="space-y-4" id="adminForm">
                        <input type="hidden" name="id_admin" value="<?= htmlspecialchars($id_admin) ?>">

                        <!-- Nama Admin -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="username">
                                Nama Admin <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= $username ?>"
                                class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                                minlength="3"
                                maxlength="100">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="email">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= $email ?>"
                                class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                        </div>

                        <!-- Tombol Submit dan Kembali dalam satu baris -->
                        <div class="flex flex-row space-x-3 pt-4">
                            <button type="submit" name="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200 flex items-center justify-center">
                                <i class="fa-solid fa-save mr-2"></i>
                                Update Data
                            </button>

                            <a href="../../admin/dashboard/dashboard.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition duration-200 flex items-center justify-center">
                                <i class="fa-solid fa-arrow-left mr-2"></i>
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Additional info for edit mode -->
                <?php if ($isEdit): ?>
                    <div class="content-card rounded-lg p-6 bg-white shadow-md">
                        <h4 class="text-lg font-semibold mb-3">
                            <i class="fa-solid fa-info-circle mr-2"></i>
                            Informasi Data Admin
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between">
                                <span class="font-medium">ID Admin:</span>
                                <span class="text-gray-600"><?= htmlspecialchars($id_admin) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">User ID:</span>
                                <span class="text-gray-600"><?= htmlspecialchars($id_user) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Username:</span>
                                <span class="text-gray-600"><?= htmlspecialchars($username) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Email:</span>
                                <span class="text-gray-600"><?= htmlspecialchars($email) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Enhanced JavaScript functionality
        let currentPage = 'Profile';

        // Form validation and 
        document.getElementById('adminForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loadingIcon = document.getElementById('loadingIcon');
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');

            // Show loading state
            submitBtn.disabled = true;
            loadingIcon.classList.remove('hidden');
            submitIcon.classList.add('hidden');
            // submitText.textContent = 'Memproses...';

            // Validate form
            if (!validateForm()) {
                e.preventDefault();
                submitBtn.disabled = false;
                loadingIcon.classList.add('hidden');
                submitIcon.classList.remove('hidden');
                submitText.textContent = $isEdit ? 'Update Data' : 'Simpan Data';
                return;
            }

            // If validation passes, allow the form to submit normally
            // Remove e.preventDefault() if you want normal form submission
        });

        // Form validation
        function validateForm() {
            const nama = document.getElementById('username').value.trim();

            if (nama.length < 3) {
                alert('Nama admin minimal 3 karakter');
                return false;
            }

            return true;
        }

        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = '../../auth/logout.php';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>

    <!-- Footer -->
    <footer class="bg-white shadow-md lg:ml-64 mt-auto bottom-0">
        <div class="max-w-7xl mx-auto py-4 px-6">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; <?= date('Y') ?> PRESENSIS NCEEC. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script src="../../../assets/js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</body>

</html>