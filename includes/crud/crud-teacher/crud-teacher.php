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