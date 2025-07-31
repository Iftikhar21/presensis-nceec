<?php
// Konfigurasi session yang aman
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

session_start();

// Include file CRUD
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-teacher/crud-teacher.php';
include '../../../includes/crud/crud-material/crud-material.php';
include '../../../includes/crud/crud-lessons/crud-lessons.php';
include '../../../includes/crud/crud-presence/crud-presence.php';

// Pengecekan session dan autoritas
if (!isset($_SESSION['username']) || !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];

// Validasi teacher data - menggunakan nama fungsi yang benar dari CRUD
$data_teacher = getTeacherWhereId($id_user);
if (!$data_teacher['status']) {
    echo "<p>Error: " . htmlspecialchars($data_teacher['message']) . "</p>";
    exit();
}

if (!isset($data_teacher['teacher']['username'])) {
    echo "<p>Error: Teacher data is incomplete.</p>";
    exit();
}

$username = htmlspecialchars($data_teacher['teacher']['username']);
$email = htmlspecialchars($data_teacher['teacher']['email']);
$role = ucfirst(htmlspecialchars($data_teacher['teacher']['role']));

// Get data dengan error handling yang lebih baik
$all_materi = getAllMaterial();
$materi_count = $all_materi['status'] ? count($all_materi['materi']) : 0;

$lessons_data = getAllLessons();
$lessons_count = is_array($lessons_data) ? count($lessons_data) : 0;

// Cek apakah data tutor sudah ada menggunakan fungsi dari CRUD
$existing_tutor = getTutorByUserId($id_user);
$isEdit = ($existing_tutor !== null);

// Tentukan URL action dan inisialisasi nilai
if ($isEdit) {
    $actionURL = '../../../includes/crud/crud-teacher/crud-teacher.php?action=update';
    $id_tutor = $existing_tutor['id_tutor'];
    $nama_tutor = htmlspecialchars($existing_tutor['nama_tutor']);
    $id_pelajaran = $existing_tutor['id_pelajaran'];
    $bergabung = $existing_tutor['bergabung'];
    $foto_profile = $existing_tutor['foto_profile'];
} else {
    $actionURL = '../../../includes/crud/crud-teacher/crud-teacher.php?action=create';
    $id_tutor = '';
    $nama_tutor = '';
    $id_pelajaran = '';
    $bergabung = date('Y-m-d'); // Default tanggal hari ini
    $foto_profile = '';
}

