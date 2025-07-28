<?php
require_once(__DIR__ . '/../../../config.php');

function getAllMaterial() {
    $conn = connectDatabase();
    $query = "SELECT * FROM materi";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    $teachers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $teachers[] = $row;
    }

    return $teachers;
}

function getMaterialWhereId($id) {
    $conn = connectDatabase();
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM materi WHERE id_materi = '$id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    return mysqli_fetch_assoc($result);
}

function getAllCountMaterial() {
    $conn = connectDatabase();
    $query = "SELECT COUNT(*) as count FROM materi";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}

function getMaterialsByLesson($lessonId) {
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

function getMaterialCountByLesson($lessonId) {
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