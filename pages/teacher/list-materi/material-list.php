<?php
ini_set('session.cookie_lifetime', 0);
session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-lessons/crud-lessons.php';
include '../../../includes/crud/crud-material/crud-material.php';
include '../../../includes/crud/crud-teacher/crud-teacher.php';

// Pengecekan session
if (!isset($_SESSION['username']) && !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];

// Ambil data teacher dari users table
$data_teacher = getTeacherWhereId($id_user);

if (!$data_teacher['status'] || !isset($data_teacher['teacher']['username'])) {
    echo "<p>Error: Teacher data is incomplete.</p>";
    exit();
}

// Ambil data tutor dari tutor table berdasarkan user_id
$tutor_data = getTutorByUserId($id_user);

if (!$tutor_data) {
    echo "<p>Error: Tutor profile not found. Please complete your tutor profile first.</p>";
    exit();
}

$id_tutor = $tutor_data['id_tutor'];
$nama_tutor = $tutor_data['nama_tutor'];
$id_pelajaran_tutor = $tutor_data['id_pelajaran']; // Pelajaran yang diajarkan tutor ini

$username = $data_teacher['teacher']['username'];
$role = ucfirst($data_teacher['teacher']['role']);
$title_page = "NCEEC";

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'add':
            // Validasi bahwa pelajaran yang dipilih sesuai dengan yang diajarkan tutor
            if ($_POST['id_pelajaran'] != $id_pelajaran_tutor) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Anda hanya dapat menambahkan materi untuk pelajaran yang Anda ajarkan'
                ]);
                exit;
            }
            
            $result = insertMateri(
                $id_tutor,
                $_POST['id_pelajaran'],
                $_POST['isi_materi'],
                $_POST['waktu']
            );
            echo json_encode($result);
            exit;

        case 'update':
            // Validasi bahwa pelajaran yang dipilih sesuai dengan yang diajarkan tutor
            if ($_POST['id_pelajaran'] != $id_pelajaran_tutor) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Anda hanya dapat mengupdate materi untuk pelajaran yang Anda ajarkan'
                ]);
                exit;
            }
            
            $result = updateMateri(
                $_POST['id_materi'],
                $id_tutor,
                $_POST['id_pelajaran'],
                $_POST['isi_materi'],
                $_POST['waktu']
            );
            echo json_encode($result);
            exit;

        case 'delete':
            // Validasi bahwa materi yang akan dihapus adalah milik tutor ini
            $materi_check = getMaterialWhereId($_POST['id_materi']);
            if (!$materi_check['status'] || $materi_check['materi']['id_tutor'] != $id_tutor) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Anda hanya dapat menghapus materi yang Anda buat'
                ]);
                exit;
            }
            
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

        case 'get_materials_by_lesson':
            // Hanya ambil materi untuk pelajaran yang diajarkan tutor ini dan yang dibuat oleh tutor ini
            if ($_POST['lesson_id'] != $id_pelajaran_tutor) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses ke pelajaran ini'
                ]);
                exit;
            }
            
            $result = getMaterialsByLessonAndTutor($_POST['lesson_id'], $id_tutor);
            echo json_encode(['status' => true, 'materi' => $result]);
            exit;
    }
}

// Hanya ambil pelajaran yang diajarkan oleh tutor ini
$lessons = [];
$current_lesson = getLessonsWhereId($id_pelajaran_tutor);
if ($current_lesson) {
    $lessons[] = $current_lesson;
}
$totalLessons = count($lessons);

// Ambil materi berdasarkan pelajaran yang dipilih (jika ada) dan tutor yang sedang login
$selectedLesson = isset($_GET['lesson_id']) ? $_GET['lesson_id'] : null;

// Validasi bahwa lesson yang dipilih adalah yang diajarkan tutor ini
if ($selectedLesson && $selectedLesson != $id_pelajaran_tutor) {
    header("Location: material-list.php");
    exit();
}

$materials = $selectedLesson ? getMaterialsByLessonAndTutor($selectedLesson, $id_tutor) : [];
$totalMaterials = $selectedLesson ? count($materials) : 0;

// Get tutor list for dropdown (tidak digunakan lagi karena hanya tutor yang login)
$tutor_list = getAllTeachers();

