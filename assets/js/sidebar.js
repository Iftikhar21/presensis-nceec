
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
document.addEventListener('click', function (event) {
    const sidebar = document.getElementById('sidebar');
    const hamburger = event.target.closest('button[onclick="toggleSidebar()"]');

    if (!sidebar.contains(event.target) && !hamburger && window.innerWidth < 1024) {
        if (!sidebar.classList.contains('-translate-x-full')) {
            toggleSidebar();
        }
    }
});

// Handle window resize
window.addEventListener('resize', function () {
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