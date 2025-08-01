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

$actionURL = "profile.php"; // Current page name

// Pengecekan session dan autoritas
if (!isset($_SESSION['username']) || !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];

// Validasi teacher data
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

// Cek apakah user sudah memiliki profil tutor
$tutor_data = getTutorByUserId($id_user);
$isEdit = ($tutor_data !== null);

// Inisialisasi variabel untuk form
$id_tutor = $isEdit ? $tutor_data['id_tutor'] : '';
$nama_tutor = $isEdit ? htmlspecialchars($tutor_data['nama_tutor']) : '';
$id_pelajaran = $isEdit ? htmlspecialchars($tutor_data['id_pelajaran']) : '';
$bergabung = $isEdit ? htmlspecialchars($tutor_data['bergabung']) : date('Y-m-d');
$foto_profile = $isEdit ? htmlspecialchars($tutor_data['foto_profile']) : '';

// Get data pelajaran untuk dropdown
$lessons_data = getAllLessons();
$lessons_count = is_array($lessons_data) ? count($lessons_data) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama_tutor = $_POST['nama_tutor'] ?? '';
    $id_pelajaran = $_POST['id_pelajaran'] ?? '';
    $bergabung = $_POST['bergabung'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    // Handle file upload
    $foto_path = null;
    if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
        $upload_result = handleProfilePhotoUpload($_FILES['foto_profile'], $id_user);
        if ($upload_result['status']) {
            $foto_path = $upload_result['path'];
        } else {
            header("Location: $actionURL?status=error&message=" . urlencode($upload_result['message']));
            exit();
        }
    }

    if ($isEdit) {
        // Update existing tutor profile
        $result = updateTeacher(
            $id_tutor,
            $nama_tutor,
            $id_pelajaran,
            $bergabung,
            $foto_path,
            $username,
            $email
        );

        if ($result['status']) {
            header("Location: $actionURL?status=success&message=" . urlencode("Profile updated successfully"));
        } else {
            header("Location: $actionURL?status=error&message=" . urlencode($result['message']));
        }
    } else {
        // Create new tutor profile
        $result = createTeacher(
            $id_user,
            $nama_tutor,
            $id_pelajaran,
            $bergabung,
            $foto_path
        );

        if ($result['status']) {
            // Update username dan email di tabel users
            $update_result = updateUserProfile($id_user, $username, $email);

            if ($update_result['status']) {
                header("Location: $actionURL?status=success&message=" . urlencode("Profile created successfully"));
            } else {
                header("Location: $actionURL?status=error&message=" . urlencode($update_result['message']));
            }
        } else {
            header("Location: $actionURL?status=error&message=" . urlencode($result['message']));
        }
    }
    exit();
}

$title_page = "NCEEC";
?>
<!DOCTYPE html>
<html lang="id">
<!-- Bagian HTML tetap sama seperti sebelumnya -->
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
                            <a href="profile.php" class="block px-6 py-4 text-sm text-gray-700 hover:bg-gray-100">
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
                                    <strong>Sukses!</strong> <?= isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Operasi berhasil.' ?>
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
                            <?= $isEdit ? 'Profil Tutor' : 'Buat Profil Tutor' ?>
                        </h3>
                        <span class="<?= $isEdit ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?> text-sm font-medium px-2.5 py-0.5 rounded">
                            <i class="fa-solid <?= $isEdit ? 'fa-edit' : 'fa-plus' ?> mr-1"></i>
                            <?= $isEdit ? 'Mode Edit' : 'Mode Buat' ?>
                        </span>
                    </div>

                    <div class="<?= $isEdit ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200' ?> border rounded-lg p-4 mb-4">
                        <p class="text-sm <?= $isEdit ? 'text-blue-700' : 'text-green-700' ?>">
                            <i class="fa-solid fa-info-circle mr-2"></i>
                            <?= $isEdit ?
                                'Anda sedang mengedit data tutor. Silakan ubah data yang diperlukan dan klik "Update" untuk menyimpan perubahan.' :
                                'Anda belum memiliki profil tutor. Silakan isi data berikut untuk membuat profil tutor.' ?>
                        </p>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4" id="teacherForm">
                        <input type="hidden" name="id_tutor" value="<?= htmlspecialchars($id_tutor) ?>">

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
                                required
                                minlength="3"
                                maxlength="100">
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
                                Kosongkan jika tidak ingin mengubah foto.
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

                        <!-- Tombol Submit dan Kembali dalam satu baris -->
                        <div class="flex flex-row space-x-3 pt-4">
                            <button type="submit" name="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200 flex items-center justify-center">
                                <i class="fa-solid fa-save mr-2"></i>
                                <?= $isEdit ? 'Update Data' : 'Buat Profil' ?>
                            </button>

                            <a href="../../teacher/dashboard/dashboard.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition duration-200 flex items-center justify-center">
                                <i class="fa-solid fa-arrow-left mr-2"></i>
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Additional info -->
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