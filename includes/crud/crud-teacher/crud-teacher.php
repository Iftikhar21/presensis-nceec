<?php
require_once(__DIR__ . '/../../../config.php');
function getAllTeacher() {
    $conn = connectDatabase();
    $query = "SELECT * FROM users WHERE role = 'teacher'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [
            "status" => false,
            "message" => "Error fetching admins: " . mysqli_error($conn)
        ];

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

    return [
        "status" => true,
        "teachers" => $teachers
    ];
}

function getTeacherWhereId($id) {
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
?>
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
