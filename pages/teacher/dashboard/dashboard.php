<?php
// Tambahkan ini dulu
ini_set('session.cookie_lifetime', 0); // session hilang saat browser ditutup
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

session_start();
include '../../../includes/crud/crud-auth/crud-login.php';
include '../../../includes/crud/crud-teacher/crud-teacher.php';
include '../../../includes/crud/crud-material/crud-material.php';
include '../../../includes/crud/crud-lessons/crud-lessons.php';
include '../../../includes/crud/crud-presence/crud-presence.php';


// Pengecekan session utama
if (!isset($_SESSION['username']) || !isset($_SESSION['ID'])) {
    header("Location: ../../auth/form-login.php");
    exit();
}

$id_user = $_SESSION['ID'];

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

// Get material data with error handling
$all_materi = getAllMaterial();
$materi_count = $all_materi['status'] ? count($all_materi['materi']) : 0;

// Get lessons data with error handling
$lessons_data = getAllLessons();
$lessons_count = is_array($lessons_data) ? count($lessons_data) : 0;

$title_page = "NCEEC";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - <?= htmlspecialchars($title_page); ?></title>
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
                <a href="../dashboard/dashboard.php" onclick="setActivePage('Dashboard')" class="menu-item active flex items-center px-4 py-3 text-sm rounded-lg" id="menu-dashboard">
                    <i class="fa-solid fa-house text-lg mr-3"></i>
                    Dashboard
                </a>

                <a href="../list-materi/material-list.php" onclick="setActivePage('List Materi')" class="menu-item flex items-center px-4 py-3 text-sm rounded-lg" id="menu-materi">
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
                <h2 id="page-title" class="ml-4 lg:ml-0 text-xl font-semibold">Dashboard</h2>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="content-card rounded-lg p-6 bg-white shadow-md animate-fade-in-up animate-delay-200 hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-200 mr-3">
                                <i class="fa-solid fa-list-check text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-semibold mb-2">List Materi</h3>
                                <h3 class="text-5xl font-semibold mb-2 text-green-600"><?= $materi_count; ?></h3>
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
                                <h3 class="text-5xl font-semibold mb-2 text-purple-600"><?= $lessons_count; ?></h3>
                                <p class="text-muted">Tersedia</p>
                            </div>
                        </div>
                    </div>
                </div>
                
<!-- Attendance Chart Section -->
<div class="content-card rounded-lg p-6 bg-white shadow-md animate-fade-in-up animate-delay-600 mt-4">
    <h3 class="text-lg font-semibold mb-4">Riwayat Absensi 7 Hari Terakhir</h3>
    <div class="chart-container" style="position: relative; height:350px; width:100%">
        <canvas id="userAttendanceChart"></canvas>
    </div>
    <div class="mt-4 flex justify-center">
        <div class="flex items-center mr-4">
            <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
            <span class="text-sm">Hadir</span>
        </div>
        <div class="flex items-center mr-4">
            <div class="w-3 h-3 rounded-full bg-yellow-400 mr-2"></div>
            <span class="text-sm">Izin</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
            <span class="text-sm">Tidak Hadir</span>
        </div>
    </div>
</div>

            </div>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <script src="../../../assets/js/sidebar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php 
$userId = $_SESSION['ID'];
$userAttendance = getWeeklyAttendanceForUser($userId);
$labels = array_column($userAttendance, 'date');
$statusData = array_column($userAttendance, 'status');
$waktuData = array_column($userAttendance, 'waktu');
$moodData = array_column($userAttendance, 'mood');
$keteranganData = array_column($userAttendance, 'keterangan');

// Prepare data for chart
$chartData = [];
$pointDetails = [];
$backgroundColors = [];