// Function untuk mendapatkan materi berdasarkan pelajaran dan tutor
function getMaterialsByLessonAndTutor($lessonId, $tutorId) {
    $conn = connectDatabase();
    $lessonId = mysqli_real_escape_string($conn, $lessonId);
    $tutorId = mysqli_real_escape_string($conn, $tutorId);
    $query = "SELECT * FROM materi WHERE id_pelajaran = '$lessonId' AND id_tutor = '$tutorId' ORDER BY waktu DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    $materials = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = $row;
    }

    return $materials;
}
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
                            <h3 class="text-xl md:text-2xl font-semibold mb-1">Pelajaran Anda</h3>
                            <p class="text-xs md:text-sm text-muted">Pelajaran yang Anda ajarkan</p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-xl md:text-3xl font-bold text-blue-600 mb-1"><?= $totalLessons ?></h3>
                            <p class="text-xs md:text-sm text-muted">Pelajaran</p>
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
                                    <?= count(getMaterialsByLessonAndTutor($lesson['id_pelajaran'], $id_tutor)) ?> Materi
                                </span>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                    Pelajaran Anda
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    
                    <?php if (empty($lessons)): ?>
                        <div class="col-span-full text-center py-12">
                            <i class="fa-solid fa-book text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pelajaran</h3>
                            <p class="text-gray-500">Anda belum memiliki pelajaran yang diajarkan. Silakan hubungi administrator.</p>
                        </div>
                    <?php endif; ?>
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
                            <h3 class="text-xl md:text-2xl font-semibold mb-1">Materi Anda</h3>
                            <p class="text-xs md:text-sm text-muted flex flex-col md:flex-row md:items-center">
                                <span>Pelajaran: <?= htmlspecialchars($currentLesson['pelajaran']) ?></span>
                                <a href="material-list.php" class="text-blue-600 hover:text-blue-800 mt-1 md:mt-0 md:ml-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-xl md:text-3xl font-bold text-blue-600 mb-1" id="total-materi-count"><?= $totalMaterials ?></h3>
                            <p class="text-xs md:text-sm text-muted">Total Materi</p>
                        </div>
                    </div>
                </div>

                <div class="content-card rounded-lg p-6 bg-white shadow-md mb-4 overflow-hidden relative">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Materi untuk <?= htmlspecialchars($currentLesson['pelajaran']) ?>
                        </h3>
                        <button onclick="openAddModal()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Materi
                        </button>
                    </div>

                    <!-- Filter Section -->
                    <div class="p-6 mb-6 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Upload</label>
                                <input
                                    type="date"
                                    id="filterTanggal"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
                            </div>

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

                    <!-- Tabel Materi -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID Materi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Judul Materi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tutor</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal Upload</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                                <!-- Content will be loaded here via JavaScript -->
                            </tbody>
                        </table>

                        <!-- Loading State -->
                        <div id="loading-state" class="text-center py-8 hidden">
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
                    <input type="hidden" id="lesson-id" value="<?= $id_pelajaran_tutor ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tutor *</label>
                            <input type="hidden" id="tutor-id" name="id_tutor" value="<?= $id_tutor ?>">
                            <input type="text" id="tutor-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" 
                                   value="<?= htmlspecialchars($nama_tutor) ?>" readonly>
                            <small class="text-gray-500 text-xs mt-1">Tutor otomatis sesuai dengan yang sedang login</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pelajaran *</label>
                            <input type="hidden" id="pelajaran-id" name="id_pelajaran" value="<?= $id_pelajaran_tutor ?>">
                            <input type="text" id="pelajaran-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" 
                                   value="<?= htmlspecialchars($tutor_data['pelajaran']) ?>" readonly>
                            <small class="text-gray-500 text-xs mt-1">Pelajaran yang Anda ajarkan</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Isi Materi *</label>
                        <textarea id="materi-content" name="isi_materi" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Masukkan isi materi pembelajaran..." required></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Pembelajaran *</label>
                        <input type="datetime-local" id="waktu-pembelajaran" step="1" name="waktu" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <script src="../../../assets/js/sidebar.js"></script>
    <script>
        // Global variables
        let allMaterials = <?php echo json_encode($materials); ?>;
        let filteredMaterials = [...allMaterials];
        let currentPage = 1;
        const rowsPerPage = 5;
        let pelajaranData = <?= json_encode($lessons) ?>;
        let tutorData = <?= json_encode($tutor_list) ?>;
        const selectedLessonId = <?= json_encode($selectedLesson) ?>;
        const currentTutorId = <?= json_encode($id_tutor) ?>;
        const currentTutorName = <?= json_encode($nama_tutor) ?>;
        const tutorLessonId = <?= json_encode($id_pelajaran_tutor) ?>;
        const tutorLessonName = <?= json_encode($tutor_data['pelajaran']) ?>;

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
            // Untuk tutor, hanya bisa mengajar satu pelajaran
            return tutorLessonName || 'Unknown';
        }

        function getTutorName(id) {
            // Untuk tutor yang sedang login
            if (id == currentTutorId) {
                return currentTutorName;
            }
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
        async function loadMaterialsForLesson() {
            if (!selectedLessonId) return;

            const loadingState = document.getElementById('loading-state');
            const emptyState = document.getElementById('empty-state');
            const tableBody = document.getElementById('tableBody');

            // Show loading
            loadingState.classList.remove('hidden');
            emptyState.classList.add('hidden');
            tableBody.innerHTML = '';

            try {
                const result = await sendAjaxRequest('get_materials_by_lesson', {
                    lesson_id: selectedLessonId
                });

                if (result.status) {
                    allMaterials = result.materi || [];
                    filteredMaterials = [...allMaterials];
                    renderTable();
                    updatePaginationInfo();
                    document.getElementById('total-materi-count').textContent = allMaterials.length;
                } else {
                    showNotification('Error loading data: ' + result.message, 'error');
                    allMaterials = [];
                    filteredMaterials = [];
                    renderTable();
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                showNotification('Error loading data: ' + error.message, 'error');
                allMaterials = [];
                filteredMaterials = [];
                renderTable();
            }

            loadingState.classList.add('hidden');
        }

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
            const emptyState = document.getElementById('empty-state');
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const paginatedMaterials = filteredMaterials.slice(startIndex, endIndex);

            if (filteredMaterials.length === 0) {
                emptyState.classList.remove('hidden');
                tableBody.innerHTML = '';
                return;
            }

            emptyState.classList.add('hidden');

            tableBody.innerHTML = paginatedMaterials.map(material => `
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${material.id_materi || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${(material.isi_materi || 'N/A').substring(0, 50)}${(material.isi_materi || '').length > 50 ? '...' : ''}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${getTutorName(material.id_tutor)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        ${formatDateTime(material.waktu) || 'N/A'}
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
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Fungsi untuk update info pagination
        function updatePaginationInfo() {
            const totalPages = Math.ceil(filteredMaterials.length / rowsPerPage);
            const startItem = filteredMaterials.length === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
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
            if (nextPage) nextPage.disabled = currentPage === totalPages || totalPages === 0;
            if (nextPageMobile) nextPageMobile.disabled = currentPage === totalPages || totalPages === 0;

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

        // Modal functions
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Tambah Materi Baru';
            document.getElementById('material-id').value = '';
            document.getElementById('form-action').value = 'add';
            document.getElementById('material-form').reset();
            
            // Set tutor dan pelajaran yang tidak bisa diubah
            document.getElementById('tutor-id').value = currentTutorId;
            document.getElementById('tutor-name').value = currentTutorName;
            document.getElementById('pelajaran-id').value = tutorLessonId;
            document.getElementById('pelajaran-name').value = tutorLessonName;
            
            document.getElementById('material-modal').classList.remove('hidden');
        }

        async function viewMaterial(id) {
            const result = await sendAjaxRequest('get_by_id', {
                id_materi: id
            });

            if (result.status) {
                const material = result.materi;
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
            const result = await sendAjaxRequest('get_by_id', {
                id_materi: id
            });

            if (result.status) {
                const material = result.materi;
                document.getElementById('modal-title').textContent = 'Edit Materi';
                document.getElementById('material-id').value = material.id_materi;
                document.getElementById('form-action').value = 'update';
                
                // Set tutor dan pelajaran yang tidak bisa diubah
                document.getElementById('tutor-id').value = currentTutorId;
                document.getElementById('tutor-name').value = currentTutorName;
                document.getElementById('pelajaran-id').value = tutorLessonId;
                document.getElementById('pelajaran-name').value = tutorLessonName;
                
                document.getElementById('materi-content').value = material.isi_materi;
                document.getElementById('waktu-pembelajaran').value = material.waktu;
                document.getElementById('material-modal').classList.remove('hidden');
            } else {
                showNotification('Error: ' + result.message, 'error');
            }
        }

        async function deleteMaterial(id) {
            const modal = document.createElement('div');
            modal.id = 'delete-material-modal';
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-96">
                        <h2 class="text-lg font-semibold mb-4">Konfirmasi Hapus Materi</h2>
                        <p class="text-sm mb-6">Apakah Anda yakin ingin menghapus materi ini?</p>
                        <div class="flex justify-end space-x-4">
                            <button id="close-modal" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
                            <button id="confirm-delete" class="px-4 py-2 bg-red-500 text-white rounded">Hapus</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('close-modal').addEventListener('click', () => {
                modal.remove();
            });

            document.getElementById('confirm-delete').addEventListener('click', async () => {
                const result = await sendAjaxRequest('delete', {
                    id_materi: id
                });
                if (result.status) {
                    showNotification('Materi berhasil dihapus', 'success');
                    await loadMaterialsForLesson();
                } else {
                    showNotification('Error: ' + result.message, 'error');
                }
                modal.remove();
            });
        }

        function closeModal() {
            document.getElementById('material-modal').classList.add('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            if (selectedLessonId) {
                renderTable();
                updatePaginationInfo();

                // Event listeners for filter inputs
                document.getElementById('filterJudul').addEventListener('input', applyFilters);
                document.getElementById('filterTanggal').addEventListener('change', applyFilters);

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
            }
        });

        // Form submission
        document.getElementById('material-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = {
                id_materi: document.getElementById('material-id').value,
                id_tutor: currentTutorId,
                id_pelajaran: tutorLessonId,
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
                    await loadMaterialsForLesson();
                } else {
                    showNotification('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat memproses data', 'error');
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
    </script>
</body>

</html>