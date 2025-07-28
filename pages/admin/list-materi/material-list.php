<?php
ini_set('session.cookie_lifetime', 0);
session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-admin/crud-admin.php';
include '../../../includes/crud/crud-lessons/crud-lessons.php';
include '../../../includes/crud/crud-material/crud-material.php';

// Pengecekan session
if (!isset($_SESSION['username']) && !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];
$data_admin = getAdminWhereId($id_user);

if (!$data_admin['status'] || !isset($data_admin['admin']['username'])) {
    echo "<p>Error: Admin data is incomplete.</p>";
    exit();
}

$username = $data_admin['admin']['username'];
$role = ucfirst($data_admin['admin']['role']);
$title_page = "NCEEC";

// Ambil semua pelajaran
$lessons = getAllLessons();
$totalLessons = count($lessons);

// Ambil materi berdasarkan pelajaran yang dipilih (jika ada)
$selectedLesson = isset($_GET['lesson_id']) ? $_GET['lesson_id'] : null;
$materials = $selectedLesson ? getMaterialsByLesson($selectedLesson) : [];
$totalMaterials = getMaterialCountByLesson($selectedLesson);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Materi - <?= $title_page; ?></title>
    <link rel="icon" href="../../../assets/img/nceec-logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../../../assets/css/index.css">
    <link rel="stylesheet" href="../../../assets/css/app.css">
    <link rel="stylesheet" href="../../../assets/css/main.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .lesson-card {
            transition: all 0.3s ease;
        }

        .lesson-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .active-lesson {
            border-left: 4px solid #3B82F6;
            background-color: #EFF6FF;
        }
    </style>
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

                <a href="../list-materi/material-list.php" onclick="setActivePage('List Materi')" class="menu-item active flex items-center px-4 py-3 text-sm rounded-lg" id="menu-materi">
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
                <h2 id="page-title" class="ml-4 lg:ml-0 text-xl font-semibold">
                    <?= $selectedLesson ? 'Daftar Materi' : 'Daftar Pelajaran' ?>
                </h2>
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
            <?php if (!$selectedLesson): ?>
                <!-- Tampilan Daftar Pelajaran -->
                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative animate-fade-in-down">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full bg-blue-100 opacity-50"></div>
                    <div class="flex items-center justify-between relative z-5">
                        <div>
                            <h3 class="text-xl md:text-2xl font-semibold mb-1">Daftar Pelajaran</h3>
                            <p class="text-xs md:text-sm text-muted">Pilih pelajaran untuk melihat materi</p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-xl md:text-3xl font-bold text-blue-600 mb-1"><?= $totalLessons ?></h3>
                            <p class="text-xs md:text-sm text-muted">Total Pelajaran</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($lessons as $lesson): ?>
                        <a href="?lesson_id=<?= $lesson['id_pelajaran'] ?>" class="lesson-card block p-6 bg-white rounded-lg shadow-md border border-gray-200 hover:border-blue-300">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-book text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($lesson['pelajaran']) ?></h3>
                                    <p class="text-sm text-gray-500">Kode: <?= htmlspecialchars($lesson['id_pelajaran']) ?></p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    <?= $lesson['jumlah_materi'] ?? 0 ?> Materi
                                </span>
                                <!-- <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($lesson['tingkat_kesulitan'] ?? 'Medium') ?>
                                </span> -->
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- Tampilan Daftar Materi untuk Pelajaran yang Dipilih -->
                <?php
                $currentLesson = getLessonsWhereId($selectedLesson);
                if (!$currentLesson) {
                    echo "<div class='p-6 bg-white rounded-lg shadow-md'>Pelajaran tidak ditemukan</div>";
                    exit();
                }
                ?>

                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative animate-fade-in-down">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full bg-blue-100 opacity-50"></div>
                    <div class="flex items-center justify-between relative z-5">
                        <div>
                            <h3 class="text-xl md:text-2xl font-semibold mb-1">Daftar Materi</h3>
                            <p class="text-xs md:text-sm text-muted flex flex-col md:flex-row md:items-center">
                                <span>Pelajaran: <?= htmlspecialchars($currentLesson['pelajaran']) ?></span>
                                <a href="material-list.php" class="text-blue-600 hover:text-blue-800 mt-1 md:mt-0 md:ml-2">
                                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pelajaran
                                </a>
                            </p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-xl md:text-3xl font-bold text-blue-600 mb-1"><?= $totalMaterials ?></h3>
                            <p class="text-xs md:text-sm text-muted">Total Materi</p>
                        </div>
                    </div>
                </div>

                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Materi untuk <?= htmlspecialchars($currentLesson['pelajaran']) ?>
                        </h3>
                        <button onclick="addMaterial(<?= $selectedLesson ?>)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Materi
                        </button>
                    </div>

                    <!-- Tabel Materi -->
                    <div class="overflow-x-auto">
                        <!-- Filter Section -->
                        <div class="p-6 mb-6 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Filter Judul Materi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Judul Materi</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <input
                                            type="text"
                                            id="filterJudul"
                                            placeholder="Cari judul materi..."
                                            class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                                    </div>
                                </div>

                                <!-- Filter Tanggal -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Upload</label>
                                    <input
                                        type="date"
                                        id="filterTanggal"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-end space-x-3">
                                    <button
                                        onclick="resetFilters()"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 flex items-center justify-center">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Reset
                                    </button>
                                    <button
                                        onclick="applyFilters()"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 flex items-center justify-center">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID Materi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Judul Materi</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal Upload</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                                <?php if (count($materials) > 0): ?>
                                    <?php foreach ($materials as $index => $material): ?>
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800"><?= $material['id_materi'] ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-file-alt text-blue-600"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($material['isi_materi']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                <?= htmlspecialchars($material['waktu']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex justify-center space-x-3">
                                                    <button onclick="viewMaterial(<?= $material['id_materi'] ?>)" class="text-blue-600 hover:text-blue-900 transition duration-150" title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button onclick="editMaterial(<?= $material['id_materi'] ?>)" class="text-yellow-600 hover:text-yellow-900 transition duration-150" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="deleteMaterial(<?= $material['id_materi'] ?>)" class="text-red-600 hover:text-red-900 transition duration-150" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <a href="<?= htmlspecialchars($material['id_materi']) ?>" download class="text-green-600 hover:text-green-900 transition duration-150" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada materi untuk pelajaran ini
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button class="prev-page-mobile relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </button>
                                <button class="next-page-mobile ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="pagination-info text-sm text-gray-700">
                                        <!-- Akan diisi oleh JavaScript -->
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <button class="prev-page relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        <div class="page-buttons flex">
                                            <!-- Tombol halaman akan diisi oleh JavaScript -->
                                        </div>
                                        <button class="next-page relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
    <script src="../../../assets/js/sidebar.js"></script>
    <script>
        // Data dan konfigurasi awal
        const allMaterials = <?php echo json_encode($materials); ?>; // Ubah dari $allMaterials ke $materials
        let filteredMaterials = [...allMaterials];
        let currentPage = 1;
        const rowsPerPage = 5;

        // Fungsi untuk memfilter data
        function applyFilters() {
            const judulMateri = document.getElementById('filterJudul').value.toLowerCase();
            const tanggalUpload = document.getElementById('filterTanggal').value;

            filteredMaterials = allMaterials.filter(material => {
                const judulMatch = material.isi_materi?.toLowerCase().includes(judulMateri) || false;
                const tanggalMatch = tanggalUpload ? material.waktu.includes(tanggalUpload) : true;

                return judulMatch && tanggalMatch;
            });

            currentPage = 1;
            renderTable();
            updatePaginationInfo();
        }

        // Fungsi untuk mereset filter
        function resetFilters() {
            document.getElementById('filterJudul').value = '';
            document.getElementById('filterTanggal').value = '';
            filteredMaterials = [...allMaterials];
            currentPage = 1;
            renderTable();
            updatePaginationInfo();
        }

        // Fungsi untuk merender tabel
        function renderTable() {
            const tableBody = document.getElementById('tableBody');
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const paginatedMaterials = filteredMaterials.slice(startIndex, endIndex);

            tableBody.innerHTML = paginatedMaterials.map(material => `
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${material.id_materi || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${material.isi_materi || 'N/A'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        ${material.waktu || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex justify-center space-x-3">
                            <button onclick="viewMaterial(${material.id_materi})" class="text-blue-600 hover:text-blue-900 transition duration-150" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editMaterial(${material.id_materi})" class="text-yellow-600 hover:text-yellow-900 transition duration-150" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteMaterial(${material.id_materi})" class="text-red-600 hover:text-red-900 transition duration-150" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            <a href="${material.id_materi}" download class="text-green-600 hover:text-green-900 transition duration-150" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Jika tidak ada data
            if (paginatedMaterials.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada materi yang ditemukan
                        </td>
                    </tr>
                `;
            }
        }

        // Fungsi untuk update info pagination
        function updatePaginationInfo() {
            const totalPages = Math.ceil(filteredMaterials.length / rowsPerPage);
            const startItem = (currentPage - 1) * rowsPerPage + 1;
            const endItem = Math.min(currentPage * rowsPerPage, filteredMaterials.length);

            document.querySelector('.pagination-info').textContent =
                `Menampilkan ${startItem}-${endItem} dari ${filteredMaterials.length} hasil`;

            // Update tombol navigasi
            const prevPage = document.querySelector('.prev-page');
            const prevPageMobile = document.querySelector('.prev-page-mobile');
            const nextPage = document.querySelector('.next-page');
            const nextPageMobile = document.querySelector('.next-page-mobile');

            if (prevPage) prevPage.disabled = currentPage === 1;
            if (prevPageMobile) prevPageMobile.disabled = currentPage === 1;
            if (nextPage) nextPage.disabled = currentPage === totalPages;
            if (nextPageMobile) nextPageMobile.disabled = currentPage === totalPages;

            // Update tombol halaman
            const pageButtonsContainer = document.querySelector('.page-buttons');
            if (pageButtonsContainer) {
                pageButtonsContainer.innerHTML = '';

                for (let i = 1; i <= totalPages; i++) {
                    const button = document.createElement('button');
                    button.className = `relative inline-flex items-center px-4 py-2 border ${
                        currentPage === i 
                            ? 'bg-blue-50 border-blue-500 text-blue-600' 
                            : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'
                    } text-sm font-medium`;
                    button.textContent = i;
                    button.addEventListener('click', () => changePage(i));
                    pageButtonsContainer.appendChild(button);
                }
            }
        }

        // Fungsi untuk navigasi halaman
        function changePage(newPage) {
            currentPage = newPage;
            renderTable();
            updatePaginationInfo();
        }

        // Fungsi untuk aksi materi
        function addMaterial(lessonId) {
            console.log('Tambah materi baru untuk pelajaran ID:', lessonId);
            // window.location.href = `add-material.php?lesson_id=${lessonId}`;
        }

        function viewMaterial(id) {
            console.log('Lihat materi dengan ID:', id);
            // window.location.href = `view-material.php?id=${id}`;
        }

        function editMaterial(id) {
            console.log('Edit materi dengan ID:', id);
            // window.location.href = `edit-material.php?id=${id}`;
        }

        function deleteMaterial(id) {
            if (confirm('Apakah Anda yakin ingin menghapus materi ini?')) {
                console.log('Hapus materi dengan ID:', id);
                // Lakukan AJAX request atau form submission untuk menghapus data
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            renderTable();
            updatePaginationInfo();

            // Filter buttons
            const applyFilterBtn = document.querySelector('button[onclick="applyFilters()"]');
            const resetFilterBtn = document.querySelector('button[onclick="resetFilters()"]');

            if (applyFilterBtn) applyFilterBtn.addEventListener('click', applyFilters);
            if (resetFilterBtn) resetFilterBtn.addEventListener('click', resetFilters);

            // Pagination controls
            const prevPage = document.querySelector('.prev-page');
            const nextPage = document.querySelector('.next-page');
            const prevPageMobile = document.querySelector('.prev-page-mobile');
            const nextPageMobile = document.querySelector('.next-page-mobile');

            if (prevPage) prevPage.addEventListener('click', () => {
                if (currentPage > 1) changePage(currentPage - 1);
            });

            if (nextPage) nextPage.addEventListener('click', () => {
                if (currentPage < Math.ceil(filteredMaterials.length / rowsPerPage)) changePage(currentPage + 1);
            });

            if (prevPageMobile) prevPageMobile.addEventListener('click', () => {
                if (currentPage > 1) changePage(currentPage - 1);
            });

            if (nextPageMobile) nextPageMobile.addEventListener('click', () => {
                if (currentPage < Math.ceil(filteredMaterials.length / rowsPerPage)) changePage(currentPage + 1);
            });
        });
    </script>
</body>

</html>