<?php
require_once(__DIR__ . '/../../../config.php');
date_default_timezone_set('Asia/Jakarta');

function getAbsentWhereToday() {
    $date_now = date('Y-m-d'); // Pastikan ini sesuai
    $conn = connectDatabase();
    $query = "SELECT * FROM absensi WHERE tanggal = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date_now);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        return [];
    }

    $absences = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $absences[] = $row;
    }

    return $absences;
}

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
