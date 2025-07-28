<?php
require_once(__DIR__ . '/../../../config.php');

function getAllTeachers() {
    $conn = connectDatabase();
    $query = "SELECT * FROM tutor INNER JOIN pelajaran ON tutor.id_pelajaran = pelajaran.id_pelajaran";
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

function getTeachersWhereId($id) {
    $conn = connectDatabase();
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM tutor WHERE id_tutor = '$id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    return mysqli_fetch_assoc($result);
}

function getAllCountTeachers() {
    $conn = connectDatabase();
    $query = "SELECT COUNT(*) as count FROM tutor";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}