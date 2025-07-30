<?php
// Tambahkan ini dulu
ini_set('session.cookie_lifetime', 0); // session hilang saat browser ditutup

session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-admin/crud-admin.php';
include '../../../includes/crud/crud-lessons/crud-lessons.php';


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

$lessons = getAllLessons();
$countLessons = getAllCountLessons();

// add Lessons
if (isset($_POST['add-lessons'])) {
    $nama_pelajaran = $_POST['pelajaran'];
    $add_lesson = addLessons($nama_pelajaran);

    if ($add_lesson) {
        $_SESSION['success_message'] = "Pelajaran berhasil ditambahkan!";
        header("Location: lesson-list.php");
        exit();
    }
}

if (isset($_POST['edit-lessons'])) {
    $nama_pelajaran = $_POST['pelajaran'];
    $id_pelajaran = $_POST['id_pelajaran'];
    $edit_lesson = editLessons($nama_pelajaran, $id_pelajaran);

    if ($edit_lesson) {
        $_SESSION['success_message'] = "Pelajaran berhasil diperbarui!";
        header("Location: lesson-list.php");
        exit();
    } else {
        echo "<script>alert('Gagal mengupdate pelajaran');</script>";
    }
}

// delete Lessons
if (isset($_POST['delete-lessons'])) {
    $id_pelajaran = $_POST['id_pelajaran'];
    $delete_lesson = deleteLessons($id_pelajaran);

    if ($delete_lesson) {
        $_SESSION['success_message'] = "Pelajaran berhasil dihapus!";
        header("Location: lesson-list.php");
        exit();
    } else {
        echo "<script>alert('Gagal menghapus pelajaran');</script>";
    }
}
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
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-6 flex-grow">
            <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative animate-fade-in-down">
                <div class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full bg-blue-100 opacity-50"></div>
                <div class="flex items-center justify-between relative z-5">
                    <div>
                        <h3 class="text-xl md:text-2xl font-semibold mb-1">Lihat List Pelajaran</h3>
                        <p class="text-xs md:text-sm text-muted">Lihat List Pelajaran yang Tersedia</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-xl md:text-3xl font-bold text-blue-600 mb-1"><?= $countLessons; ?></h3>
                        <p class="text-xs md:text-sm text-muted">List Pelajaran yang Tersedia</p>
                    </div>
                </div>
            </div>
            <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative animate-fade-in-down">
                <!-- Header -->
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">List Pelajaran</h2>
                </div>

                <!-- Filter Section -->
                <div class="p-6 mb-6 border border-gray-200 rounded-lg bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Filter Nama (diperpendek) -->
                        <div class="w-full"> <!-- Menambahkan fixed width -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pelajaran</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="filterNama"
                                    placeholder="Cari nama pelajaran..."
                                    class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-end space-x-3">
                            <button
                                onclick="resetFilters()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 flex items-center justify-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset
                            </button>
                            <button
                                onclick="applyFilters()"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 flex items-center justify-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Terapkan
                            </button>
                            <button
                                onclick="addLessons()"
                                class="w-48 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 flex items-center justify-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Pelajaran
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-10">
                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">ID Pelajaran</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Pelajaran</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Dibuat Pada</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Diperbarui Pada</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="bg-white divide-y divide-gray-200">

                            </tbody>
                        </table>
                    </div>

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

    <div id="modalOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40 transition-opacity duration-300"></div>

    <!-- modal area -->
    <div id="addLessonModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Pelajaran Baru</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition duration-150">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="addLessonForm" method="POST" class="px-6 py-4">
                <div class="space-y-4">
                    <div>
                        <label for="pelajaran" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Pelajaran
                        </label>
                        <input type="text" id="pelajaran" name="pelajaran" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                            placeholder="Masukkan nama pelajaran">
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md hidden">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span id="errorText">Terjadi kesalahan</span>
                    </div>
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                    Batal
                </button>
                <button type="submit" name="add-lessons" form="addLessonForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                    Tambah Pelajaran
                </button>
            </div>
        </div>
    </div>

    <div id="editLessonModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Pelajaran</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition duration-150">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="editLessonForm" method="POST" class="px-6 py-4">
                <input type="hidden" id="edit_id_pelajaran" name="id_pelajaran">
                <div class="space-y-4">
                    <div>
                        <label for="edit_pelajaran" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Pelajaran
                        </label>
                        <input type="text" id="edit_pelajaran" name="pelajaran" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                            placeholder="Masukkan nama pelajaran">
                    </div>
                </div>

                <!-- Error Message -->
                <div id="editErrorMessage" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md hidden">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span id="editErrorText">Terjadi kesalahan</span>
                    </div>
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                    Batal
                </button>
                <button type="submit" name="edit-lessons" form="editLessonForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <div id="deleteLessonModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm transform transition-all duration-300 scale-95 opacity-0">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Hapus Pelajaran</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition duration-150">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="deleteLessonForm" method="POST" action="lesson-list.php">
                <input type="hidden" id="delete_id_pelajaran" name="id_pelajaran">
                <div class="px-6 py-4">
                    <p class="text-gray-700">Anda yakin ingin menghapus pelajaran <span id="deleteLessonName" class="font-semibold"></span>?</p>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                        Batal
                    </button>
                    <button type="submit" name="delete-lessons"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-150">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Notification -->
    <div id="successNotification" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center animate-fade-in-up">
            <i class="fas fa-check-circle text-xl mr-3"></i>
            <span id="successMessage"></span>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <script src="../../../assets/js/sidebar.js"></script>

    <script>
        // Data dan konfigurasi awal
        const allLessons = <?php echo json_encode($lessons); ?>;
        let filteredLessons = [...allLessons];
        let currentPage = 1;
        const rowsPerPage = 10;

        // Fungsi untuk memfilter data
        function applyFilters() {
            const namaPelajaran = document.getElementById('filterNama').value.toLowerCase();

            filteredLessons = allLessons.filter(lesson => {
                const namaMatch = lesson.pelajaran?.toLowerCase().includes(namaPelajaran) || false;
                return namaMatch;
            });

            currentPage = 1;
            renderTable();
            updatePaginationInfo();
        }

        // Fungsi untuk mereset filter
        function resetFilters() {
            document.getElementById('filterNama').value = '';
            filteredLessons = [...allLessons];
            currentPage = 1;
            renderTable();
            updatePaginationInfo();
        }


        // Fungsi untuk merender tabel
        function renderTable() {
            const tableBody = document.getElementById('tableBody');
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const paginatedlessons = filteredLessons.slice(startIndex, endIndex);

            const formatDate = (dateStr) => {
                if (!dateStr) return 'N/A';
                const date = new Date(dateStr);
                return date.toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            };

            tableBody.innerHTML = paginatedlessons.map(lesson => `
            <tr class="hover:bg-gray-50 transition duration-150 text-center">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-center">
                    ${lesson.id_pelajaran || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 text-center">
                        ${lesson.pelajaran || 'N/A'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-center">
                    ${formatDate(lesson.created_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-center">
                    ${formatDate(lesson.updated_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex justify-center space-x-3">
                        <button onclick="editLesson(${lesson.id_pelajaran})" class="text-blue-600 hover:text-blue-900 transition duration-150" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteLesson(${lesson.id_pelajaran})" class="text-red-600 hover:text-red-900 transition duration-150" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        }

        // Fungsi untuk update info pagination
        function updatePaginationInfo() {
            const totalPages = Math.ceil(filteredLessons.length / rowsPerPage);
            const startItem = (currentPage - 1) * rowsPerPage + 1;
            const endItem = Math.min(currentPage * rowsPerPage, filteredLessons.length);

            document.querySelector('.pagination-info').textContent =
                `Menampilkan ${startItem}-${endItem} dari ${filteredLessons.length} hasil`;

            // Update tombol navigasi
            document.querySelector('.prev-page').disabled = currentPage === 1;
            document.querySelector('.prev-page-mobile').disabled = currentPage === 1;
            document.querySelector('.next-page').disabled = currentPage === totalPages;
            document.querySelector('.next-page-mobile').disabled = currentPage === totalPages;

            // Update tombol halaman
            const pageButtonsContainer = document.querySelector('.page-buttons');
            pageButtonsContainer.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const button = document.createElement('button');
                button.className = `relative inline-flex items-center px-4 py-2 border ${currentPage === i ? 'bg-blue-50 border-blue-500 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'} text-sm font-medium`;
                button.textContent = i;
                button.addEventListener('click', () => changePage(i));
                pageButtonsContainer.appendChild(button);
            }
        }

        // Fungsi untuk navigasi halaman
        function changePage(newPage) {
            currentPage = newPage;
            renderTable();
            updatePaginationInfo();
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            renderTable();
            updatePaginationInfo();

            // Filter buttons
            document.querySelector('button[onclick="applyFilters()"]').addEventListener('click', applyFilters);
            document.querySelector('button[onclick="resetFilters()"]').addEventListener('click', resetFilters);

            // Pagination controls
            document.querySelector('.prev-page').addEventListener('click', () => {
                if (currentPage > 1) changePage(currentPage - 1);
            });

            document.querySelector('.next-page').addEventListener('click', () => {
                if (currentPage < Math.ceil(filteredLessons.length / rowsPerPage)) changePage(currentPage + 1);
            });

            document.querySelector('.prev-page-mobile').addEventListener('click', () => {
                if (currentPage > 1) changePage(currentPage - 1);
            });

            document.querySelector('.next-page-mobile').addEventListener('click', () => {
                if (currentPage < Math.ceil(filteredLessons.length / rowsPerPage)) changePage(currentPage + 1);
            });

            // Select all checkbox
            document.getElementById('selectAll').addEventListener('change', function() {
                document.querySelectorAll('#tableBody input[type="checkbox"]')
                    .forEach(checkbox => checkbox.checked = this.checked);
            });

            <?php if (isset($_SESSION['success_message'])): ?>
                showSuccessNotification("<?php echo $_SESSION['success_message']; ?>");
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        });

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            const overlay = document.createElement('div');
            overlay.id = 'modalOverlay';
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-40';
            document.body.appendChild(overlay);

            modal.classList.remove('hidden');
            const content = modal.querySelector('.bg-white');

            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);

            overlay.addEventListener('click', closeModal);
        }

        function closeModal() {
            const modals = document.querySelectorAll('[id$="Modal"]');
            const overlay = document.getElementById('modalOverlay');

            modals.forEach(modal => {
                const content = modal.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-100', 'opacity-100');
                    content.classList.add('scale-95', 'opacity-0');
                }

                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            });

            if (overlay) {
                setTimeout(() => {
                    overlay.remove();
                }, 300);
            }

            // Reset forms and hide messages
            document.getElementById('addLessonForm').reset();
            document.getElementById('editLessonForm').reset();
            document.getElementById('deleteLessonsButton').reset();
            hideMessages();
        }

        function hideMessages() {
            document.getElementById('errorMessage').classList.add('hidden');
            document.getElementById('editErrorMessage').classList.add('hidden');
        }

        function showError(message, isEditForm = false) {
            const errorDiv = isEditForm ? document.getElementById('editErrorMessage') : document.getElementById('errorMessage');
            const errorText = isEditForm ? document.getElementById('editErrorText') : document.getElementById('errorText');

            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        // Lesson-specific Functions
        function addLessons() {
            openModal('addLessonModal');
        }

        function editLesson(id) {
            // Find the lesson data
            const lesson = allLessons.find(item => item.id_pelajaran == id);

            if (!lesson) {
                alert('Data pelajaran tidak ditemukan');
                return;
            }

            // Fill the edit form
            document.getElementById('edit_id_pelajaran').value = lesson.id_pelajaran;
            document.getElementById('edit_pelajaran').value = lesson.pelajaran;

            // Open the modal
            openModal('editLessonModal');
        }

        // Form Validations
        document.getElementById('addLessonForm').addEventListener('submit', function(e) {
            const pelajaran = document.getElementById('pelajaran').value.trim();

            if (!pelajaran) {
                e.preventDefault();
                showError('Nama pelajaran tidak boleh kosong!');
            }
        });

        document.getElementById('editLessonForm').addEventListener('submit', function(e) {
            const pelajaran = document.getElementById('edit_pelajaran').value.trim();

            if (!pelajaran) {
                e.preventDefault();
                showError('Nama pelajaran tidak boleh kosong!', true);
            }
        });

        function deleteLesson(id) {
            // Cari data pelajaran
            const lesson = allLessons.find(item => item.id_pelajaran == id);

            if (!lesson) {
                alert('Data pelajaran tidak ditemukan');
                return;
            }

            // Isi form delete
            document.getElementById('delete_id_pelajaran').value = lesson.id_pelajaran;
            document.getElementById('deleteLessonName').textContent = lesson.pelajaran;

            // Tampilkan modal
            openModal('deleteLessonModal');
        }

        function showSuccessNotification(message) {
            const notification = document.getElementById('successNotification');
            const messageElement = document.getElementById('successMessage');

            messageElement.textContent = message;
            notification.classList.remove('hidden');
            notification.classList.add('animate-fade-in-up');
            notification.classList.remove('animate-fade-out');

            // Hide after 3 seconds
            setTimeout(() => {
                notification.classList.remove('animate-fade-in-up');
                notification.classList.add('animate-fade-out');
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 500);
            }, 3000);
        }
    </script>
</body>

</html>