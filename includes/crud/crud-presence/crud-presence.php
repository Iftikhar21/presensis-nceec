<?php
require_once(__DIR__ . '/../../../config.php');
date_default_timezone_set('Asia/Jakarta');

function getAllCountAbsentToday() {
    $date_now = date('Y-m-d'); 
    $conn = connectDatabase();
    $query = "SELECT COUNT(*) AS total_absen 
              FROM absensi 
              WHERE tanggal = ? AND status = 'Hadir'";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date_now);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total_absen'];
}
