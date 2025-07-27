<?php
require_once(__DIR__ . '/../../../config.php');

function getAllAdmins() {
    $conn = connectDatabase();
    $query = "SELECT * FROM users WHERE role = 'admin'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [
            "status" => false,
            "message" => "Error fetching admins: " . mysqli_error($conn)
        ];
    }

    $admins = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $admins[] = $row;
    }

    return [
        "status" => true,
        "admins" => $admins
    ];
}

function getAdminWhereId($id) {
    $conn = connectDatabase();
    $query = "SELECT * FROM users WHERE id = '$id' AND role = 'admin'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [
            "status" => false,
            "message" => "Error fetching admin: " . mysqli_error($conn)
        ];
    }

    $admin = mysqli_fetch_assoc($result);

    return [
        "status" => true,
        "admin" => $admin
    ];
}