foreach ($statusData as $index => $status) {
    switch($status) {
        case 'Hadir': 
            $chartData[] = 2;
            $backgroundColors[] = 'rgba(75, 192, 192, 0.2)';
            $pointDetails[] = [
                'waktu' => $waktuData[$index],
                'mood' => $moodData[$index],
                'keterangan' => $keteranganData[$index]
            ];
            break;
        case 'Izin': 
            $chartData[] = 1;
            $backgroundColors[] = 'rgba(255, 206, 86, 0.2)';
            $pointDetails[] = [
                'waktu' => null,
                'mood' => null,
                'keterangan' => null
            ];
            break;
        case 'Tidak Hadir':
        default: 
            $chartData[] = 0;
            $backgroundColors[] = 'rgba(255, 99, 132, 0.2)';
            $pointDetails[] = [
                'waktu' => null,
                'mood' => null,
                'keterangan' => null
            ];
    }
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('userAttendanceChart').getContext('2d');
    
    // Status colors
    const statusColors = {
        0: 'rgba(255, 99, 132, 1)',    // Tidak Hadir - Merah
        1: 'rgba(255, 206, 86, 1)',     // Izin - Kuning
        2: 'rgba(75, 192, 192, 1)'      // Hadir - Hijau
    };
    
    // Mood icons
    const moodIcons = {
        'senang': '😊',
        'biasa': '😐',
        'sedih': '😢',
        'lelah': '😫',
        'semangat': '💪'
    };
    
    const userAttendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Status Absensi',
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: <?php echo json_encode($backgroundColors); ?>,
                borderColor: 'rgba(54, 162, 235, 0.8)',
                borderWidth: 2,
                pointBackgroundColor: function(context) {
                    return statusColors[context.dataset.data[context.dataIndex]];
                },
                pointBorderColor: '#fff',
                pointHoverRadius: 8,
                pointRadius: 6,
                pointHitRadius: 12,
                fill: 'origin',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 2,
                    min: 0,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            const labels = {
                                0: 'Tidak Hadir',
                                1: 'Izin',
                                2: 'Hadir'
                            };
                            return labels[value];
                        },
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        lineWidth: 1,
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        beforeTitle: function(context) {
                            return `Tanggal: ${context[0].label}`;
                        },
                        label: function(context) {
                            const labels = {
                                0: 'Tidak Hadir',
                                1: 'Izin',
                                2: 'Hadir'
                            };
                            const index = context.dataIndex;
                            const details = <?php echo json_encode($pointDetails); ?>[index];
                            let tooltip = [];
                            
                            tooltip.push(`Status: ${labels[context.raw]}`);
                            
                            if (context.raw === 2) {
                                tooltip.push(`Waktu: ${details.waktu || '-'}`);
                                tooltip.push(`Mood: ${details.mood ? moodIcons[details.mood] + ' ' + details.mood : '-'}`);
                                if (details.keterangan) {
                                    tooltip.push(`Keterangan: ${details.keterangan}`);
                                }
                            }
                            
                            return tooltip;
                        },
                        labelColor: function(context) {
                            return {
                                borderColor: 'transparent',
                                backgroundColor: statusColors[context.raw],
                                borderRadius: 4
                            };
                        }
                    },
                    displayColors: false,
                    backgroundColor: '#fff',
                    titleColor: '#333',
                    bodyColor: '#333',
                    borderColor: '#eee',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 6,
                    bodyFont: {
                        weight: 'bold'
                    },
                    footerFont: {
                        style: 'italic',
                        size: 11
                    }
                },
                legend: {
                    display: false
                }
            },
            onClick: function(evt, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const status = <?php echo json_encode($statusData); ?>[index];
                    if (status === 'Hadir') {
                        const details = <?php echo json_encode($pointDetails); ?>[index];
                        // Show detailed modal
                        showAttendanceDetail(
                            <?php echo json_encode($labels); ?>[index],
                            status,
                            details.waktu,
                            details.mood,
                            details.keterangan
                        );
                    }
                }
            }
        }
    });

    function showAttendanceDetail(date, status, waktu, mood, keterangan) {
        const modal = document.createElement('div');
        modal.id = 'attendance-detail-modal';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-96">
                    <h2 class="text-lg font-semibold mb-4">Detail Absensi - ${date}</h2>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="font-medium w-24">Status:</span>
                            <span>${status}</span>
                        </div>
                        <div class="flex">
                            <span class="font-medium w-24">Waktu:</span>
                            <span>${waktu || '-'}</span>
                        </div>
                        <div class="flex">
                            <span class="font-medium w-24">Mood:</span>
                            <span>${mood ? moodIcons[mood] + ' ' + mood : '-'}</span>
                        </div>
                        <div class="flex">
                            <span class="font-medium w-24">Keterangan:</span>
                            <span>${keterangan || '-'}</span>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button id="close-detail-modal" class="px-4 py-2 bg-blue-500 text-white rounded">Tutup</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('close-detail-modal').addEventListener('click', () => {
            modal.remove();
        });
    }
});
</script>
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
            currentPage = pageName;
            document.getElementById('page-title').textContent = pageName;

            const contentTitle = document.getElementById('content-title');
            const contentDescription = document.getElementById('content-description');

            contentTitle.textContent = `Ini halaman ${pageName}`;

            const descriptions = {
                'Dashboard': 'Selamat datang di panel administrasi. Pilih menu di sidebar untuk navigasi ke halaman yang diinginkan.',
                'List Materi': 'Halaman untuk mengelola daftar materi pembelajaran. Anda dapat menambah, mengedit, atau menghapus materi di sini.',
                'Data Absen Tutor': 'Halaman untuk melihat dan mengelola data absensi tutor. Monitor kehadiran dan rekap absensi tutor.',
                'Data Tutor': 'Halaman untuk mengelola data tutor. Kelola informasi profil, jadwal, dan data tutor lainnya.'
            };

            contentDescription.textContent = descriptions[pageName] || `Konten untuk halaman ${pageName}`;

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

            if (window.innerWidth < 1024) {
                toggleSidebar();
            }
        }

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

        // Initialize
        updateDateTime();
        setInterval(updateDateTime, 1000);

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = event.target.closest('button[onclick="toggleSidebar()"]');

            if (!sidebar.contains(event.target) && !hamburger && window.innerWidth < 1024) {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            }
        });

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
            const currentRotation = userMenu.classList.contains('hidden') ? 180 : 0;
            userMenuIcon.style.transform = `rotate(${currentRotation}deg)`;
            userMenuIcon.style.transition = 'transform 0.3s ease';
            userMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenuIcon.style.transform = 'rotate(0deg)';
                userMenu.classList.add('hidden');
            }
        });
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