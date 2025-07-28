<?php
require_once(__DIR__ . '/../../../config.php');

function getAllLessons() {
    $conn = connectDatabase();
    $query = "SELECT * FROM pelajaran";
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

function getLessonsWhereId($id) {
    $conn = connectDatabase();
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM pelajaran WHERE id_pelajaran = '$id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    return mysqli_fetch_assoc($result);
}

function getAllCountLessons() {
    $conn = connectDatabase();
    $query = "SELECT COUNT(*) as count FROM pelajaran";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}