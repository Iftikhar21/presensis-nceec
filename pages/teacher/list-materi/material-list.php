<?php
// Tambahkan ini dulu
ini_set('session.cookie_lifetime', 0); // session hilang saat browser ditutup

session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-teacher/crud-teacher.php';
include '../../../includes/crud/crud-material/crud-material.php';
include '../../../includes/crud/crud-lessons/crud-lessons.php';

// Pengecekan session utama
if (!isset($_SESSION['username']) && !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];

$data_teacher = getTeacherWhereId($id_user);
if (!$data_teacher['status']) {
    echo "<p>Error: " . $data_teacher['message'] . "</p>";
    exit();
}

if (!isset($data_teacher['teacher']['username'])) {
    echo "<p>Error: Teacher data is incomplete.</p>";
    exit();
}

$username = $data_teacher['teacher']['username'];
$email = $data_teacher['teacher']['email'];
$role = ucfirst($data_teacher['teacher']['role']);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'add':
            $result = insertMateri(
                $_POST['id_tutor'],
                $_POST['id_pelajaran'],
                $_POST['isi_materi'],
                $_POST['waktu']
            );
            echo json_encode($result);
            exit;

        case 'update':
            $result = updateMateri(
                $_POST['id_materi'],
                $_POST['id_tutor'],
                $_POST['id_pelajaran'],
                $_POST['isi_materi'],
                $_POST['waktu']
            );
            echo json_encode($result);
            exit;

        case 'delete':
            $result = deleteMateri($_POST['id_materi']);
            echo json_encode($result);
            exit;

        case 'get_all':
            $result = getAllMaterial();
            echo json_encode($result);
            exit;

        case 'get_by_id':
            $result = getMaterialWhereId($_POST['id_materi']);
            echo json_encode($result);
            exit;
    }
}

// Get all materi data for initial load
$all_materi = getAllMaterial();
$materi_count = $all_materi['status'] ? count($all_materi['materi']) : 0;

// Get pelajaran data for dropdown


$pelajaran_list = getAllLessons();
$tutor_list = getAllTeachers();

