<?php
require_once(__DIR__ . '/../../../config.php');

function getMateriWhereId($id) {
    $conn = connectDatabase();
    $query = "SELECT * FROM materi_diajarkan WHERE id_materi = '$id'";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return [
            "status" => false,
            "message" => "Error fetching materi: " . mysqli_error($conn)
        ];
    }
    $materi = mysqli_fetch_assoc($result);
    return [
        "status" => true,
        "materi" => $materi
    ];
}
function getAllMateri() {
    $conn = connectDatabase();
    $query = "SELECT * FROM `materi_diajarkan`";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("MySQL Error: " . mysqli_error($conn)); // Log error
        return [
            "status" => false,
            "message" => "Error fetching materi: " . mysqli_error($conn)
        ];
    }

    $materiList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $materiList[] = $row;
    }

    error_log("Total materi fetched: " . count($materiList)); // Log jumlah data
    
    return [
        "status" => true,
        "materi" => $materiList
    ];
}
function deleteMateri($id) {
    $conn = connectDatabase();
    $query = "DELETE FROM materi_diajarkan WHERE id_materi = '$id'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [
            "status" => false,
            "message" => "Error deleting materi: " . mysqli_error($conn)
        ];
    }

    return [
        "status" => true,
        "message" => "Materi deleted successfully"
    ];
}
function insertMateri($id_tutor, $id_pelajaran, $isi_materi, $waktu) {
    $conn = connectDatabase();
    $query = "INSERT INTO materi_diajarkan (id_tutor, id_pelajaran, isi_materi, waktu) 
              VALUES ('$id_tutor', '$id_pelajaran', '$isi_materi', '$waktu')";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [
            "status" => false,
            "message" => "Error inserting materi: " . mysqli_error($conn)
        ];
    }

    return [
        "status" => true,
        "message" => "Materi inserted successfully"
    ];
}

function updateMateri($id_materi, $id_tutor, $id_pelajaran, $isi_materi, $waktu) {
    $conn = connectDatabase();
    $query = "UPDATE materi_diajarkan 
              SET id_tutor = '$id_tutor', id_pelajaran = '$id_pelajaran', isi_materi = '$isi_materi', waktu = '$waktu' 
              WHERE id_materi = '$id_materi'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [
            "status" => false,
            "message" => "Error updating materi: " . mysqli_error($conn)
        ];
    }

    return [
        "status" => true,
        "message" => "Materi updated successfully"
    ];
}
function getAllPelajaran() {
    $conn = connectDatabase();
    $query = "SELECT * FROM pelajaran";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [];
    }

    $pelajaranList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pelajaranList[] = $row;
    }
    return $pelajaranList;
}

// Get tutor data for dropdown
function getAllTutor() {
    $conn = connectDatabase();
    $query = "SELECT * FROM tutor";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [];
    }

    $tutorList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tutorList[] = $row;
    }
    return $tutorList;
}
?>