<?php
// Tambahkan ini dulu
ini_set('session.cookie_lifetime', 0); // session hilang saat browser ditutup

session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-admin/crud-admin.php';


// Pengecekan session utama
if (!isset($_SESSION['username']) && !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];

$data_admin = getAdminWhereId($id_user);
if (!$data_admin['status']) {
    echo "<p>Error: " . $data_admin['message'] . "</p>";
    exit();
}

if (!isset($data_admin['admin']['username'])) {
    echo "<p>Error: Admin data is incomplete.</p>";
    exit();
}

$username = $data_admin['admin']['username'];
$email = $data_admin['admin']['email'];
$role = ucfirst($data_admin['admin']['role']);

$title_page = "NCEEC";
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Pelajaran - <?= $title_page; ?></title>
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
                <a href="../dashboard/dashboard.php" onclick="setActivePage('Dashboard')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-dashboard">
                    <i class="fa-solid fa-house text-lg mr-3"></i>
                    Dashboard
                </a>

                <a href="../list-materi/material-list.php" onclick="setActivePage('List Materi')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-materi">
                    <i class="fa-solid fa-list-check text-lg mr-3"></i>
                    List Materi
                </a>

                <a href="../list-pelajaran/lesson-list.php" onclick="setActivePage('List Pelajaran')" class="menu-item active flex items-center px-4 py-3 text-sm rounded-lg" id="menu-pelajaran">
                    <i class="fa-solid fa-book-open-reader text-lg mr-3"></i>
                    List Pelajaran
                </a>

                <a href="../data-absent-tutor/absent-tutor.php" onclick="setActivePage('Data Absen Tutor')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-absen">
                    <i class="fa-solid fa-calendar text-lg mr-3"></i>
                    Data Absen Tutor
                </a>

                <a href="../data-tutor/data-tutor.php" onclick="setActivePage('Data Tutor')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-tutor">
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
                <h2 id="page-title" class="ml-4 lg:ml-0 text-xl font-semibold">List Pelajaran</h2>
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center text-sm text-muted z-10">
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                            <i class="fa-solid fa-user mr-1 bg-gray-200 p-3 rounded-full hover:bg-gray-300 hover:transition ease-in duration-100 hover:shadow-md"></i>
                            <span class="hidden md:inline-block"><?php echo "$username ($role)"; ?></span>
                            <i class="fa-solid fa-chevron-down" id="user-menu-icon"></i>
                        </button>
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg">
                            <a href="../../auth/profile.php" class="block px-6 py-4 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fa-solid fa-user mr-3 text-blue-700"></i>
                                Profile
                            </a>
                            <hr class="border-gray-200">
                            <a href="#" onclick="logout()" class="block px-6 py-4 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fa-solid fa-right-from-bracket text-lg mr-3 text-red-700"></i>
                                Logouttt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-6 flex-grow">
            <div class="max-w-7xl mx-auto">
                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative animate-fade-in-down">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full bg-blue-100 opacity-50"></div>
                    <div class="flex items-center justify-between relative z-5">
                        <div>
                            <h3 class="text-xl md:text-2xl font-semibold mb-1">Selamat Datang, <?= $username ?>!</h3>
                            <p class="text-xs md:text-sm text-muted">Semoga harimu menyenangkan</p>
                        </div>
                        <div class="text-right">
                            <div id="current-time" class="text-2xl md:text-3xl font-bold text-blue-600"></div>
                            <div id="current-date" class="text-xs md:text-sm text-gray-500"></div>
                        </div>
                    </div>
                </div>
                <script>
                    function updateDateTime() {
                        const now = new Date();
                        const options = {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        };

                        document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });
                        document.getElementById('current-date').textContent = now.toLocaleDateString('id-ID', options);
                    }

                    // Update setiap detik
                    updateDateTime();
                    setInterval(updateDateTime, 1000);
                </script>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                    <div class="content-card rounded-lg p-6 bg-white shadow-md animate-fade-in-up animate-delay-100 hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-blue-200 mr-3">
                                <i class="fa-solid fa-users text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-semibold mb-2">Jumlah Tutor</h3>
                                <h3 class="text-5xl font-semibold mb-2 text-blue-600">36</h3>
                                <p class="text-muted">Terdaftar</p>
                            </div>
                        </div>
                    </div>

                    <div class="content-card rounded-lg p-6 bg-white shadow-md animate-fade-in-up animate-delay-200 hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-200 mr-3">
                                <i class="fa-solid fa-list-check text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-semibold mb-2">List Materi</h3>
                                <h3 class="text-5xl font-semibold mb-2 text-green-600">36</h3>
                                <p class="text-muted">Tersedia</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="content-card rounded-lg p-6 bg-white shadow-md animate-fade-in-up animate-delay-300 hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-yellow-200 mr-3">
                                <i class="fa-solid fa-calendar text-2xl text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-semibold mb-2">Jumlah Kelas</h3>
                                <h3 class="text-5xl font-semibold mb-2 text-yellow-600">36</h3>
                                <p class="text-muted">Tersedia</p>
                            </div>
                        </div>
                    </div>

                    <div class="content-card rounded-lg p-6 bg-white shadow-md animate-fade-in-up animate-delay-400 hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-200 mr-3">
                                <i class="fa-solid fa-book-open-reader text-2xl text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-semibold mb-2">Jumlah Pelajaran</h3>
                                <h3 class="text-5xl font-semibold mb-2 text-purple-600">36</h3>
                                <p class="text-muted">Tersedia</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let currentPage = 'Dashboard';

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
            // Update current page
            currentPage = pageName;

            // Update page title
            document.getElementById('page-title').textContent = pageName;

            // Update content
            const contentTitle = document.getElementById('content-title');
            const contentDescription = document.getElementById('content-description');

            contentTitle.textContent = `Ini halaman ${pageName}`;

            // Set description based on page
            const descriptions = {
                'Dashboard': 'Selamat datang di panel administrasi. Pilih menu di sidebar untuk navigasi ke halaman yang diinginkan.',
                'List Materi': 'Halaman untuk mengelola daftar materi pembelajaran. Anda dapat menambah, mengedit, atau menghapus materi di sini.',
                'Data Absen Tutor': 'Halaman untuk melihat dan mengelola data absensi tutor. Monitor kehadiran dan rekap absensi tutor.',
                'Data Tutor': 'Halaman untuk mengelola data tutor. Kelola informasi profil, jadwal, dan data tutor lainnya.'
            };

            contentDescription.textContent = descriptions[pageName] || `Konten untuk halaman ${pageName}`;

            // Update active menu
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => item.classList.remove('active'));

            const menuMap = {
                'Dashboard': 'menu-dashboard',
                'List Materi': 'menu-materi',
                'Data Absen Tutor': 'menu-absen',
                'Data Tutor': 'menu-tutor'
            };

            if (menuMap[pageName]) {
                document.getElementById(menuMap[pageName]).classList.add('active');
            }

            // Close sidebar on mobile after selection
            if (window.innerWidth < 1024) {
                toggleSidebar();
            }
        }

        function logout() {
            const modal = document.createElement('div');
            modal.id = 'logout-modal';
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-96">
                        <h2 class="text-lg font-semibold mb-4">Konfirmasi Logout</h2>
                        <p class="text-sm mb-6">Apakah Anda yakin ingin logout?</p>
                        <div class="flex justify-end space-x-4">
                            <button id="close-modal" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
                            <button id="confirm-logout" class="px-4 py-2 bg-red-500 text-white rounded">Logout</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('close-modal').addEventListener('click', () => {
                modal.remove();
            });

            document.getElementById('confirm-logout').addEventListener('click', () => {
                window.location.href = '../../auth/logout.php';
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = event.target.closest('button[onclick="toggleSidebar()"]');

            if (!sidebar.contains(event.target) && !hamburger && window.innerWidth < 1024) {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            }
        });

        // Handle window resize
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

        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');
        const userMenuIcon = document.getElementById('user-menu-icon');

        userMenuButton.addEventListener('click', () => {
            // Toggle rotasi antara 0 dan 180 derajat dengan transition
            const currentRotation = userMenu.classList.contains('hidden') ? 180 : 0;
            userMenuIcon.style.transform = `rotate(${currentRotation}deg)`;
            userMenuIcon.style.transition = 'transform 0.3s ease';
            userMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                // Kembalikan rotasi ke 0 derajat saat menutup menu dengan transition
                userMenuIcon.style.transform = 'rotate(0deg)';
                userMenu.classList.add('hidden');
            }
        });
    </script>
    <style>

    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

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