<?php
require_once(__DIR__ . '/../../../config.php');

function getAllMaterial()
{
    $conn = connectDatabase();
    $query = "SELECT * FROM `materi`";
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

function getMaterialWhereId($id)
{
    $conn = connectDatabase();
    $query = "SELECT * FROM materi WHERE id_materi = '$id'";
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

function getAllCountMaterial()
{
    $conn = connectDatabase();
    $query = "SELECT COUNT(*) as count FROM materi";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}

function getMaterialsByLesson($lessonId)
{
    $conn = connectDatabase();
    $lessonId = mysqli_real_escape_string($conn, $lessonId);
    $query = "SELECT * FROM materi WHERE id_pelajaran = '$lessonId'";
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

function getMaterialCountByLesson($lessonId)
{
    $conn = connectDatabase();
    $lessonId = mysqli_real_escape_string($conn, $lessonId);
    $query = "SELECT COUNT(*) as count FROM materi WHERE id_pelajaran = '$lessonId'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}


function deleteMateri($id)
{
    $conn = connectDatabase();
    $query = "DELETE FROM materi WHERE id_materi = '$id'";
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
function insertMateri($id_tutor, $id_pelajaran, $isi_materi, $waktu)
{
    $conn = connectDatabase();
    $query = "INSERT INTO materi (id_tutor, id_pelajaran, isi_materi, waktu) 
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

function updateMateri($id_materi, $id_tutor, $id_pelajaran, $isi_materi, $waktu)
{
    $conn = connectDatabase();
    $query = "UPDATE materi 
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
