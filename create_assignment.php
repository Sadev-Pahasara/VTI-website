<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

// Get form data
$lecturer_id = $_POST['lecturer_id'] ?? '';
$course_ID = $_POST['course_code'] ?? '';
$module_id = $_POST['module_id'] ?? '';
$assignment_title = $_POST['assignment_title'] ?? '';
$assignment_description = $_POST['assignment_description'] ?? '';
$due_date = $_POST['due_date'] ?? '';
$max_marks = $_POST['max_marks'] ?? '';

// Validate required fields
if (empty($lecturer_id) || empty($course_ID) || empty($module_id) || empty($assignment_title) || empty($due_date) || empty($max_marks)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
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
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        exit;
    }
} else {
    // No file was uploaded, but that's OK - assignment can be created without file
    $assignment_file = null;
}

// Verify course exists
$course_sql = "SELECT course_ID FROM course WHERE course_ID = ?";
$stmt = $conn->prepare($course_sql);
$stmt->bind_param("s", $course_ID);
$stmt->execute();
$course_result = $stmt->get_result();
$course_row = $course_result->fetch_assoc();
$stmt->close();

if (empty($course_row)) {
    echo json_encode(['success' => false, 'message' => 'Invalid course selected']);
    exit;
}

// Verify module exists and belongs to the course
$module_sql = "SELECT module_ID FROM module WHERE module_ID = ? AND course_ID = ?";
$stmt = $conn->prepare($module_sql);
$stmt->bind_param("ss", $module_id, $course_ID);
$stmt->execute();
$module_result = $stmt->get_result();
$module_row = $module_result->fetch_assoc();
$stmt->close();

if (empty($module_row)) {
    echo json_encode(['success' => false, 'message' => 'Invalid module selected for this course']);
    exit;
}

// Insert assignment into database
$sql = "INSERT INTO assignments (lecturer_id, course_ID, module_ID, assignment_title, assignment_description, due_date, max_marks, assignment_file) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssis", $lecturer_id, $course_ID, $module_id, $assignment_title, $assignment_description, $due_date, $max_marks, $assignment_file);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Assignment created successfully' . ($assignment_file ? ' with file' : ' without file')]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating assignment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>