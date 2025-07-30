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
function getWeeklyAttendanceForUser($userId) {
    $conn = connectDatabase();
    
    // Get dates for the past 7 days including today
    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $dates[] = date('Y-m-d', strtotime("-$i days"));
    }
    
    $weeklyData = [];
    
    foreach ($dates as $date) {
        $query = "SELECT status, waktu, mood, keterangan 
                  FROM absensi 
                  WHERE tanggal = ? AND user_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $date, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $status = 'Tidak Hadir'; // Default
        $waktu = null;
        $mood = null;
        $keterangan = null;
        
        if ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            $waktu = $row['waktu'];
            $mood = $row['mood'];
            $keterangan = $row['keterangan'];
        }
        
        $weeklyData[] = [
            'date' => date('D, d M', strtotime($date)),
            'status' => $status,
            'waktu' => $waktu,
            'mood' => $mood,
            'keterangan' => $keterangan
        ];
    }
    
    return $weeklyData;
}