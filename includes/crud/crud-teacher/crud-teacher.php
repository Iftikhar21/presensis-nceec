<?php
require_once(__DIR__ . '/../../../config.php');

// =========================
// READ OPERATIONS
// =========================

/**
 * Get all teachers with their subject information
 */
function getAllTeachers()
{
    $conn = connectDatabase();
    if (!$conn) {
        return ['status' => false, 'message' => 'Database connection failed', 'teachers' => []];
    }

    $query = "SELECT t.*, p.pelajaran, u.username, u.email 
              FROM tutor t 
              INNER JOIN pelajaran p ON t.id_pelajaran = p.id_pelajaran
              INNER JOIN users u ON t.user_id = u.id
              ORDER BY t.created_at DESC";
    
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [
            'status' => false, 
            'message' => 'Error fetching teachers: ' . mysqli_error($conn),
            'teachers' => []
        ];
    }

    $tutorList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tutorList[] = $row;
    }
    
    mysqli_close($conn);
    return [
        'status' => true,
        'message' => 'Teachers fetched successfully',
        'teachers' => $tutorList
    ];
}

/**
 * Get teacher data by user ID
 */
function getTeacherWhereId($id)
{
    $conn = connectDatabase();
    if (!$conn) {
        return [
            "status" => false,
            "message" => "Database connection failed"
        ];
    }

    // Use prepared statement for security
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND role = 'teacher'");
    if (!$stmt) {
        mysqli_close($conn);
        return [
            "status" => false,
            "message" => "Error preparing statement: " . mysqli_error($conn)
        ];
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return [
            "status" => false,
            "message" => "Error fetching teacher: " . mysqli_error($conn)
        ];
    }

    $teacher = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    if (!$teacher) {
        return [
            "status" => false,
            "message" => "Teacher not found"
        ];
    }

    return [
        "status" => true,
        "teacher" => $teacher
    ];
}

/**
 * Get tutor data by user ID
 */
function getTutorByUserId($user_id) 
{
    $conn = connectDatabase();
    if (!$conn) {
        return null;
    }
    
    // Use prepared statement for security
    $stmt = mysqli_prepare($conn, "SELECT t.*, p.pelajaran 
                                  FROM tutor t 
                                  LEFT JOIN pelajaran p ON t.id_pelajaran = p.id_pelajaran 
                                  WHERE t.user_id = ?");
    
    if (!$stmt) {
        mysqli_close($conn);
        return null;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return null;
    }
    
    $tutor_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $tutor_data;
}

/**
 * Get tutor by ID
 */
function getTutorById($id_tutor)
{
    $conn = connectDatabase();
    if (!$conn) {
        return ['status' => false, 'message' => 'Database connection failed'];
    }

    $stmt = mysqli_prepare($conn, "SELECT t.*, p.pelajaran, u.username, u.email 
                                  FROM tutor t 
                                  LEFT JOIN pelajaran p ON t.id_pelajaran = p.id_pelajaran
                                  LEFT JOIN users u ON t.user_id = u.id
                                  WHERE t.id_tutor = ?");
    
    if (!$stmt) {
        mysqli_close($conn);
        return ['status' => false, 'message' => 'Error preparing statement: ' . mysqli_error($conn)];
    }

    mysqli_stmt_bind_param($stmt, "i", $id_tutor);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return ['status' => false, 'message' => 'Error executing query: ' . mysqli_error($conn)];
    }

    $tutor = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    if (!$tutor) {
        return ['status' => false, 'message' => 'Tutor not found'];
    }

    return ['status' => true, 'tutor' => $tutor];
}

/**
 * Get total count of teachers
 */
function getAllCountTeachers()
{
    $conn = connectDatabase();
    if (!$conn) {
        return 0;
    }

    $query = "SELECT COUNT(*) as count FROM users WHERE role = 'teacher'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        mysqli_close($conn);
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    return (int)$row['count'];
}

// =========================
// CREATE/UPDATE OPERATIONS
// =========================

/**
 * Create new tutor profile
 */
function createTeacher($user_id, $nama_tutor, $id_pelajaran, $bergabung, $foto_profile = null)
{
    $conn = connectDatabase();
    if (!$conn) {
        return ["status" => false, "message" => "Database connection failed"];
    }

    // Validate input
    if (empty($user_id) || empty($nama_tutor) || empty($id_pelajaran) || empty($bergabung)) {
        mysqli_close($conn);
        return ["status" => false, "message" => "All required fields must be filled"];
    }

    // Check if tutor already exists for this user
    $check_stmt = mysqli_prepare($conn, "SELECT id_tutor FROM tutor WHERE user_id = ?");
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "i", $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            mysqli_stmt_close($check_stmt);
            mysqli_close($conn);
            return ["status" => false, "message" => "Tutor profile already exists for this user"];
        }
        mysqli_stmt_close($check_stmt);
    }

    // Validate pelajaran exists
    $pelajaran_stmt = mysqli_prepare($conn, "SELECT id_pelajaran FROM pelajaran WHERE id_pelajaran = ?");
    if ($pelajaran_stmt) {
        mysqli_stmt_bind_param($pelajaran_stmt, "i", $id_pelajaran);
        mysqli_stmt_execute($pelajaran_stmt);
        $pelajaran_result = mysqli_stmt_get_result($pelajaran_stmt);
        
        if (mysqli_num_rows($pelajaran_result) == 0) {
            mysqli_stmt_close($pelajaran_stmt);
            mysqli_close($conn);
            return ["status" => false, "message" => "Selected subject does not exist"];
        }
        mysqli_stmt_close($pelajaran_stmt);
    }

    $created_at = date('Y-m-d H:i:s');

    // Use prepared statement
    $stmt = mysqli_prepare($conn, "INSERT INTO tutor (user_id, nama_tutor, id_pelajaran, bergabung, foto_profile, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        mysqli_close($conn);
        return ["status" => false, "message" => "Error preparing statement: " . mysqli_error($conn)];
    }

    mysqli_stmt_bind_param($stmt, "isssss", $user_id, $nama_tutor, $id_pelajaran, $bergabung, $foto_profile, $created_at);
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return [
            "status" => true, 
            "message" => "Tutor profile created successfully",
            "id_tutor" => $insert_id
        ];
    } else {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return ["status" => false, "message" => "Error creating tutor: " . $error];
    }
}

