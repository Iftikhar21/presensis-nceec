<?php
    function connectDatabase() {
        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = '';
        $db_name = 'nceec_db';

        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

        if (!$conn) {
            error_log("Koneksi database gagal: " . mysqli_connect_error(), 0);

            die("Maaf, terjadi kesalahan pada sistem. Silakan coba lagi nanti.");
        }
        
        return $conn;
    }
?>