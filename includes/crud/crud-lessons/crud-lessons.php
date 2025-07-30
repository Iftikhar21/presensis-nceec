<?php
require_once(__DIR__ . '/../../../config.php');

function getAllLessons()
{
    $conn = connectDatabase();
    $query = "SELECT p.*, 
              (SELECT COUNT(*) FROM materi WHERE id_pelajaran = p.id_pelajaran) as jumlah_materi
              FROM pelajaran p";
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

function getLessonsWhereId($id)
{
    $conn = connectDatabase();
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM pelajaran WHERE id_pelajaran = '$id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    return mysqli_fetch_assoc($result);
}

function getAllCountLessons()
{
    $conn = connectDatabase();
    $query = "SELECT COUNT(*) as count FROM pelajaran";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}

function addLessons($nama_pelajaran)
{
    $conn = connectDatabase();
    $query = "INSERT INTO pelajaran (pelajaran, created_at, updated_at) VALUES (?, ?, ?)";
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $nama_pelajaran, $createdAt, $updatedAt);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return true;
        } else {
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        return false;
    }
}

function editLessons($nama_pelajaran, $id_pelajaran)
{
    $conn = connectDatabase();
    $query = "UPDATE pelajaran SET pelajaran = ?, updated_at = ? WHERE id_pelajaran = ?";
    $updatedAt = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $nama_pelajaran, $updatedAt, $id_pelajaran);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return true;
        } else {
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        return false;
    }
}

function deleteLessons($id_pelajaran)
{
    $conn = connectDatabase();
    $query = "DELETE FROM pelajaran WHERE id_pelajaran = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_pelajaran);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return true;
        } else {
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        return false;
    }
}