$title_page = "NCEEC";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile - <?= htmlspecialchars($title_page); ?></title>
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
                <a href="../../teacher/dashboard/dashboard.php" onclick="setActivePage('Dashboard')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-dashboard">
                    <i class="fa-solid fa-house text-lg mr-3"></i>
                    Dashboard
                </a>

                <a href="../../teacher/list-materi/material-list.php" onclick="setActivePage('List Materi')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-materi">
                    <i class="fa-solid fa-list-check text-lg mr-3"></i>
                    List Materi
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
                            <a href="../../auth/teacher/profile.php" class="block px-6 py-4 text-sm text-gray-700 hover:bg-gray-100">
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
                <!-- Alert Status -->
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'success'): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <div class="flex items-center">
                                <i class="fa-solid fa-check-circle mr-2"></i>
                                <strong>Sukses!</strong> Data tutor berhasil <?= $isEdit ? 'diupdate' : 'ditambahkan' ?>.
                            </div>
                        </div>
                    <?php elseif ($_GET['status'] == 'error'): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <div class="flex items-center">
                                <i class="fa-solid fa-exclamation-circle mr-2"></i>
                                <strong>Error!</strong> <?= isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Terjadi kesalahan.' ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl md:text-2xl font-semibold">
                            <?= $isEdit ? 'Edit Data Tutor' : 'Tambah Data Tutor' ?>
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
                                Anda sedang mengedit data tutor yang sudah ada. Silakan ubah data yang diperlukan dan klik "Update" untuk menyimpan perubahan.
                            </p>
                        </div>
                    <?php endif; ?>

                    <form action="<?= $actionURL ?>" method="POST" enctype="multipart/form-data" class="space-y-4" id="tutorForm" onsubmit="return validateForm()") onsubmit="return validateForm()">
                        <!-- Hidden fields -->
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($id_user) ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_tutor" value="<?= htmlspecialchars($id_tutor) ?>">
                        <?php endif; ?>

                        <!-- Username -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="username">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="username"
                                name="username" 
                                value="<?= $username ?>" 
                                class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Masukkan username"
                                required
                                minlength="3"
                                maxlength="50">
                            <p class="text-xs text-gray-500 mt-1">Minimal 3 karakter, maksimal 50 karakter</p>
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
                                placeholder="Masukkan email"
                                required
                                maxlength="100">
                            <p class="text-xs text-gray-500 mt-1">Masukkan email yang valid</p>
                        </div>

                        <!-- Nama Tutor -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="nama_tutor">
                                Nama Tutor <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nama_tutor"
                                name="nama_tutor" 
                                value="<?= $nama_tutor ?>" 
                                class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Masukkan nama tutor"
                                required
                                minlength="3"
                                maxlength="100">
                            <p class="text-xs text-gray-500 mt-1">Minimal 3 karakter, maksimal 100 karakter</p>
                        </div>

                        <!-- Dropdown Pelajaran -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="id_pelajaran">
                                Pelajaran <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="id_pelajaran"
                                name="id_pelajaran" 
                                class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                required>
                                <option value="">-- Pilih Pelajaran --</option>
                                <?php if (is_array($lessons_data) && !empty($lessons_data)) : ?>
                                    <?php foreach ($lessons_data as $lesson) : ?>
                                        <option value="<?= htmlspecialchars($lesson['id_pelajaran']) ?>" 
                                                <?= ($lesson['id_pelajaran'] == $id_pelajaran ? 'selected' : '') ?>>
                                            <?= htmlspecialchars($lesson['pelajaran']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <option value="" disabled>Tidak ada data pelajaran tersedia</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Tanggal Bergabung -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="bergabung">
                                Tanggal Bergabung <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="bergabung"
                                name="bergabung" 
                                value="<?= $bergabung ?>" 
                                class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                max="<?= date('Y-m-d') ?>"
                                required>
                            <p class="text-xs text-gray-500 mt-1">Tanggal tidak boleh lebih dari hari ini</p>
                        </div>

                        <!-- Foto Profile -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="foto_profile">
                                Foto Profile
                                <?php if ($isEdit && $foto_profile): ?>
                                    <span class="text-sm text-gray-500 ml-2">
                                        (Saat ini: <a href="<?= htmlspecialchars($foto_profile) ?>" target="_blank" class="text-blue-600 hover:underline">Lihat Foto</a>)
                                    </span>
                                <?php endif; ?>
                            </label>
                            <input 
                                type="file" 
                                id="foto_profile"
                                name="foto_profile" 
                                accept="image/jpeg,image/jpg,image/png" 
                                class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">
                                Format yang didukung: JPG, JPEG, PNG. Maksimal 2MB.
                                <?= $isEdit ? ' Kosongkan jika tidak ingin mengubah foto.' : '' ?>
                            </p>
                            
                            <!-- Preview area -->
                            <div id="imagePreview" class="mt-2 hidden">
                                <img id="previewImg" src="" alt="Preview" class="w-24 h-24 object-cover rounded-lg border">
                            </div>
                        </div>

                        <!-- Current photo preview (edit mode) -->
                        <?php if ($isEdit && $foto_profile): ?>
                            <div class="mt-2">
                                <label class="block text-sm font-medium mb-1">Foto Saat Ini:</label>
                                <img src="<?= htmlspecialchars($foto_profile) ?>" 
                                     alt="Foto Profile" 
                                     class="w-24 h-24 object-cover rounded-lg border">
                            </div>
                        <?php endif; ?>

                        <!-- Submit buttons -->
                        <div class="pt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                            <button 
                                type="submit" 
                                id="submitBtn"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200 flex items-center justify-center">                               
                                <?php if ($isEdit): ?>
                                    <i class="fa-solid fa-save mr-2" id="submitIcon"></i>
                                    <span id="submitText">Update Data</span>
                                <?php else: ?>
                                    <i class="fa-solid fa-plus mr-2" id="submitIcon"></i>
                                    <span id="submitText">Simpan Data</span>
                                <?php endif; ?>
                            </button>
                            
                            <a href="../../teacher/dashboard/dashboard.php" 
                               class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition duration-200 flex items-center justify-center">
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
                            Informasi Data Tutor
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between">
                                <span class="font-medium">ID Tutor:</span>
                                <span class="text-gray-600"><?= htmlspecialchars($id_tutor) ?></span>
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

        // Form validation and submission
        document.getElementById('tutorForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loadingIcon = document.getElementById('loadingIcon');
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');
            
            // Show loading state
            submitBtn.disabled = true;
            loadingIcon.classList.remove('hidden');
            submitIcon.classList.add('hidden');
            
            submitText.textContent = 'Memproses...';
        });

        // File preview functionality
        document.getElementById('foto_profile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Hanya JPG, JPEG, PNG yang diizinkan.');
                    e.target.value = '';
                    preview.classList.add('hidden');
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    e.target.value = '';
                    preview.classList.add('hidden');
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
            }
        });

        // Form validation
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const nama = document.getElementById('nama_tutor').value.trim();
            const pelajaran = document.getElementById('id_pelajaran').value;
            const bergabung = document.getElementById('bergabung').value;
            
            if (username.length < 3) {
                alert('Username minimal 3 karakter');
                return false;
            }
            
            if (!email || !isValidEmail(email)) {
                alert('Silakan masukkan email yang valid');
                return false;
            }
            
            if (nama.length < 3) {
                alert('Nama tutor minimal 3 karakter');
                return false;
            }
            
            if (!pelajaran) {
                alert('Silakan pilih pelajaran');
                return false;
            }
            
            if (!bergabung) {
                alert('Silakan pilih tanggal bergabung');
                return false;
            }
            
            const today = new Date();
            const selectedDate = new Date(bergabung);
            if (selectedDate > today) {
                alert('Tanggal bergabung tidak boleh lebih dari hari ini');
                return false;
            }
            
            return true;
        }

        // Email validation helper function
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Sidebar and navigation functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function setActivePage(pageName) {
            currentPage = pageName;
            document.getElementById('page-title').textContent = pageName;

            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => item.classList.remove('active'));

            const menuMap = {
                'Dashboard': 'menu-dashboard',
                'List Materi': 'menu-materi',
                'Profile': 'menu-profile'
            };

            if (menuMap[pageName]) {
                const menuElement = document.getElementById(menuMap[pageName]);
                if (menuElement) menuElement.classList.add('active');
            }

            if (window.innerWidth < 1024) {
                toggleSidebar();
            }
        }

        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = '../../auth/logout.php';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setActivePage('Profile');
            
            // Handle responsive sidebar
            window.addEventListener('resize', function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');

                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            });

            // User menu toggle
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');
            const userMenuIcon = document.getElementById('user-menu-icon');

            if (userMenuButton && userMenu && userMenuIcon) {
                userMenuButton.addEventListener('click', () => {
                    const isHidden = userMenu.classList.contains('hidden');
                    userMenuIcon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                    userMenuIcon.style.transition = 'transform 0.3s ease';
                    userMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', (event) => {
                    if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                        userMenuIcon.style.transform = 'rotate(0deg)';
                        userMenu.classList.add('hidden');
                    }
                });
            }
        });

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
</body>
</html>