/**
 * Update existing tutor profile
 */
function updateTeacher($user_id, $nama_tutor, $id_pelajaran, $bergabung, $foto_profile = null)
{
    $conn = connectDatabase();
    if (!$conn) {
        return ["status" => false, "message" => "Database connection failed"];
    }

    // Validate input
    if (empty($user_id) || empty($nama_tutor) || empty($id_pelajaran) || empty($bergabung)) {
        mysqli_close($conn);
        return ["status" => false, "message" => "All required fields must be filled"];
    }

    // Check if tutor exists
    $check_stmt = mysqli_prepare($conn, "SELECT id_tutor, foto_profile FROM tutor WHERE user_id = ?");
    if (!$check_stmt) {
        mysqli_close($conn);
        return ["status" => false, "message" => "Error preparing check statement"];
    }

    mysqli_stmt_bind_param($check_stmt, "i", $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($check_stmt);
        mysqli_close($conn);
        return ["status" => false, "message" => "Tutor profile not found"];
    }

    $existing_data = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    // If no new photo provided, keep the existing one
    if (empty($foto_profile)) {
        $foto_profile = $existing_data['foto_profile'];
    }

    // Validate pelajaran exists
    $pelajaran_stmt = mysqli_prepare($conn, "SELECT id_pelajaran FROM pelajaran WHERE id_pelajaran = ?");
    if ($pelajaran_stmt) {
        mysqli_stmt_bind_param($pelajaran_stmt, "i", $id_pelajaran);
        mysqli_stmt_execute($pelajaran_stmt);
        $pelajaran_result = mysqli_stmt_get_result($pelajaran_stmt);
        
        if (mysqli_num_rows($pelajaran_result) == 0) {
            mysqli_stmt_close($pelajaran_stmt);
            mysqli_close($conn);
            return ["status" => false, "message" => "Selected subject does not exist"];
        }
        mysqli_stmt_close($pelajaran_stmt);
    }

    $updated_at = date('Y-m-d H:i:s');

    // Use prepared statement for update
    $stmt = mysqli_prepare($conn, "UPDATE tutor SET 
                                    nama_tutor = ?, 
                                    id_pelajaran = ?, 
                                    bergabung = ?, 
                                    foto_profile = ?, 
                                    updated_at = ?
                                  WHERE user_id = ?");

    if (!$stmt) {
        mysqli_close($conn);
        return ["status" => false, "message" => "Error preparing update statement: " . mysqli_error($conn)];
    }

    mysqli_stmt_bind_param($stmt, "sssssi", $nama_tutor, $id_pelajaran, $bergabung, $foto_profile, $updated_at, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return [
            "status" => true, 
            "message" => "Tutor profile updated successfully"
        ];
    } else {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return ["status" => false, "message" => "Error updating tutor: " . $error];
    }
}

// =========================
// DELETE OPERATIONS
// =========================

/**
 * Delete tutor profile
 */
function deleteTeacher($id_tutor)
{
    $conn = connectDatabase();
    if (!$conn) {
        return ["status" => false, "message" => "Database connection failed"];
    }

    // Check if tutor exists and get photo path for cleanup
    $check_stmt = mysqli_prepare($conn, "SELECT foto_profile FROM tutor WHERE id_tutor = ?");
    if (!$check_stmt) {
        mysqli_close($conn);
        return ["status" => false, "message" => "Error preparing check statement"];
    }

    mysqli_stmt_bind_param($check_stmt, "i", $id_tutor);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($check_stmt);
        mysqli_close($conn);
        return ["status" => false, "message" => "Tutor not found"];
    }

    $tutor_data = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    // Delete tutor record
    $stmt = mysqli_prepare($conn, "DELETE FROM tutor WHERE id_tutor = ?");
    if (!$stmt) {
        mysqli_close($conn);
        return ["status" => false, "message" => "Error preparing delete statement: " . mysqli_error($conn)];
    }

    mysqli_stmt_bind_param($stmt, "i", $id_tutor);

    if (mysqli_stmt_execute($stmt)) {
        // Clean up photo file if exists
        if (!empty($tutor_data['foto_profile']) && file_exists($tutor_data['foto_profile'])) {
            unlink($tutor_data['foto_profile']);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return [
            "status" => true, 
            "message" => "Tutor deleted successfully"
        ];
    } else {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return ["status" => false, "message" => "Error deleting tutor: " . $error];
    }
}

// =========================
// FILE HANDLING OPERATIONS
// =========================

/**
 * Handle file upload for profile photo
 */
function handleProfilePhotoUpload($file, $user_id, $upload_dir = '../../../uploads/profiles/')
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'No file uploaded or upload error', 'path' => null];
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $file_type = $file['type'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $detected_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);
    
    if (!in_array($file_type, $allowed_types) || !in_array($detected_type, $allowed_types)) {
        return ['status' => false, 'message' => 'File type not allowed. Only JPG, JPEG, PNG allowed.', 'path' => null];
    }
    
    // Validate file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['status' => false, 'message' => 'File size too large. Maximum 2MB allowed.', 'path' => null];
    }
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['status' => false, 'message' => 'Failed to create upload directory.', 'path' => null];
        }
    }
    
    // Generate unique filename
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return [
            'status' => true, 
            'message' => 'File uploaded successfully',
            'filename' => $new_filename, 
            'path' => $target_path
        ];
    } else {
        return ['status' => false, 'message' => 'Failed to move uploaded file', 'path' => null];
    }
}

