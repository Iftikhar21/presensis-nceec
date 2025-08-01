<?php
require_once(__DIR__ . '/../../../config.php');

function getAllAdmins()
{
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

function getAdminWhereId($id)
{
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

function updateAdmin($username, $email, $id)
{
    $conn = connectDatabase();

    // Escape input untuk mencegah SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    $id = mysqli_real_escape_string($conn, $id);

    $query = "UPDATE users SET username = '$username', email = '$email' WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        error_log("Error updating admin: " . mysqli_error($conn));
        return [
            "status" => false,
            "message" => "Error updating admin: " . mysqli_error($conn)
        ];
    }

    if (mysqli_affected_rows($conn) > 0) {
        return [
            "status" => true,
            "message" => "Admin updated successfully"
        ];
    } else {
        return [
            "status" => false,
            "message" => "No changes made or admin not found"
        ];
    }
}
