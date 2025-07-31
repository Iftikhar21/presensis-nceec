<?php
// Tambahkan ini dulu
ini_set('session.cookie_lifetime', 0); // session hilang saat browser ditutup

session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-admin/crud-admin.php';
include '../../../includes/crud/crud-presence/crud-presence.php';


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

$data_absent_today = getAbsentWhereToday();
$countAbsentToday = getAllCountAbsentToday();

// Handle AJAX request for date filtering
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'get_by_date' && isset($_GET['date'])) {
        $date = $_GET['date'];
        $response = getAllAbsentWhereSelectedDate($date); // Gunakan fungsi yang baru
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Absen Tutor - <?= $title_page; ?></title>
    <link rel="icon" href="../../../assets/img/nceec-logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../../../assets/css/index.css">
    <link rel="stylesheet" href="../../../assets/css/app.css">
    <link rel="stylesheet" href="../../../assets/css/main.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

                <a href="../data-absent-tutor/absent-tutor.php" onclick="setActivePage('Data Absen Tutor')" class="menu-item active flex items-center px-4 py-3 text-sm rounded-lg" id="menu-absen">
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
                <h2 id="page-title" class="ml-4 lg:ml-0 text-xl font-semibold">Data Absen Tutor</h2>
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
                        <h3 class="text-xl md:text-2xl font-semibold mb-1">Lihat Data Absen Tutor</h3>
                        <p class="text-xs md:text-sm text-muted">Lihat Tutor yang Sudah Absen</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-xl md:text-3xl font-bold text-blue-600 mb-1"><?= $countAbsentToday; ?></h3>
                        <p class="text-xs md:text-sm text-muted">Tutor yang Sudah Absen</p>
                    </div>
                </div>
            </div>
            <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative animate-fade-in-down">
                <!-- Header -->
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Data Absen Tutor</h2>
                </div>

                <!-- Filter Section -->
                <div class="p-4 sm:p-6 mb-6 border border-gray-200 rounded-lg bg-gray-50">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Filter Nama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tutor</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="filterNama"
                                    placeholder="Cari nama tutor..."
                                    class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                            </div>
                        </div>

                        <!-- Filter Tanggal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-calendar text-gray-400"></i>
                                </div>
                                <input
                                    type="text"
                                    id="filterTanggal"
                                    placeholder="Pilih tanggal..."
                                    class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                            </div>
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Status</label>
                            <select
                                id="filterStatus"
                                class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                                <option value="">Semua Status</option>
                                <option value="Hadir">Hadir</option>
                                <option value="Terlambat">Terlambat</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpa">Alpa</option>
                            </select>
                        </div>

                        <!-- Filter Mood -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Mood</label>
                            <select
                                id="filterMood"
                                class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                                <option value="">Semua Mood</option>
                                <option value="Happy">Baik</option>
                                <option value="Normal">Biasa Aja</option>
                                <option value="Bad">Buruk</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <button
                            onclick="resetFilters()"
                            class="col-span-1 px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 flex items-center justify-center">
                            <svg class="h-4 w-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset
                        </button>
                        <button
                            onclick="applyFilters()"
                            class="col-span-1 px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 flex items-center justify-center">
                            <svg class="h-4 w-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Terapkan
                        </button>
                        <button
                            onclick="exportToExcel()"
                            class="col-span-2 sm:col-span-1 px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150 flex items-center justify-center">
                            <i class="fa-solid fa-file-excel mr-1 sm:mr-2"></i>
                            Export Excel
                        </button>
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
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Nama Tutor</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Mood</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Keterangan</th>
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


    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script src="../../../assets/js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


    <script>
        // Data dan konfigurasi awal
        let allTeachers = <?php echo json_encode($data_absent_today['data']); ?>;
        let filteredTeachers = [...allTeachers];
        let currentPage = 1;
        const rowsPerPage = 10;

        // Inisialisasi flatpickr untuk filter tanggal
        document.addEventListener('DOMContentLoaded', function() {
            // Setup date picker dengan konfigurasi yang lebih baik
            flatpickr("#filterTanggal", {
                dateFormat: "Y-m-d",
                locale: "id",
                allowInput: true,
                maxDate: "today",
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    console.log('Date changed:', dateStr);
                }
            });

            // Tambahkan event listener untuk filter nama (real-time)
            document.getElementById('filterNama').addEventListener('input', debounce(applyFilters, 500));

            // Render tabel awal
            renderTable();
            updatePaginationInfo();
            setupPaginationListeners();
        });

        function showNotification(message, type = 'info') {
            // Buat element notifikasi
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;

            // Set warna berdasarkan type
            switch (type) {
                case 'success':
                    notification.className += ' bg-green-500 text-white';
                    break;
                case 'error':
                    notification.className += ' bg-red-500 text-white';
                    break;
                case 'warning':
                    notification.className += ' bg-yellow-500 text-white';
                    break;
                default:
                    notification.className += ' bg-blue-500 text-white';
            }

            notification.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        async function applyFilters() {
            const namaFilter = document.getElementById('filterNama').value.toLowerCase().trim();
            const tanggalFilter = document.getElementById('filterTanggal').value.trim();
            const statusFilter = document.getElementById('filterStatus').value.trim();
            const moodFilter = document.getElementById('filterMood').value.trim();

            console.log('Applying filters:', {
                namaFilter,
                tanggalFilter,
                statusFilter,
                moodFilter
            });

            // Jika ada filter tanggal, ambil data dari server
            if (tanggalFilter !== '') {
                try {
                    const response = await fetchDataByDate(tanggalFilter);
                    console.log('Server response:', response);

                    if (response.status) {
                        allTeachers = response.data;
                        console.log('Data loaded for date:', tanggalFilter, allTeachers);
                    } else {
                        console.error('Error fetching data:', response.message);
                        allTeachers = [];

                        // Tampilkan pesan error ke user
                        showNotification('Tidak ada data untuk tanggal yang dipilih', 'warning');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    allTeachers = [];
                    showNotification('Terjadi kesalahan saat mengambil data', 'error');
                }
            } else {
                // Jika tidak ada filter tanggal, ambil data hari ini
                const responseToday = <?php echo json_encode($data_absent_today); ?>;
                allTeachers = responseToday.status ? responseToday.data : [];
                console.log('Using today data:', allTeachers);
            }

            // Apply client-side filters
            filteredTeachers = allTeachers.filter(teacher => {
                // Filter nama
                const namaTutor = (teacher.nama_tutor || '').toLowerCase();
                const namaMatch = namaFilter === '' || namaTutor.includes(namaFilter);

                // Filter status
                const teacherStatus = teacher.status || '';
                const statusMatch = statusFilter === '' || teacherStatus === statusFilter;

                // Filter mood
                let moodMatch = true;
                if (moodFilter !== '') {
                    const teacherMood = teacher.mood || '';

                    if (moodFilter === 'Happy') {
                        moodMatch = teacherMood === 'Baik';
                    } else if (moodFilter === 'Normal') {
                        moodMatch = teacherMood === 'Biasa Aja';
                    } else if (moodFilter === 'Bad') {
                        moodMatch = teacherMood === 'Buruk';
                    } else {
                        moodMatch = teacherMood === moodFilter;
                    }
                }

                return namaMatch && statusMatch && moodMatch;
            });

            console.log('Filtered results:', filteredTeachers);

            currentPage = 1;
            renderTable();
            updatePaginationInfo();
        }

        async function fetchDataByDate(date) {
            try {
                // Perbaiki URL endpoint - gunakan file yang sama
                const response = await fetch(`?action=get_by_date&date=${date}`);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                console.log('Data received:', data); // Debug log
                return data;
            } catch (error) {
                console.error('Error fetching data:', error);
                return {
                    status: false,
                    message: error.message,
                    data: []
                };
            }
        }

        // Fungsi untuk mereset filter
        function resetFilters() {
            document.getElementById('filterNama').value = '';
            document.getElementById('filterTanggal').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterMood').value = '';

            // Reset flatpickr
            if (document.getElementById('filterTanggal')._flatpickr) {
                document.getElementById('filterTanggal')._flatpickr.clear();
            }

            // Kembalikan ke data hari ini (semua data)
            const responseToday = <?php echo json_encode($data_absent_today); ?>;
            allTeachers = responseToday.status ? responseToday.data : [];
            filteredTeachers = [...allTeachers];

            console.log('Filters reset, using today data:', allTeachers);

            currentPage = 1;
            renderTable();
            updatePaginationInfo();
        }

        // Fungsi untuk merender tabel
        function renderTable() {
            const tableBody = document.getElementById('tableBody');

            if (filteredTeachers.length === 0) {
                tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data yang sesuai dengan filter
                </td>
            </tr>
        `;
                return;
            }

            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const paginatedTeachers = filteredTeachers.slice(startIndex, endIndex);

            tableBody.innerHTML = paginatedTeachers.map(absent => {
                // Format status dan mood dengan nilai default jika tidak ada
                const status = absent.status || 'N/A';
                const mood = absent.mood || 'N/A';

                // Tentukan kelas CSS berdasarkan status
                let statusClass = '';
                let statusText = status;

                switch (status) {
                    case 'Hadir':
                        statusClass = 'bg-green-100 text-green-800';
                        break;
                    case 'Terlambat':
                        statusClass = 'bg-yellow-100 text-yellow-800';
                        break;
                    case 'Izin':
                        statusClass = 'bg-blue-100 text-blue-800';
                        break;
                    case 'Sakit':
                        statusClass = 'bg-purple-100 text-purple-800';
                        break;
                    case 'Alpa':
                        statusClass = 'bg-red-100 text-red-800';
                        break;
                    default:
                        statusClass = 'bg-gray-100 text-gray-800';
                        statusText = 'N/A';
                }

                // Tentukan kelas CSS berdasarkan mood
                let moodClass = '';
                let moodText = mood;

                switch (mood) {
                    case 'Baik':
                        moodClass = 'bg-green-100 text-green-800';
                        moodText = 'Baik';
                        break;
                    case 'Biasa Aja':
                        moodClass = 'bg-yellow-100 text-yellow-800';
                        moodText = 'Biasa Aja';
                        break;
                    case 'Buruk':
                        moodClass = 'bg-red-100 text-red-800';
                        moodText = 'Buruk';
                        break;
                    default:
                        moodClass = 'bg-gray-100 text-gray-800';
                        moodText = 'N/A';
                }

                return `
            <tr class="hover:bg-gray-50 transition duration-150 text-center">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-center">
                    ${absent.nama_tutor || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-center">
                    ${absent.tanggal ? formatDate(absent.tanggal) : 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-center">
                    ${absent.waktu || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${statusClass}">
                        ${statusText}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${moodClass}">
                        ${moodText}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    ${absent.keterangan || '-'}
                </td>
            </tr>
        `;
            }).join('');
        }

        // Fungsi untuk memformat tanggal
        function formatDate(dateString) {
            if (!dateString) return 'N/A';

            try {
                const date = new Date(dateString);

                if (isNaN(date.getTime())) {
                    return 'N/A';
                }

                const options = {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                };

                return date.toLocaleDateString('id-ID', options);
            } catch (error) {
                console.error('Error formatting date:', error);
                return 'N/A';
            }
        }

        // Fungsi untuk update info pagination
        function updatePaginationInfo() {
            const totalPages = Math.ceil(filteredTeachers.length / rowsPerPage);
            const startItem = filteredTeachers.length > 0 ? (currentPage - 1) * rowsPerPage + 1 : 0;
            const endItem = Math.min(currentPage * rowsPerPage, filteredTeachers.length);

            const paginationInfo = document.querySelector('.pagination-info');
            if (paginationInfo) {
                paginationInfo.textContent = `Menampilkan ${startItem}-${endItem} dari ${filteredTeachers.length} hasil`;
            }

            // Update tombol navigasi
            const prevButton = document.querySelector('.prev-page');
            const nextButton = document.querySelector('.next-page');
            const prevMobileButton = document.querySelector('.prev-page-mobile');
            const nextMobileButton = document.querySelector('.next-page-mobile');

            if (prevButton) prevButton.disabled = currentPage === 1;
            if (prevMobileButton) prevMobileButton.disabled = currentPage === 1;
            if (nextButton) nextButton.disabled = currentPage === totalPages || totalPages === 0;
            if (nextMobileButton) nextMobileButton.disabled = currentPage === totalPages || totalPages === 0;

            // Update tombol halaman
            updatePageButtons(totalPages);
        }

        // Fungsi untuk update tombol halaman
        function updatePageButtons(totalPages) {
            const pageButtonsContainer = document.querySelector('.page-buttons');
            if (!pageButtonsContainer) return;

            pageButtonsContainer.innerHTML = '';

            if (totalPages === 0) return;

            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            // Tombol halaman pertama
            if (startPage > 1) {
                const button = createPageButton('1', 1);
                pageButtonsContainer.appendChild(button);

                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700';
                    ellipsis.textContent = '...';
                    pageButtonsContainer.appendChild(ellipsis);
                }
            }

            // Tombol halaman tengah
            for (let i = startPage; i <= endPage; i++) {
                const button = createPageButton(i.toString(), i);
                pageButtonsContainer.appendChild(button);
            }

            // Tombol halaman terakhir
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700';
                    ellipsis.textContent = '...';
                    pageButtonsContainer.appendChild(ellipsis);
                }

                const button = createPageButton(totalPages.toString(), totalPages);
                pageButtonsContainer.appendChild(button);
            }
        }

        // Fungsi helper untuk membuat tombol halaman
        function createPageButton(text, pageNumber) {
            const button = document.createElement('button');
            const isActive = currentPage === pageNumber;

            button.className = `relative inline-flex items-center px-4 py-2 border ${
        isActive 
            ? 'bg-blue-50 border-blue-500 text-blue-600' 
            : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'
    } text-sm font-medium`;

            button.textContent = text;
            button.addEventListener('click', () => changePage(pageNumber));

            return button;
        }

        // Fungsi untuk navigasi halaman
        function changePage(newPage) {
            currentPage = newPage;
            renderTable();
            updatePaginationInfo();

            // Scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Setup event listeners untuk pagination
        function setupPaginationListeners() {
            // Pagination controls
            const prevButton = document.querySelector('.prev-page');
            const nextButton = document.querySelector('.next-page');
            const prevMobileButton = document.querySelector('.prev-page-mobile');
            const nextMobileButton = document.querySelector('.next-page-mobile');

            if (prevButton) {
                prevButton.addEventListener('click', () => {
                    if (currentPage > 1) changePage(currentPage - 1);
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', () => {
                    const totalPages = Math.ceil(filteredTeachers.length / rowsPerPage);
                    if (currentPage < totalPages) changePage(currentPage + 1);
                });
            }

            if (prevMobileButton) {
                prevMobileButton.addEventListener('click', () => {
                    if (currentPage > 1) changePage(currentPage - 1);
                });
            }

            if (nextMobileButton) {
                nextMobileButton.addEventListener('click', () => {
                    const totalPages = Math.ceil(filteredTeachers.length / rowsPerPage);
                    if (currentPage < totalPages) changePage(currentPage + 1);
                });
            }

            // Select all checkbox
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('#tableBody input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function exportToExcel() {
            // Validasi data
            if (filteredTeachers.length === 0) {
                showNotification('Tidak ada data untuk diexport', 'warning');
                return;
            }

            // Siapkan data
            const excelData = filteredTeachers.map(teacher => ({
                'Nama Tutor': teacher.nama_tutor || 'N/A',
                'Tanggal': teacher.tanggal ? formatExcelDate(teacher.tanggal) : 'N/A',
                'Waktu': teacher.waktu || 'N/A',
                'Status': teacher.status || 'N/A',
                'Mood': teacher.mood || 'N/A',
                'Keterangan': teacher.keterangan || '-'
            }));

            // Buat worksheet
            const worksheet = XLSX.utils.json_to_sheet(excelData);

            // Buat workbook
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Data Absen");

            // Format kolom lebar
            worksheet['!cols'] = [{
                    width: 25
                }, // Nama Tutor
                {
                    width: 15
                }, // Tanggal
                {
                    width: 10
                }, // Waktu
                {
                    width: 15
                }, // Status
                {
                    width: 15
                }, // Mood
                {
                    width: 30
                } // Keterangan
            ];

            // Generate nama file
            const today = new Date();
            const dateStr = today.toISOString().split('T')[0];
            const fileName = `Data_Absen_Tutor_${dateStr}.xlsx`;

            // Export file
            XLSX.writeFile(workbook, fileName);

            showNotification('Data berhasil diexport ke Excel', 'success');
        }

        // Format tanggal untuk Excel
        function formatExcelDate(dateString) {
            if (!dateString) return 'N/A';

            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';

                // Format: DD/MM/YYYY
                const day = date.getDate().toString().padStart(2, '0');
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const year = date.getFullYear();

                return `${day}/${month}/${year}`;
            } catch (error) {
                console.error('Error formatting date:', error);
                return 'N/A';
            }
        }

        function exportToExcelXLSX() {
            if (filteredTeachers.length === 0) {
                showNotification('Tidak ada data untuk diexport', 'warning');
                return;
            }

            // Prepare data for worksheet
            const data = [
                ['Nama Tutor', 'Tanggal', 'Waktu', 'Status', 'Mood', 'Keterangan']
            ];

            filteredTeachers.forEach(teacher => {
                data.push([
                    teacher.nama_tutor || 'N/A',
                    teacher.tanggal ? formatDateForExport(teacher.tanggal) : 'N/A',
                    teacher.waktu || 'N/A',
                    teacher.status || 'N/A',
                    teacher.mood || 'N/A',
                    teacher.keterangan || '-'
                ]);
            });

            // Create workbook
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, "Data Absen Tutor");

            // Generate file and download
            const today = new Date();
            const dateString = today.toISOString().split('T')[0];
            XLSX.writeFile(wb, `Data_Absen_Tutor_${dateString}.xlsx`);

            showNotification('Data berhasil diexport ke Excel', 'success');
        }

        // Fungsi untuk logout
        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = '../../auth/logout.php';
            }
        }
    </script>
</body>

</html>