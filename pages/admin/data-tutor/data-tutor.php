<?php
// Tambahkan ini dulu
ini_set('session.cookie_lifetime', 0); // session hilang saat browser ditutup

session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-admin/crud-admin.php';
include '../../../includes/crud/crud-teacher/crud-teacher.php';


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

$teachers = getAllTeachers();
$totalTeachers = getAllCountTeachers();
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tutor - <?= $title_page; ?></title>
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

                <a href="../list-pelajaran/lesson-list.php" onclick="setActivePage('List Pelajaran')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-pelajaran">
                    <i class="fa-solid fa-book-open-reader text-lg mr-3"></i>
                    List Pelajaran
                </a>

                <a href="../data-absent-tutor/absent-tutor.php" onclick="setActivePage('Data Absen Tutor')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-absen">
                    <i class="fa-solid fa-calendar text-lg mr-3"></i>
                    Data Absen Tutor
                </a>

                <a href="../data-tutor/data-tutor.php" onclick="setActivePage('Data Tutor')" class="menu-item active flex items-center px-4 py-3 text-sm rounded-lg" id="menu-tutor">
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
                <h2 id="page-title" class="ml-4 lg:ml-0 text-xl font-semibold">Data Tutor</h2>
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
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-6 flex-grow">
            <div class="bg-white rounded-lg shadow-sm">
                <!-- Header -->
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Data Tutor</h2>
                </div>

                <!-- Filter Section -->
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter Nama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tutor</label>
                            <input
                                type="text"
                                id="filterNama"
                                placeholder="Cari nama tutor..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Filter Mata Pelajaran -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran</label>
                            <select
                                id="filterMapel"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Semua Mata Pelajaran</option>
                                <option value="Matematika">Matematika</option>
                                <option value="Fisika">Fisika</option>
                                <option value="Kimia">Kimia</option>
                                <option value="Biologi">Biologi</option>
                                <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                <option value="Bahasa Inggris">Bahasa Inggris</option>
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                id="filterStatus"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Filter Tanggal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Bergabung</label>
                            <input
                                type="date"
                                id="filterTanggal"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center mt-4">
                        <button
                            onclick="resetFilters()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Reset Filter
                        </button>

                        <button
                            onclick="applyFilters()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Terapkan Filter
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Tutor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Tutor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pelajaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto Profil</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                            <?php foreach ($teachers as $teacher): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($teacher['id_tutor'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($teacher['user_id'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($teacher['nama_tutor'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($teacher['id_pelajaran'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($teacher['bergabung'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if (!empty($teacher['foto_profil'])): ?>
                                            <img src="<?= htmlspecialchars($teacher['foto_profil']) ?>" alt="Foto Profil" class="h-10 w-10 rounded-full">
                                        <?php else: ?>
                                            <span class="text-gray-400">Tidak ada foto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editTutor(<?= $teacher['id_tutor'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button onclick="deleteTutor(<?= $teacher['id_tutor'] ?>)" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </button>
                        <button class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Menampilkan <span class="font-medium">1</span> sampai <span class="font-medium"><?= min(10, $totalTeachers) ?></span> dari <span class="font-medium"><?= $totalTeachers ?></span> hasil
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    ‹
                                </button>
                                <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                                    1
                                </button>
                                <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    2
                                </button>
                                <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    3
                                </button>
                                <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    ›
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
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
    <script src="../../../assets/js/sidebar.js"></script>

    <script>
        // Fungsi untuk filter
        function applyFilters() {
            const nama = document.getElementById('filterNama').value.toLowerCase();
            const mapel = document.getElementById('filterMapel').value;
            const status = document.getElementById('filterStatus').value;
            const tanggal = document.getElementById('filterTanggal').value;

            const rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                const rowNama = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                const rowMapel = row.querySelector('td:nth-child(5)').textContent;
                const rowStatus = row.querySelector('td:nth-child(6)').textContent; // Sesuaikan dengan kolom status jika ada
                const rowTanggal = row.querySelector('td:nth-child(6)').textContent; // Sesuaikan dengan kolom tanggal

                const namaMatch = nama === '' || rowNama.includes(nama);
                const mapelMatch = mapel === '' || rowMapel === mapel;
                const statusMatch = status === '' || rowStatus === status;
                const tanggalMatch = tanggal === '' || rowTanggal === tanggal;

                if (namaMatch && mapelMatch && statusMatch && tanggalMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Fungsi reset filter
        function resetFilters() {
            document.getElementById('filterNama').value = '';
            document.getElementById('filterMapel').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterTanggal').value = '';
            applyFilters();
        }

        // Fungsi edit dan hapus
        function editTutor(id) {
            alert('Edit tutor dengan ID: ' + id);
            // window.location.href = 'edit-tutor.php?id=' + id;
        }

        function deleteTutor(id) {
            if (confirm('Apakah Anda yakin ingin menghapus tutor ini?')) {
                alert('Hapus tutor dengan ID: ' + id);
                // window.location.href = 'delete-tutor.php?id=' + id;
            }
        }
    </script>

</body>

</html>