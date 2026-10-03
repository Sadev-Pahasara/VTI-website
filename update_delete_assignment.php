<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // UPDATE ASSIGNMENT
    handleUpdateAssignment($conn);
} elseif ($method === 'DELETE') {
    // DELETE ASSIGNMENT
    handleDeleteAssignment($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleUpdateAssignment($conn) {
    // Get form data
    $assignment_id = $_POST['assignment_id'] ?? '';
    $lecturer_id = $_POST['lecturer_id'] ?? '';
    $course_ID = $_POST['course_code'] ?? '';
    $module_id = $_POST['module_id'] ?? '';
    $assignment_title = $_POST['assignment_title'] ?? '';
    $assignment_description = $_POST['assignment_description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $max_marks = $_POST['max_marks'] ?? '';

    // Validate required fields
    if (empty($assignment_id) || empty($lecturer_id) || empty($course_ID) || empty($module_id) || empty($assignment_title) || empty($due_date) || empty($max_marks)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    // Verify assignment exists and belongs to lecturer
    $verify_sql = "SELECT assignment_id FROM assignments WHERE assignment_id = ? AND lecturer_id = ?";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("ss", $assignment_id, $lecturer_id);
    $stmt->execute();
    $verify_result = $stmt->get_result();
    $verify_row = $verify_result->fetch_assoc();
    $stmt->close();

    if (empty($verify_row)) {
        echo json_encode(['success' => false, 'message' => 'Assignment not found or you do not have permission to edit it']);
        exit;
    }

    // Handle file upload
    $assignment_file = null;
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        
        // Check for upload errors
        if ($_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'File is too large (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit)',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $error_message = $upload_errors[$_FILES['assignment_file']['error']] ?? 'Unknown upload error';
            echo json_encode(['success' => false, 'message' => 'File upload error: ' . $error_message]);
            exit;
        }
        
        $uploadDir = 'uploads/assignments/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                exit;
            }
        }
        
        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            echo json_encode(['success' => false, 'message' => 'Upload directory is not writable']);
            exit;
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION));
        $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $assignment_title) . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['doc', 'docx', 'pdf'];
        if (!in_array($fileExtension, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only DOC, DOCX, and PDF files are allowed. Your file type: ' . $fileExtension]);
            exit;
        }
        
        // Check file size (max 10MB)
        if ($_FILES['assignment_file']['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size must be less than 10MB']);
            exit;
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $filePath)) {
            $assignment_file = $filePath;
            
            // Update with file
            $sql = "UPDATE assignments SET course_ID = ?, module_ID = ?, assignment_title = ?, assignment_description = ?, due_date = ?, max_marks = ?, assignment_file = ? WHERE assignment_id = ? AND lecturer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiss", $course_ID, $module_id, $assignment_title, $assignment_description, $due_date, $max_marks, $assignment_file, $assignment_id, $lecturer_id);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
    } else {
        // Update without changing file
        $sql = "UPDATE assignments SET course_ID = ?, module_ID = ?, assignment_title = ?, assignment_description = ?, due_date = ?, max_marks = ? WHERE assignment_id = ? AND lecturer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiss", $course_ID, $module_id, $assignment_title, $assignment_description, $due_date, $max_marks, $assignment_id, $lecturer_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Assignment updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating assignment: ' . $stmt->error]);
    }

    $stmt->close();
}

function handleDeleteAssignment($conn) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $assignment_id = $input['assignment_id'] ?? '';
    $lecturer_id = $input['lecturer_id'] ?? '';

    // Validate required fields
    if (empty($assignment_id) || empty($lecturer_id)) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID and Lecturer ID are required']);
        exit;
    }

    // Verify assignment exists and belongs to lecturer
    $verify_sql = "SELECT assignment_id, assignment_file FROM assignments WHERE assignment_id = ? AND lecturer_id = ?";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("ss", $assignment_id, $lecturer_id);
    $stmt->execute();
    $verify_result = $stmt->get_result();
    $verify_row = $verify_result->fetch_assoc();
    $stmt->close();

    if (empty($verify_row)) {
        echo json_encode(['success' => false, 'message' => 'Assignment not found or you do not have permission to delete it']);
        exit;
    }

    // Delete assignment file if exists
    if (!empty($verify_row['assignment_file']) && file_exists($verify_row['assignment_file'])) {
        unlink($verify_row['assignment_file']);
    }

    // Delete assignment from database
    $sql = "DELETE FROM assignments WHERE assignment_id = ? AND lecturer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $assignment_id, $lecturer_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting assignment: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>