$title_page = "NCEEC";

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
        .animate-fade-in-down {
            animation: fadeInDown 0.5s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        .btn-hover:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .btn-focus:focus {
            outline: 2px solid rgb(59, 130, 246);
            outline-offset: 2px;
        }

        .menu-item {
            transition: all 0.2s ease-in-out;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .menu-item.active {
            background-color: rgba(59, 130, 246, 0.2);
            border-right: 3px solid rgb(59, 130, 246);
        }

        .content-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .rotate-180 {
            transform: rotate(180deg);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive table */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 14px;
            }

            .table-responsive th,
            .table-responsive td {
                padding: 8px 4px;
            }
        }

        /* Loading spinner */
        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Form validation styles */
        .form-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .form-success {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        /* Modal styling */
        .modal-backdrop {
            backdrop-filter: blur(5px);
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
                <h2 id="page-title" class="ml-4 lg:ml-0 text-xl font-semibold">List Materi</h2>
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
            <div class="max-w-7xl mx-auto">
                <!-- Welcome Card -->
                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-6 overflow-hidden relative animate-fade-in-down">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full bg-blue-100 opacity-50"></div>
                    <div class="flex items-center justify-between relative z-5">
                        <div>
                            <h3 class="text-xl md:text-2xl font-semibold mb-1">Kelola Materi Pembelajaran</h3>
                            <p class="text-xs md:text-sm text-muted">Tambah, edit, dan kelola materi pembelajaran dengan mudah</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl md:text-3xl font-bold text-blue-600" id="total-materi-count"><?= $materi_count ?></div>
                            <div class="text-xs md:text-sm text-gray-500">Total Materi</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4">
                    <button onclick="openAddModal()" class="btn-primary flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambah Materi Baru
                    </button>

                    <div class="flex gap-2">
                        <div class="relative">
                            <input type="text" id="search-input" placeholder="Cari materi..."
                                class="pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full sm:w-64">
                            <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>

                        <select id="filter-pelajaran" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Pelajaran</option>
                            <?php foreach ($pelajaran_list as $pelajaran): ?>
                                <option value="<?= $pelajaran['id_pelajaran'] ?>"><?= $pelajaran['pelajaran'] ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button onclick="refreshData()" class="px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fa-solid fa-refresh"></i>
                        </button>
                    </div>
                </div>

                <!-- Materials Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="materials-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelajaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="materials-table-body" class="bg-white divide-y divide-gray-200">
                                <!-- Content will be loaded here via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Loading State -->
                    <div id="loading-state" class="text-center py-8">
                        <i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Memuat data...</p>
                    </div>

                    <!-- Empty State -->
                    <div id="empty-state" class="text-center py-12 hidden">
                        <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada materi</h3>
                        <p class="text-gray-500 mb-4">Mulai dengan menambahkan materi pembelajaran pertama</p>
                        <button onclick="openAddModal()" class="btn-primary px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Tambah Materi
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span id="showing-from">0</span> - <span id="showing-to">0</span> dari <span id="total-items">0</span> materi
                    </div>
                    <div class="flex space-x-2">
                        <button id="prev-page" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div id="page-numbers" class="flex space-x-1">
                            <!-- Page numbers will be generated here -->
                        </div>
                        <button id="next-page" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="material-modal" class="modal fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-fade-in-down">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 id="modal-title" class="text-lg font-semibold">Tambah Materi Baru</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="material-form">
                    <input type="hidden" id="material-id" value="">
                    <input type="hidden" id="form-action" value="add">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tutor *</label>
                            <select id="tutor-select" name="id_tutor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Pilih Tutor</option>
                                <?php foreach ($tutor_list as $tutor): ?>
                                    <option value="<?= $tutor['id_tutor'] ?>"><?= $tutor['nama_tutor'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pelajaran *</label>
                            <select id="pelajaran-select" name="id_pelajaran" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Pilih Pelajaran</option>
                                <?php foreach ($pelajaran_list as $pelajaran): ?>
                                    <option value="<?= $pelajaran['id_pelajaran'] ?>"><?= $pelajaran['pelajaran'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Isi Materi *</label>
                        <textarea id="materi-content" name="isi_materi" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Masukkan isi materi pembelajaran..." required></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Pembelajaran *</label>
                        <input type="datetime-local" id="waktu-pembelajaran" name="waktu" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" id="submit-btn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fa-solid fa-save mr-2"></i>
                            <span id="submit-text">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto animate-fade-in-down">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Detail Materi</h3>
                    <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6" id="detail-content">
                <!-- Detail content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let materialsData = [];
        let pelajaranData = <?= json_encode($pelajaran_list) ?>;
        let tutorData = <?= json_encode($tutor_list) ?>;
        let currentPage = 1;
        let itemsPerPage = 10;
        let filteredData = [];

        // Utility functions
        function formatDateTime(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getPelajaranName(id) {
            const pelajaran = pelajaranData.find(p => p.id_pelajaran == id);
            return pelajaran ? pelajaran.pelajaran : 'Unknown';
        }

        function getTutorName(id) {
            const tutor = tutorData.find(t => t.id_tutor == id);
            return tutor ? tutor.nama_tutor : 'Unknown';
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            } shadow-lg transform translate-x-full transition-transform duration-300`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fa-solid ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-times' : 'fa-info'} mr-2"></i>
                    ${message}
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // AJAX Functions
        function sendAjaxRequest(action, data = {}) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('action', action);

                for (const key in data) {
                    formData.append(key, data[key]);
                }

                fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => resolve(data))
                    .catch(error => reject(error));
            });
        }

        // Data loading functions
        function renderMaterials() {
            const tableBody = document.getElementById('materials-table-body');
            const emptyState = document.getElementById('empty-state');

            if (filteredData.length === 0) {
                emptyState.classList.remove('hidden');
                tableBody.innerHTML = '';
                updatePagination();
                return;
            }

            emptyState.classList.add('hidden');

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageData = filteredData.slice(startIndex, endIndex);

            // Kosongkan tabel terlebih dahulu
            tableBody.innerHTML = '';

            // Tambahkan baris satu per satu dengan event listener yang benar
            pageData.forEach((material, index) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors duration-150';
                row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${startIndex + index + 1}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs">
                    <div class="text-sm text-gray-500 truncate">${material.isi_materi.substring(0, 100)}${material.isi_materi.length > 100 ? '...' : ''}</div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${getPelajaranName(material.id_pelajaran)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${getTutorName(material.id_tutor)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatDateTime(material.waktu)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button class="view-btn text-blue-600 hover:text-blue-900">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <button class="edit-btn text-indigo-600 hover:text-indigo-900">
                    <i class="fa-solid fa-edit"></i>
                </button>
                <button class="delete-btn text-red-600 hover:text-red-900">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;

                // Tambahkan event listener untuk setiap tombol
                row.querySelector('.view-btn').addEventListener('click', () => viewDetail(material.id_materi));
                row.querySelector('.edit-btn').addEventListener('click', () => editMaterial(material.id_materi));
                row.querySelector('.delete-btn').addEventListener('click', () => deleteMaterial(material.id_materi));

                tableBody.appendChild(row);
            });

            updatePagination();
        }

        function applyFilters() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const selectedPelajaran = document.getElementById('filter-pelajaran').value;

            console.log('Applying filters:', {
                searchTerm,
                selectedPelajaran
            });
            console.log('Materials data before filter:', materialsData);

            filteredData = materialsData.filter(material => {
                const matchesSearch = material.isi_materi.toLowerCase().includes(searchTerm) ||
                    getPelajaranName(material.id_pelajaran).toLowerCase().includes(searchTerm) ||
                    getTutorName(material.id_tutor).toLowerCase().includes(searchTerm);

                const matchesPelajaran = !selectedPelajaran ||
                    material.id_pelajaran == selectedPelajaran;

                return matchesSearch && matchesPelajaran;
            });

            console.log('Filtered data after filter:', filteredData);

            currentPage = 1;
            renderMaterials();
        }

        function updatePagination() {
            const totalItems = filteredData.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            console.log('Updating pagination:', {
                totalItems,
                totalPages,
                currentPage
            });

            document.getElementById('showing-from').textContent = totalItems === 0 ? 0 : ((currentPage - 1) * itemsPerPage) + 1;
            document.getElementById('showing-to').textContent = Math.min(currentPage * itemsPerPage, totalItems);
            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('total-materi-count').textContent = totalItems;

            const prevBtn = document.getElementById('prev-page');
            const nextBtn = document.getElementById('next-page');

            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;

            // Generate page numbers
            const pageNumbers = document.getElementById('page-numbers');
            pageNumbers.innerHTML = '';

            if (totalPages === 0) return;

            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = `px-3 py-2 border rounded-lg ${
            i === currentPage 
                ? 'bg-blue-600 text-white border-blue-600' 
                : 'border-gray-300 hover:bg-gray-50'
        }`;
                pageBtn.onclick = () => {
                    currentPage = i;
                    renderMaterials();
                };
                pageNumbers.appendChild(pageBtn);
            }
        }

        // Enhanced loadMaterials function
        async function loadMaterials() {
            const tableBody = document.getElementById('materials-table-body');
            const loadingState = document.getElementById('loading-state');
            const emptyState = document.getElementById('empty-state');

            // Show loading
            loadingState.classList.remove('hidden');
            emptyState.classList.add('hidden');
            tableBody.innerHTML = '';

            console.log('Loading materials...');

            try {
                const result = await sendAjaxRequest('get_all');

                console.log('AJAX Result:', result);

                if (result.status) {
                    materialsData = result.materi || [];
                    console.log('Materials data assigned:', materialsData);
                    console.log('Materials count:', materialsData.length);

                    // Make sure to call applyFilters after data is loaded
                    applyFilters();
                } else {
                    console.error('Load materials failed:', result.message);
                    showNotification('Error loading data: ' + result.message, 'error');
                    materialsData = [];
                    filteredData = [];
                    renderMaterials();
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                showNotification('Error loading data: ' + error.message, 'error');
                materialsData = [];
                filteredData = [];
                renderMaterials();
            }

            loadingState.classList.add('hidden');
        }

        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Tambah Materi Baru';
            document.getElementById('material-id').value = '';
            document.getElementById('form-action').value = 'add';
            document.getElementById('material-form').reset();
            document.getElementById('material-modal').classList.remove('hidden');
        }

        async function viewDetail(id) {
            console.log('View detail:', id);
            const result = await sendAjaxRequest('get_by_id', {
                id_materi: id
            });

            if (result.status) {
                const material = result.materi;
                // Isi modal detail
                document.getElementById('detail-content').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Informasi Dasar</h4>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500">Pelajaran:</span>
                                <p class="font-medium">${getPelajaranName(material.id_pelajaran)}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Tutor:</span>
                                <p class="font-medium">${getTutorName(material.id_tutor)}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Waktu:</span>
                                <p class="font-medium">${formatDateTime(material.waktu)}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-700 mb-3">Isi Materi</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 leading-relaxed">${material.isi_materi}</p>
                    </div>
                </div>
            </div>
        `;
                document.getElementById('detail-modal').classList.remove('hidden');
            } else {
                showNotification('Error: ' + result.message, 'error');
            }
        }

        async function editMaterial(id) {
            console.log('Edit material:', id);
            const result = await sendAjaxRequest('get_by_id', {
                id_materi: id
            });

            if (result.status) {
                const material = result.materi;
                document.getElementById('modal-title').textContent = 'Edit Materi';
                document.getElementById('material-id').value = material.id_materi;
                document.getElementById('form-action').value = 'update';
                document.getElementById('tutor-select').value = material.id_tutor;
                document.getElementById('pelajaran-select').value = material.id_pelajaran;
                document.getElementById('materi-content').value = material.isi_materi;
                document.getElementById('waktu-pembelajaran').value = material.waktu;
                document.getElementById('material-modal').classList.remove('hidden');
            } else {
                showNotification('Error: ' + result.message, 'error');
            }
        }

        async function deleteMaterial(id) {
            console.log('Delete material:', id);
            if (confirm('Apakah Anda yakin ingin menghapus materi ini?')) {
                const result = await sendAjaxRequest('delete', {
                    id_materi: id
                });

                if (result.status) {
                    showNotification('Materi berhasil dihapus', 'success');
                    await loadMaterials();
                } else {
                    showNotification('Error: ' + result.message, 'error');
                }
            }
        }

        function closeModal() {
            document.getElementById('material-modal').classList.add('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
        }

        function refreshData() {
            loadMaterials();
            showNotification('Data diperbarui', 'success');
        }

        // Event listeners
        document.getElementById('search-input').addEventListener('input', applyFilters);
        document.getElementById('filter-pelajaran').addEventListener('change', applyFilters);

        document.getElementById('prev-page').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderMaterials();
            }
        });

        document.getElementById('next-page').addEventListener('click', () => {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderMaterials();
            }
        });

        document.getElementById('material-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = {
                id_materi: document.getElementById('material-id').value,
                id_tutor: document.getElementById('tutor-select').value,
                id_pelajaran: document.getElementById('pelajaran-select').value,
                isi_materi: document.getElementById('materi-content').value,
                waktu: document.getElementById('waktu-pembelajaran').value,
                action: document.getElementById('form-action').value
            };

            try {
                let result;
                if (formData.action === 'add') {
                    result = await sendAjaxRequest('add', formData);
                } else {
                    result = await sendAjaxRequest('update', formData);
                }

                if (result.status) {
                    showNotification(
                        formData.action === 'add' ?
                        'Materi berhasil ditambahkan' :
                        'Materi berhasil diperbarui',
                        'success'
                    );
                    closeModal();
                    await loadMaterials();
                } else {
                    showNotification('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat memproses data', 'error');
            }
        });

        // User menu functionality
        document.getElementById('user-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('user-menu');
            const icon = document.getElementById('user-menu-icon');
            menu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const button = document.getElementById('user-menu-button');
            const menu = document.getElementById('user-menu');
            const icon = document.getElementById('user-menu-icon');

            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        });

        // Sidebar functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function setActivePage(pageName) {
            document.getElementById('page-title').textContent = pageName;

            // Update active menu item
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });

            if (pageName === 'List Materi') {
                document.getElementById('menu-materi').classList.add('active');
            } else if (pageName === 'Dashboard') {
                document.getElementById('menu-dashboard').classList.add('active');
            }
        }

        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = '../../../pages/auth/logout.php';
            }
        }

        // Close modals when clicking outside
        document.getElementById('material-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDetailModal();
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDetailModal();
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadMaterials();

            document.getElementById('materials-table-body').addEventListener('click', function(e) {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;

                const action = btn.getAttribute('data-action');
                const id = btn.getAttribute('data-id');

                switch (action) {
                    case 'view':
                        viewDetail(id);
                        break;
                    case 'edit':
                        editMaterial(id);
                        break;
                    case 'delete':
                        deleteMaterial(id);
                        break;
                }
            });
        });

        // Auto-refresh data every 30 seconds
        setInterval(() => {
            loadMaterials();
        }, 30000);
    </script>
</body>

</html>