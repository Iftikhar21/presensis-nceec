<?php
require_once(__DIR__ . '/../../../config.php');

function getAllTeachers()
{
    $conn = connectDatabase();
    $query = "SELECT * FROM tutor INNER JOIN pelajaran ON tutor.id_pelajaran = pelajaran.id_pelajaran";
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

function getTeacherWhereId($id)
{
    $conn = connectDatabase();
    $query = "SELECT * FROM users WHERE id = '$id' AND role = 'teacher'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [
            "status" => false,
            "message" => "Error fetching teacher: " . mysqli_error($conn)
        ];
    }

    $teacher = mysqli_fetch_assoc($result);

    return [
        "status" => true,
        "teacher" => $teacher
    ];
}

function getAllCountTeachers()
{
    $conn = connectDatabase();
    $query = "SELECT COUNT(*) as count FROM users WHERE role = 'teacher'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}