// =========================
// ACTION HANDLER
// =========================

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    switch ($action) {
        case 'create':
            // Validate required fields
            if (!isset($_POST['user_id']) || !isset($_POST['nama_tutor']) || 
                !isset($_POST['id_pelajaran']) || !isset($_POST['bergabung'])) {
                
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode("All required fields must be filled"));
                exit();
            }

            $user_id = intval($_POST['user_id']);
            $nama_tutor = mysqli_real_escape_string(connectDatabase(), trim($_POST['nama_tutor']));
            $id_pelajaran = intval($_POST['id_pelajaran']);
            $bergabung = mysqli_real_escape_string(connectDatabase(), $_POST['bergabung']);
            $foto_profile = null;

            // Handle file upload
            if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleProfilePhotoUpload($_FILES['foto_profile'], $user_id);
                if ($upload_result['status']) {
                    $foto_profile = $upload_result['path'];
                } else {
                    header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode($upload_result['message']));
                    exit();
                }
            }

            // Create tutor
            $result = createTeacher($user_id, $nama_tutor, $id_pelajaran, $bergabung, $foto_profile);
            
            if ($result['status']) {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=success");
            } else {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode($result['message']));
            }
            exit();

        case 'update':
            // Validate required fields
            if (!isset($_POST['user_id']) || !isset($_POST['nama_tutor']) || 
                !isset($_POST['id_pelajaran']) || !isset($_POST['bergabung'])) {
                
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode("All required fields must be filled"));
                exit();
            }

            $user_id = intval($_POST['user_id']);
            $nama_tutor = mysqli_real_escape_string(connectDatabase(), trim($_POST['nama_tutor']));
            $id_pelajaran = intval($_POST['id_pelajaran']);
            $bergabung = mysqli_real_escape_string(connectDatabase(), $_POST['bergabung']);
            $foto_profile = null;

            // Handle file upload (optional for update)
            if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleProfilePhotoUpload($_FILES['foto_profile'], $user_id);
                if ($upload_result['status']) {
                    $foto_profile = $upload_result['path'];
                } else {
                    header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode($upload_result['message']));
                    exit();
                }
            }

            // Update tutor
            $result = updateTeacher($user_id, $nama_tutor, $id_pelajaran, $bergabung, $foto_profile);
            
            if ($result['status']) {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=success");
            } else {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode($result['message']));
            }
            exit();

        case 'delete':
            if (!isset($_POST['id_tutor'])) {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode("Tutor ID is required"));
                exit();
            }

            $id_tutor = intval($_POST['id_tutor']);
            $result = deleteTeacher($id_tutor);
            
            if ($result['status']) {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=success");
            } else {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode($result['message']));
            }
            exit();

        default:
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=error&message=" . urlencode("Invalid action"));
            exit();
    }
}
?>